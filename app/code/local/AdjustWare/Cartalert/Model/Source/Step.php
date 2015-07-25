<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.4.1.x-1.5.0.1 - 06/07/11
 * Package:     AdjustWare_Cartalert_3.0.5_0.2.3_183688
 * Purchase ID: Y6M1PHMt9YjaYLDNXoI3HVQQ5WLuo3S19F0xW5tLYM
 * Generated:   2012-02-06 21:31:16
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Source/Step.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ EMVOrmkZdmeNqkqX('50ccb4cda777575db8c4899cc4f6f5c5'); ?><?php
class AdjustWare_Cartalert_Model_Source_Step extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array(
            0 => array(
                'value' => '',
                'label' => '-'
            )
        );

        foreach (array('first','second','third') as $step)
            $options[] = array(
                'value'=> $step,
                'label' => Mage::helper('adjcartalert')->__(ucfirst($step). ' Email Template')
            );
        
        return $options;
    }
} } 