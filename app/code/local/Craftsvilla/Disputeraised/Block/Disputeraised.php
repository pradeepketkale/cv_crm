<?php
class Craftsvilla_Disputeraised_Block_Disputeraised extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDisputeraised()     
     { 
        if (!$this->hasData('disputeraised')) {
            $this->setData('disputeraised', Mage::registry('disputeraised'));
        }
        return $this->getData('disputeraised');
        
    }
}