<?php
class Craftsvilla_Agentpayout_Block_Agentpayout extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getAgentpayout()     
     { 
        if (!$this->hasData('agentpayout')) {
            $this->setData('agentpayout', Mage::registry('agentpayout'));
        }
        return $this->getData('agentpayout');
        
    }
}