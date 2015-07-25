<?php
class Craftsvilla_Productdownloadreq_Block_Productdownloadreq extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getProductdownloadreq()     
     { 
        if (!$this->hasData('productdownloadreq')) {
            $this->setData('productdownloadreq', Mage::registry('productdownloadreq'));
        }
        return $this->getData('productdownloadreq');
        
    }
}