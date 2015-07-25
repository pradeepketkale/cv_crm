<?php
class Craftsvilla_Customerreturn_Block_Customerreturn extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCustomerreturn()     
     { 
        if (!$this->hasData('customerreturn')) {
            $this->setData('customerreturn', Mage::registry('customerreturn'));
        }
        return $this->getData('customerreturn');
        
    }
}
