<?php
class Craftsvilla_Managemkt_Block_Managemkt extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getManagemkt()     
     { 
        if (!$this->hasData('managemkt')) {
            $this->setData('managemkt', Mage::registry('managemkt'));
        }
        return $this->getData('managemkt');
        
    }
}