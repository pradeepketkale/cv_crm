<?php
class Craftsvilla_Productmanagement_Block_Productmanagement extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getProductmanagement()     
     { 
        if (!$this->hasData('productmanagement')) {
            $this->setData('productmanagement', Mage::registry('productmanagement'));
        }
        return $this->getData('productmanagement');
        
    }
   
}


