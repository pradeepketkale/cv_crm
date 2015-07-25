<?php
class Craftsvilla_Shipmentlite_Block_Shipmentlite extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getShipmentlite()     
     { 
        if (!$this->hasData('shipmentlite')) {
            $this->setData('shipmentlite', Mage::registry('shipmentlite'));
        }
        return $this->getData('shipmentlite');
        
    }
}
