<?php
class Craftsvilla_Follow_Block_Follow extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFollowVendor()
	{
		$vendor = '';
		if($vendor = $this->helper('umicrosite')->getCurrentVendor())
			return $vendor;
		elseif($vendor = $this->helper('udropship')->getVendor(Mage::registry('product')))
			return $vendor;
	}
}
