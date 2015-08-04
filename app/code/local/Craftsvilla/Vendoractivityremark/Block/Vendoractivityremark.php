<?php
class Craftsvilla_Vendoractivityremark_Block_Vendoractivityremark extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getVendoractivityremark()     
     { 
        if (!$this->hasData('vendoractivityremark')) {
            $this->setData('vendoractivityremark', Mage::registry('vendoractivityremark'));
        }
        return $this->getData('vendoractivityremark');
        
    }
}
