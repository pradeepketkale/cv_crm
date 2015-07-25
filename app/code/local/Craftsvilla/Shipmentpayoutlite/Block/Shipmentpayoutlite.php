<?php
class Craftsvilla_Shipmentpayoutlite_Block_Shipmentpayoutlite extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getShipmentpayoutlite()     
     { 
        if (!$this->hasData('shipmentpayoutlite')) {
            $this->setData('shipmentpayoutlite', Mage::registry('shipmentpayoutlite'));
        }
        return $this->getData('shipmentpayoutlite');
        
    }
}
