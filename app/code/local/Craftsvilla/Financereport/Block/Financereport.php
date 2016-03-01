<?php
class Craftsvilla_Financereport_Block_Financereport extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFinancereport()     
     { 
        if (!$this->hasData('financereport')) {
            $this->setData('financereport', Mage::registry('financereport'));
        }
        return $this->getData('financereport');
        
    }
}
