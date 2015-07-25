<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_EbayActions extends Mage_Core_Model_Abstract
{
    private $_actionsProducts = array();

    // ########################################

    public function setProduct(Ess_M2ePro_Model_ListingsProducts $listingProductInstance, $action, array $params = array())
    {
        $params['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_SYNCH;
        
        // Check same action set before
        //----------------------------------
        $foreachIndex = 0;
        $tempExistItem = NULL;
        
        foreach ($this->_actionsProducts as $item) {
            
            if ($item['instance']->getId() == $listingProductInstance->getId()) {

                if ($item['action'] == $action) {

                    foreach ($params as $tempParamKey => $tempParamValue) {
                        
                        if (isset($this->_actionsProducts[$foreachIndex]['params'][$tempParamKey]) &&
                            is_array($this->_actionsProducts[$foreachIndex]['params'][$tempParamKey]) &&
                            is_array($tempParamValue)) {

                            $this->_actionsProducts[$foreachIndex]['params'][$tempParamKey] =
                            array_merge($this->_actionsProducts[$foreachIndex]['params'][$tempParamKey],$tempParamValue);
                        } else {
                            $this->_actionsProducts[$foreachIndex]['params'][$tempParamKey] = $tempParamValue;
                        }
                    }

                    return true;
                }
                
                $tempExistItem = $item;
                break;
            }
            
            $foreachIndex++;
        }
        //----------------------------------

        // Prepare others actions
        //----------------------------------
        if (!is_null($tempExistItem)) {

            do {

                if ($action == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP) {
                    $this->deleteProduct($tempExistItem['instance']);
                    break;
                }

                if ($tempExistItem['action'] == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST) {
                    $this->deleteProduct($tempExistItem['instance']);
                    break;
                }

                if ($tempExistItem['action'] == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST) {
                    $this->deleteProduct($tempExistItem['instance']);
                    break;
                }

                if ($tempExistItem['action'] == Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST) {
                    return false;
                }

            } while (false);
        }
        //----------------------------------

        // Add new action for ebay
        //----------------------------------
        $this->_actionsProducts[] = array(
            'instance' => $listingProductInstance,
            'action' => $action,
            'params' => $params
        );
        //----------------------------------

        return true;
    }

    public function deleteProduct(Ess_M2ePro_Model_ListingsProducts $listingProductInstance)
    {
        // Make new array without deleted items
        //----------------------------------
        $newArray = array();
        foreach ($this->_actionsProducts as $item) {
            if ($item['instance']->getId() == $listingProductInstance->getId()) {
                continue;
            }
            $newArray[] = $item;
        }
        //----------------------------------

        // Replace main array
        //----------------------------------
        if (count($this->_actionsProducts) != count($newArray)) {
            $this->_actionsProducts = $newArray;
            return true;
        } else {
            return false;
        }
        //----------------------------------
    }

    // ########################################

    public function execute(Ess_M2ePro_Model_Synchronization_LockItem $lockItem, $percentsFrom, $percentsTo)
    {
        $lockItem->activate();
        $lockItem->setPercents($percentsFrom);

        $lockItem->setStatus(Mage::helper('M2ePro')->__('Communication with eBay is started. Please wait...'));

        // Get prepared for actions array
        //----------------------------
        $actions = $this->makeActionsForExecute();
        //----------------------------

        // Calculate total count items
        //----------------------------
        $totalCount = 0;
        foreach ($actions as $combinations) {
            foreach ($combinations as $combination) {
                $totalCount += count($combination['items']);
            }
        }

        if ($totalCount == 0) {
            return Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS;
        }
        //----------------------------

        // Execute ebay actions
        //----------------------------
        $results = array();
        $percentsOneProduct = ($percentsTo - $percentsFrom)/$totalCount;

        foreach ($actions as $action=>$combinations) {
            foreach ($combinations as $combination) {

                $currentCount = count($combination['items']);

                $maxCountPerStep = 10;
                $currentCount <= 25 && $maxCountPerStep = 5;
                $currentCount <= 15 && $maxCountPerStep = 3;
                $currentCount <= 8 && $maxCountPerStep = 2;
                $currentCount <= 4 && $maxCountPerStep = 1;

                $percentsCurrentInterval = $percentsOneProduct*$currentCount;
                $percentsByStep = $percentsCurrentInterval/ceil($currentCount/$maxCountPerStep);

                for ($i=0; $i<count($combination['items']);$i+=$maxCountPerStep) {

                    $itemsForStep = array_slice($combination['items'],$i,$maxCountPerStep);

                    // Set status for progress bar
                    //-----------------------------
                    $statusActionTitle = Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::getActionTitle($action);
                    $statusProductsIds = array();
                    foreach ($itemsForStep as $item) {
                        $statusProductsIds[] = $item->getData('product_id');
                    }
                    $lockItem->setStatus($statusActionTitle.' "'.implode('", "',$statusProductsIds).'" '.Mage::helper('M2ePro')->__('product(s). Please wait...'));
                    //-----------------------------

                    $tempResult = Mage::getModel('M2ePro/Connectors_Ebay_Item_Dispatcher')->process($action, $itemsForStep, $combination['params']);
                    $results  = array_merge($results,array($tempResult));

                    // Set percents for progress bar
                    //-----------------------------
                    $percentsStep = (int)$lockItem->getPercents() + $percentsByStep;
                    if ($percentsStep > $percentsTo) {
                        $percentsStep = $percentsTo;
                    }
                    $lockItem->setPercents($percentsStep);
                    //-----------------------------

                    $lockItem->activate();
                }
            }
        }
        //----------------------------

        $lockItem->setStatus(Mage::helper('M2ePro')->__('Communication with eBay is finished. Please wait...'));

        $lockItem->setPercents($percentsTo);
        $lockItem->activate();

        return Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::getMainStatus($results);
    }

    public function clear()
    {
        $this->_actionsProducts = array();
    }

    // ########################################

    private function makeActionsForExecute()
    {
        $actions = array(
            Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST => array(),
            Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST => array(),
            Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE => array(),
            Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP => array()
        );

        foreach ($this->_actionsProducts as $item) {

            // Get params hash
            //----------------------------
            $paramsHash = '';
            ksort($item['params']);
            foreach ($item['params'] as $keyParam => $valueParam) {
                $paramsHash .= (string)$keyParam.(string)$valueParam;
            }
            if ($paramsHash != '') {
                $paramsHash = md5($paramsHash);
            }
            //----------------------------

            // Add to output array
            //----------------------------
            $index = NULL;
            for ($i=0;$i<count($actions[$item['action']]);$i++) {
                $combination = $actions[$item['action']][$i];
                if ($combination['params_hash'] == $paramsHash) {
                    $index = $i;
                    break;
                }
            }
            if (is_null($index)) {
                $actions[$item['action']][] = array(
                    'items' => array($item['instance']),
                    'params' => $item['params'],
                    'params_hash' => $paramsHash
                );
            } else {
                $actions[$item['action']][$index]['items'][] = $item['instance'];
            }
            //----------------------------
        }

        return $actions;
    }

    // ########################################
}