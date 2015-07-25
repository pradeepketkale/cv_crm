<?php

class Craftsvilla_Courier_Model_System_Config_Source_Selectcourier 
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        
return array(
            array('value' => 'Fedex', 'label'=>Mage::helper('adminhtml')->__('Fedex')),
            array('value' => 'Aramex', 'label'=>Mage::helper('adminhtml')->__('Aramex')),
            array('value' => 'Delhivery', 'label'=>Mage::helper('adminhtml')->__('Delhivery')),
        );
    }

/**
 * Get options in "key-value" format
 *
 * @return array
 */
public function toArray()
{
    return array(
        'Fedex' => Mage::helper('adminhtml')->__('Fedex'),
        'Aramex' => Mage::helper('adminhtml')->__('Aramex'),
        'Delhivery' => Mage::helper('adminhtml')->__('Delhivery'),
    );
}
}

?>
