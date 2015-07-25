<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('creditmemo_item', 'total_shippingcost', array('type' => 'decimal'));
$setup->addAttribute('creditmemo_item', 'shippingcost', array('type' => 'decimal'));

$installer->endSetup();
