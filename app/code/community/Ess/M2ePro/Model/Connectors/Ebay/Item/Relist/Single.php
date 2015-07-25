<?php/* * @copyright  Copyright (c) 2011 by  ESS-UA. */class Ess_M2ePro_Model_Connectors_Ebay_Item_Relist_Single extends Ess_M2ePro_Model_Connectors_Ebay_Item_SingleAbstract{    // ########################################    protected function getCommand()    {        return array('item','update','relist');    }    protected function getListingsLogsCurrentAction()    {        return Ess_M2ePro_Model_ListingsLogs::ACTION_RELIST_PRODUCT_ON_EBAY;    }        // ########################################    protected function validateNeedRequestSend()    {        if (!$this->listingProduct->isRelistable()) {                        $message = array(                // Parser hack -> Mage::helper('M2ePro')->__('The item either is listed, not listed yet or not available');                parent::MESSAGE_TEXT_KEY => 'The item either is listed, not listed yet or not available',                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR            );            $this->addListingsProductsLogsMessage($this->listingProduct,$message,                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);            return false;        }        return true;    }        protected function getRequestData()    {        $requestData = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')                                ->getRelistRequestData($this->listingProduct,$this->params);        return $requestData;    }    //----------------------------------------    protected function validateResponseData($response)    {        return true;    }    protected function prepareResponseData($response)    {        if (isset($response['already_list'])) {            $dataForUpdate = array(                'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED,                'status_changer' => Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_EBAY            );            if (isset($response['ebay_start_date_raw'])) {                $dataForUpdate['ebay_start_date'] = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_start_date_raw']);            }            if (isset($response['ebay_end_date_raw'])) {                $dataForUpdate['ebay_end_date'] = Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_end_date_raw']);            }            $this->listingProduct->addData($dataForUpdate)->save();        } else if (isset($response['result']['ebay_item_id']) && $response['result']['ebay_item_id'] != '') {            Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')                                ->updateAfterRelistAction($this->listingProduct,                                                          array_merge($this->params,$response['result']));            $message = array(                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully relisted');                parent::MESSAGE_TEXT_KEY => 'Item was successfully relisted',                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS            );            $this->addListingsProductsLogsMessage($this->listingProduct, $message,                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);        }        return $response;    }    // ########################################}