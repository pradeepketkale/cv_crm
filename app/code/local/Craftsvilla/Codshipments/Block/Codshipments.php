<?php
class Craftsvilla_Codshipments_Block_Codshipments extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCodshipments()     
     { 
        if (!$this->hasData('codshipments')) {
            $this->setData('codshipments', Mage::registry('codshipments'));
        }
        return $this->getData('codshipments');
        
    }
}