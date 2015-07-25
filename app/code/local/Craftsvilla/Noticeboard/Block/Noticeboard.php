<?php
class Craftsvilla_Noticeboard_Block_Noticeboard extends Mage_Core_Block_Template
{
	
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    	
    }
 
 
    
     public function getNoticeboard()     
     { 
        if (!$this->hasData('noticeboard')) {
            $this->setData('noticeboard', Mage::registry('noticeboard'));
        }
        return $this->getData('noticeboard');
        
    }
}