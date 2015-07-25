<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Stop extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 55;
    const PERCENTS_END = 70;
    const PERCENTS_INTERVAL = 15;

    private $_synchronizations = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::registry('synchTemplatesArray');
    }

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Stop Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->executeStatusDisabled();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/3);
        $this->_lockItem->activate();

        $this->executeQtyIsOutOfStock();

        $this->_lockItem->setPercents(self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/3);
        $this->_lockItem->activate();
        
        $this->executeQtyHasValue();
    }

    //####################################

    private function executeStatusDisabled()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Stop when status disabled');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'status';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            if ((int)$changedListingProduct['pc_value_new'] != Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            
            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($changedListingProduct['id']);

            if (!$listingProduct->isListed()) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isStopStatusDisabled()) {
                continue;
            }

            if (!$listingProduct->isStoppable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsVariationsOptionsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            if ((int)$changedListingProductVariationOption['pc_value_new'] != Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                continue;
            }

            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */

            $listingProductVariationOption = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($changedListingProductVariationOption['id']);

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isStopStatusDisabled()) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->getQty() > 0) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isStoppable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeQtyIsOutOfStock()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Stop when out of stock');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();
        $attributesForProductsChanges[] = 'stock_availability';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            if ((int)$changedListingProduct['pc_value_new'] != 0) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */

            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($changedListingProduct['id']);

            if (!$listingProduct->isListed()) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isStopOutOfStock()) {
                continue;
            }

            if (!$listingProduct->isStoppable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsVariationsOptionsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            if ((int)$changedListingProductVariationOption['pc_value_new'] != 0) {
                continue;
            }

            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */

            $listingProductVariationOption = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($changedListingProductVariationOption['id']);

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isStopOutOfStock()) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->getQty() > 0) {
                continue;
            }

            if (!$listingProductVariationOption->getListingProduct()->isStoppable()) {
                continue;
            }

            $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeQtyHasValue()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Stop when item qty is');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductsChanges = array();

        // TODO PRODUCTS QTY FIX
        $attributesForProductsChanges[] = 'qty';
        
        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->isStopWhenQtyHasValue()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $src = $listing->getSellingFormatTemplate()->getQtySource();
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                    $attributesForProductsChanges[] = 'qty';
                }
                if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                    $attributesForProductsChanges[] = $src['attribute'];
                }
            }
        }

        $attributesForProductsChanges = array_unique($attributesForProductsChanges);
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            
            $listingProduct = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($changedListingProduct['id']);

            if (!$listingProduct->isListed()) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $attributeNeeded = '';

            $src = $listingProduct->getSellingFormatTemplate()->getQtySource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                $attributeNeeded = 'qty';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            // TODO PRODUCTS QTY FIX
            $attributeNeeded == '' && $attributeNeeded = 'qty';
            
            if ($attributeNeeded != $changedListingProduct['pc_attribute']) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->isStopWhenQtyHasValue()) {
                continue;
            }

            $typeQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueType();
            $minQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueMin();
            $maxQty = (int)$listingProduct->getSynchronizationTemplate()->getStopWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_NONE) {
                continue;
            }

            $result = false;

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_LESS &&
                (int)$changedListingProduct['pc_value_new'] <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_MORE &&
                (int)$changedListingProduct['pc_value_new'] >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_BETWEEN &&
                (int)$changedListingProduct['pc_value_new'] >= $minQty &&
                (int)$changedListingProduct['pc_value_new'] <= $maxQty) {
                $result = true;
            }

            if ($result) {

                if (!$listingProduct->isStoppable()) {
                    continue;
                }

                $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
            }
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/ProductsChanges')->getChangedListingsProductsVariationsOptionsByAttributes($attributesForProductsChanges);
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            /** @var $listingProductVariationOption Ess_M2ePro_Model_ListingsProductsVariationsOptions */
            
            $listingProductVariationOption = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($changedListingProductVariationOption['id']);

            if (!$listingProductVariationOption->getListingProduct()->isListed()) {
                continue;
            }

            if (!$listingProductVariationOption->getListing()->isSynchronizationNowRun()) {
                continue;
            }
            
            $attributeNeeded = '';

            $src = $listingProductVariationOption->getSellingFormatTemplate()->getQtySource();
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_PRODUCT) {
                $attributeNeeded = 'qty';
            }
            if ($src['mode'] == Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                $attributeNeeded = $src['attribute'];
            }

            // TODO PRODUCTS QTY FIX
            $attributeNeeded == '' && $attributeNeeded = 'qty';
            
            if ($attributeNeeded != $changedListingProductVariationOption['pc_attribute']) {
                continue;
            }

            if (!$listingProductVariationOption->getSynchronizationTemplate()->isStopWhenQtyHasValue()) {
                continue;
            }

            $typeQty = (int)$listingProductVariationOption->getSynchronizationTemplate()->getStopWhenQtyHasValueType();
            $minQty = (int)$listingProductVariationOption->getSynchronizationTemplate()->getStopWhenQtyHasValueMin();
            $maxQty = (int)$listingProductVariationOption->getSynchronizationTemplate()->getStopWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_NONE) {
                continue;
            }

            $result = false;
            $productQty = $listingProductVariationOption->getListingProduct()->getQty();

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_LESS &&
                (int)$productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_MORE &&
                (int)$productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_SynchronizationsTemplates::STOP_QTY_BETWEEN &&
                (int)$productQty >= $minQty &&
                (int)$productQty <= $maxQty) {
                $result = true;
            }

            if ($result) {

                if (!$listingProductVariationOption->getListingProduct()->isStoppable()) {
                    continue;
                }

                $this->_ebayActions->setProduct($listingProductVariationOption->getListingProduct(),Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,array());
            }
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}