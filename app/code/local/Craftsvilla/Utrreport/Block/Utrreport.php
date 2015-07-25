<?php
class Craftsvilla_Utrreport_Block_Utrreport extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getUtrreport()     
     { 
        if (!$this->hasData('utrreport')) {
            $this->setData('utrreport', Mage::registry('utrreport'));
        }
        return $this->getData('utrreport');
        
    }
}