<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Observer extends Varien_Object
{
    /**
    * Create shipments based on dropship vendors
    *
    * @param mixed $observer
    */
    public function sales_order_save_after($observer)
    {
        Mage::helper('udropship/protected')->sales_order_save_after($observer);
    }

    /**
    * Before 1.4.1.x
    *
    * @param Varien_Object $observer
    */
    public function sales_order_item_save_before__helper($observer)
    {
        $this->setOrderItem($observer->getEvent()->getItem());
    }
    public function sales_order_item_save_before($observer)
    {
        $this->unsOrderItem();
    }

    /**
    * After 1.4.1.x
    *
    * @param mixed $observer
    */
    public function sales_model_service_quote_submit_before__helper($observer)
    {
        $this->setQuote($observer->getEvent()->getQuote());
    }
    public function sales_model_service_quote_submit_before($observer)
    {
        $this->unsQuote();
    }

    /**
    * Skip reducing stock level if item is shipped from dropship vendor
    *
    * @deprecated
    * @param Varien_Object $observer
    */
    /*
    public function sales_order_item_save_before($observer)
    {
        $item = $observer->getEvent()->getItem();
        $store = $item->getOrder()->getStore();
        //$children = $item->getChildrenItems();
        $localVendorId = Mage::helper('udropship')->getLocalVendorId($store);

        if (!$item->getUdropshipVendor()) {
            $item->setUdropshipVendor($localVendorId);
        }
        /*
        if (!$item->getId() && empty($children) && $item->getUdropshipVendor()==$localVendorId) {
            //disabled
            //Mage::getSingleton('cataloginventory/stock')->registerItemSale($item);
        }
        * /
    }
    */

    public function checkout_cart_add_product_complete($observer)
    {
        if (!Mage::helper('udropship')->isActive()) {
           return;
        }
        try {
            $hlp = Mage::helper('udropship/protected');
            $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
        } catch (Exception $e) {
            // all necessary actions should be already done by now, kill the exception
        }
    }

    public function checkout_cart_update_items_after($observer)
    {
        if (!Mage::helper('udropship')->isActive()) {
           return;
        }
        try {
            $hlp = Mage::helper('udropship/protected');
            $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
        } catch (Exception $e) {
            // all necessary actions should be already done by now, kill the exception
        }
    }

    /**
     * Check quote items stock level qty live
     *
     * @param Varien_Object $observer
     */
    public function sales_quote_item_qty_set_after($observer)
    {
        if (!Mage::helper('udropship')->isActive()) {
           return;
        }
        //return $this; //disabled
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (!$quoteItem || !$quoteItem->getProductId() || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }
        /* //deprecated
        if ($quoteItem->getHasError()) {
            $availability = Mage::getSingleton('udropship/stock_availability');
            $store = $quoteItem->getStoreId();
            $vendor = Mage::helper('udropship')->getVendor($quoteItem->getProduct());
            if ($availability->getUseLocalStockIfAvailable($store, $vendor)) {
                $quoteItem->setHasError(false);
            }
        }
        */
        try {
            $hlp = Mage::helper('udropship/protected');
            $items = array($quoteItem);
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
        } catch (Exception $e) {
            // all necessary actions should be already done by now, kill the exception
        }
        return $this;
    }
    
    public function sales_quote_item_set_product($observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        if (($parent = $item->getParentItem()) && $parent->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $parent->setUdropshipVendor($item->getUdropshipVendor());
            $parent->setBaseCost($item->getBaseCost());
        }
    }

    /**
    * Make sure local vendor is set for products that do not have this attribute
    *
    * @param Varien_Object $observer
    */
    public function catalog_product_load_after($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product->getUdropshipVendor()) {
            $product->setUdropshipVendor(Mage::helper('udropship')->getLocalVendorId($product->getStoreId()));
        }
    }

    /**
    * Set default local vendor for new products
    *
    * @param Varien_Object $observer
    */
    public function catalog_product_new_action($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $product->setUdropshipVendor(Mage::helper('udropship')->getLocalVendorId($product->getStoreId()));
    }

    /**
    * Update stock status for product collection if augmented stock status is used
    *
    * @param mixed $observer
    */
    public function catalog_product_collection_load_after($observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if (version_compare(Mage::getVersion(), '1.3.0', '>=') && $productCollection->hasFlag('require_stock_items')) {
            return;
        }

        $hlp = Mage::helper('udropship');
        $storeId = null;
        foreach ($productCollection as $product) {
            if (is_null($storeId)) {
                $storeId = $product->getStoreId();
                $localVendorId = $hlp->getLocalVendorId($storeId);
            }
            $vendorId = $hlp->getProductVendorId($product);
            if ($vendorId==$localVendorId) {
                continue;
            }
            if (Mage::getSingleton('udropship/stock_availability')->getUseLocalStockIfAvailable($storeId, $vendorId)) {
                $product->setIsSalable(true);
                $product->getStockItem()->setIsInStock(true);
            }
        }
    }

    public function cataloginventory_stock_item_save_before($observer)
    {
        return;
        // NOT USED
        $item = $observer->getEvent()->getItem();
        if (Mage::getSingleton('udropship/stock_availability')->getUseLocalStockIfAvailable()) {
            $item->setIsInStock(1);
        }
    }

    public function catalog_product_is_salable_after($observer)
    {
        /* //deprecated
        $product = $observer->getEvent()->getProduct();
        $store = $product->getStoreId();
        $vendor = Mage::helper('udropship')->getVendor($product);
        $object = $observer->getEvent()->getSalable();
        $availability = Mage::getSingleton('udropship/stock_availability');
        if ($availability->getUseLocalStockIfAvailable($store, $vendor)) {
            $object->setIsSalable(true);
        }
        */
    }
    
    public function adminhtml_sales_order_shipment_view($observer)
    {
        if (($soi = Mage::app()->getLayout()->getBlock('order_info'))
            && ($shipment = Mage::registry('current_shipment'))
        ) {
            if (($vName = Mage::helper('udropship')->getVendorName($shipment->getUdropshipVendor()))) {
                $soi->setVendorName($vName);
            }
            if (($stId = $shipment->getStatementId())) {
                $soi->setStatementId($stId);
                if (($st = Mage::getModel('udropship/vendor_statement')->load($stId, 'statement_id')) && $st->getId()) {
                    $soi->setStatementUrl(Mage::getModel('adminhtml/url')->getUrl('udropshipadmin/adminhtml_vendor_statement/edit', array('id'=>$st->getId())));
                }
            }
            if (Mage::helper('udropship')->isUdpayoutActive() && ($ptId = $shipment->getPayoutId())) {
                $soi->setPayoutId($ptId);
                if (($pt = Mage::getModel('udpayout/payout')->load($ptId)) && $pt->getId()) {
                    $soi->setPayoutUrl(Mage::getModel('adminhtml/url')->getUrl('udpayoutadmin/payout/edit', array('id'=>$pt->getId())));
                }
            }
        	if (Mage::helper('udropship')->isUdpoActive() && ($ptId = $shipment->getUdpoId())) {
                $soi->setUdpoId($ptId);
                if (Mage::helper('udpo')->getShipmentPo($shipment)) {
                	$soi->setUdpoId($shipment->getUdpo()->getIncrementId());
                    $soi->setUdpoUrl(Mage::getModel('adminhtml/url')->getUrl('udpoadmin/order_po/view', array('udpo_id'=>$shipment->getUdpo()->getId())));
                }
            }
        }
    }

    public function adminhtml_version($observer)
    {
        Mage::helper('udropship')->addAdminhtmlVersion('Unirgy_Dropship');
    }

    public function cronCollectTracking()
    {
        $tracks = Mage::getModel('sales/order_shipment_track')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('udropship_status', 'P')
            ->addAttributeToFilter('next_check', array('datetime'=>true, 'to'=>now()))
            ->setPageSize(50)
            ->addAttributeToSort('next_check')
        ;

        Mage::helper('udropship')->collectTracking($tracks);
    }

    /**
    * Check for extension update news
    *
    * @param Varien_Event_Observer $observer
    */
    public function adminhtml_controller_action_predispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('udropship/admin/notifications')) {
            try {
                Mage::getModel('udropship/feed')->checkUpdate();
            } catch (Exception $e) {
                // silently ignore
            }
        }
    }
    
    public function adminhtml_catalog_product_edit_element_types(Varien_Event_Observer $observer)
    {
        $observer->getResponse()->setTypes(array_merge($observer->getResponse()->getTypes(), 
            array('udropship_vendor'=>Mage::getConfig()->getBlockClassName('udropship/vendor_htmlselect'))
        ));
    }
    
    public function vendorNotifyLowstock()
    {
        Mage::getSingleton('udropship/vendor_notifyLowstock')->vendorNotifyLowstock();
    }
    public function vendorCleanLowstock()
    {
        Mage::getSingleton('udropship/vendor_notifyLowstock')->vendorCleanLowstock();
    }

    public function sales_order_shipment_save_before($observer)
    {
        $po = $observer->getEvent()->getShipment();
        if ($po->getUdropshipVendor()
            && ($vendor = Mage::helper('udropship')->getVendor($po->getUdropshipVendor()))
            && $vendor->getId()
            && (!$po->getStatementDate() || $po->getStatementDate() == '0000-00-00 00:00:00')
            && $vendor->getStatementPoType() == 'shipment'
        ) {
            $stPoStatuses = $vendor->getData('statement_po_status');
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            if (in_array($po->getUdropshipStatus(), $stPoStatuses)) {
                $po->setStatementDate(now());
            }
        }
    }
}