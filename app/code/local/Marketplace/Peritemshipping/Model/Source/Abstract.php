<?php

class Marketplace_Peritemshipping_Model_source_Abstract extends Varien_Object
{

	public function toOptionArray()
	{
      	$carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carrierInstances as $code => $carrier) {
                $carriers[] = array('label'=>$carrier->getConfigData('title'), 'value'=>$code);
            }
        return $carriers;
	}
}
