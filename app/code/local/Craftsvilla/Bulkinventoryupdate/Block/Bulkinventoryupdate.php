<?php
class Craftsvilla_Bulkinventoryupdate_Block_Bulkinventoryupdate extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBulkinventoryupdate()     
     { 
        if (!$this->hasData('bulkinventoryupdate')) {
            $this->setData('bulkinventoryupdate', Mage::registry('bulkinventoryupdate'));
        }
        return $this->getData('bulkinventoryupdate');
        
    }
}