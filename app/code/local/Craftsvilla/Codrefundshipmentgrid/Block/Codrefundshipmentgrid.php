<?php
class Craftsvilla_Codrefundshipmentgrid_Block_Codrefundshipmentgrid extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCodrefundshipmentgrid()     
     { 
        if (!$this->hasData('codrefundshipmentgrid')) {
            $this->setData('codrefundshipmentgrid', Mage::registry('codrefundshipmentgrid'));
        }
        return $this->getData('codrefundshipmentgrid');
        
    }
}
