<?php
class Craftsvilla_Craftsvillapickupreference_Block_Craftsvillapickupreference extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCraftsvillapickupreference()     
     { 
        if (!$this->hasData('craftsvillapickupreference')) {
            $this->setData('craftsvillapickupreference', Mage::registry('craftsvillapickupreference'));
        }
        return $this->getData('craftsvillapickupreference');
        
    }
}
