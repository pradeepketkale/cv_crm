<?php
class Craftsvilla_Shipmentpayout_Block_Shipmentpayout extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getShipmentpayout()     
     { 
        if (!$this->hasData('shipmentpayout')) {
            $this->setData('shipmentpayout', Mage::registry('shipmentpayout'));
        }
        return $this->getData('shipmentpayout');
        
    }
}
