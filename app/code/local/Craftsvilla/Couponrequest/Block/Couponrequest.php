<?php
class Craftsvilla_Couponrequest_Block_Couponrequest extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCouponrequest()     
     { 
        if (!$this->hasData('Couponrequest')) {
            $this->setData('Couponrequest', Mage::registry('Couponrequest'));
        }
        return $this->getData('Couponrequest');
        
    }
}
