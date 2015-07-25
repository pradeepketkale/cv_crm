<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$this->startSetup();

    $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $setup->addAttribute('shipment', 'itemised_total_shippingcost', array('type' => 'decimal'));
    
$this->endSetup();
?>
