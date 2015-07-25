<?php
class Craftsvilla_Uagent_Block_Agent_Uagent extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getUagent()     
     { 
        if (!$this->hasData('uagent')) {
            $this->setData('uagent', Mage::registry('uagent'));
        }
        return $this->getData('uagent');
        
    }
}