<?php
class Craftsvilla_Refundrequestedcustomer_Block_Refundrequestedcustomer extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getRefundrequestedcustomer()     
     { 
        if (!$this->hasData('refundrequestedcustomer')) {
            $this->setData('refundrequestedcustomer', Mage::registry('refundrequestedcustomer'));
        }
        return $this->getData('refundrequestedcustomer');
        
    }
}
