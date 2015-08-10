<?php
class Craftsvilla_Disputecusremarks_Block_Disputecusremarks extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDisputecusremarks()     
     { 
        if (!$this->hasData('disputecusremarks')) {
            $this->setData('disputecusremarks', Mage::registry('disputecusremarks'));
        }
        return $this->getData('disputecusremarks');
        
    }
}
