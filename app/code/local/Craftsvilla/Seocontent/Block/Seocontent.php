<?php
class Craftsvilla_Seocontent_Block_Seocontent extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getSeocontent()     
     { 
        if (!$this->hasData('seocontent')) {
            $this->setData('seocontent', Mage::registry('seocontent'));
        }
        return $this->getData('seocontent');
        
    }
}