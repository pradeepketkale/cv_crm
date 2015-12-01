<?php
class Craftsvilla_Productupdate_Block_Productupdate extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getProductupdate()     
     { 
        if (!$this->hasData('productupdate')) {
            $this->setData('productupdate', Mage::registry('productupdate'));
        }
        return $this->getData('productupdate');
        
    }
}