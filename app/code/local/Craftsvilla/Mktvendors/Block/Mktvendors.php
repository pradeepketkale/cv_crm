<?php
class Craftsvilla_Mktvendors_Block_Mktvendors extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getMktvendors()     
     { 
        if (!$this->hasData('mktvendors')) {
            $this->setData('mktvendors', Mage::registry('mktvendors'));
        }
        return $this->getData('mktvendors');
        
    }
}