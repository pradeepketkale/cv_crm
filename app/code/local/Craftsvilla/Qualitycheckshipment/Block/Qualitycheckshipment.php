<?php
class Craftsvilla_Qualitycheckshipment_Block_Qualitycheckshipment extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getQualitycheckshipment()     
     { 
        if (!$this->hasData('qualitycheckshipment')) {
            $this->setData('qualitycheckshipment', Mage::registry('qualitycheckshipment'));
        }
        return $this->getData('qualitycheckshipment');
        
    }
}
