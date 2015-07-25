<?php
class Craftsvilla_Craftsvillatop_Block_Craftsvillatop extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCraftsvillatop()     
     { 
        if (!$this->hasData('craftsvillatop')) {
            $this->setData('craftsvillatop', Mage::registry('craftsvillatop'));
        }
        return $this->getData('craftsvillatop');
        
    }
}
