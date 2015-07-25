<?php
class Craftsvilla_Mktngproducts_Block_Mktngproducts extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getMktngproducts()     
     { 
        if (!$this->hasData('mktngproducts')) {
            $this->setData('mktngproducts', Mage::registry('mktngproducts'));
        }
        return $this->getData('mktngproducts');
        
    }
}
