<?php

class Unirgy_DropshipMicrosite_Block_Frontend_VendorProducts extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $this->_addProductAttributesAndPrices($collection);
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        Mage::dispatchEvent('catalog_block_product_list_collection', array('collection' => $collection));

        $this->_productCollection = $collection;
        return $collection;
    }
}
