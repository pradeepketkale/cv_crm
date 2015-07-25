<?php
class Craftsvilla_Sellerqualitycraftsvilla_Block_Sellerqualitycraftsvilla extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getSellerqualitycraftsvilla()     
     { 
        if (!$this->hasData('sellerqualitycraftsvilla')) {
            $this->setData('sellerqualitycraftsvilla', Mage::registry('sellerqualitycraftsvilla'));
        }
        return $this->getData('sellerqualitycraftsvilla');
        
    }
}
