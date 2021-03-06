<?php

class Unirgy_Dropship_Model_Vendor_Decision_Abstract extends Varien_Object
{
    public function apply($items)
    {
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        foreach ($items as $item) {
            $vendorId = $localVendorId();
            $product = $item->getProduct();
            if (!$item->getProduct()) {
                if (!$item->getProductId()) {
                    $item->setUdropshipVendor($localVendorId);
                    continue;
                }
                $item->setProduct(Mage::getModel('catalog/product')->load($item->getProductId()));
            }
            $product = $item->getProduct();
            if ($product->getUdropshipVendor()) {
                $vendorId = $product->getUdropshipVendor();
            }
            $item->setUdropshipVendor($vendorId);
        }
        return $this;
    }

    public function collectStockLevels($items, $options=array())
    {
        Mage::getSingleton('udropship/stock_availability')->collectStockLevels($items, $options);
        return $this;
    }


}