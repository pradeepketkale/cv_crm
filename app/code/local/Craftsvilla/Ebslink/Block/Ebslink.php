<?php
class Craftsvilla_Ebslink_Block_Ebslink extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getEbslink()     
     { 
        if (!$this->hasData('ebslink')) {
            $this->setData('ebslink', Mage::registry('ebslink'));
        }
        return $this->getData('ebslink');
        
    }
}