<?php
class Craftsvilla_Activeshipment_Block_Activeshipment extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getActiveshipment()     
     { 
        if (!$this->hasData('activeshipment')) {
            $this->setData('activeshipment', Mage::registry('activeshipment'));
        }
        return $this->getData('activeshipment');
        
    }
}