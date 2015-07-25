<?php

class Unirgy_DropshipMicrosite_Model_Mysql4_Attribute_Collection
    extends Mage_Eav_Model_Mysql4_Entity_Attribute_Group_Collection
{
    public function setAttributeSetFilter($setId)
    {
        parent::setAttributeSetFilter($setId);
        
        if (Mage::app()->getStore()->isAdmin() && Mage::helper('umicrosite')->getCurrentVendor()) {
            $hideAttrs = Mage::getStoreConfig('udropship/microsite/hide_product_attributes');
            
        }
        
        return $this;
    }
}