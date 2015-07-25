<?php
class Craftsvilla_Craftsvillacustomer_Block_Craftsvillacustomer extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCraftsvillacustomer()     
     { 
        if (!$this->hasData('craftsvillacustomer')) {
            $this->setData('craftsvillacustomer', Mage::registry('craftsvillacustomer'));
        }
        return $this->getData('craftsvillacustomer');
        
    }
}