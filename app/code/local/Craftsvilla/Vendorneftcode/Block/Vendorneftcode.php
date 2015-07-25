<?php
class Craftsvilla_Vendorneftcode_Block_Vendorneftcode extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getVendorneftcode()     
     { 
        if (!$this->hasData('vendorneftcode')) {
            $this->setData('vendorneftcode', Mage::registry('vendorneftcode'));
        }
        return $this->getData('vendorneftcode');
        
    }
}
