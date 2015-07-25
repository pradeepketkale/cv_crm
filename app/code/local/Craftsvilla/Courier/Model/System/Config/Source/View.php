<?php

class Craftsvilla_Courier_Model_System_Config_Source_View 
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'EbsLink', 'label'=>Mage::helper('adminhtml')->__('EbsLink')),
            array('value' => 'PayuLink', 'label'=>Mage::helper('adminhtml')->__('PayuLink')),
            array('value' => 'Other', 'label'=>Mage::helper('adminhtml')->__('Other')),
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
        'EbsLink' => Mage::helper('adminhtml')->__('EbsLink'),
        'PayuLink' => Mage::helper('adminhtml')->__('PayuLink'),
        'Other' => Mage::helper('adminhtml')->__('Other'),
    );
}
}

?>
