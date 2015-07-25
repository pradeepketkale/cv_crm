<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->_conn->addColumn($this->getTable('sales_flat_quote_item'), 'total_shippingcost', 'decimal');
$setup->addAttribute('quote_item', 'total_shippingcost', array('type' => 'decimal'));

$installer->_conn->addColumn($this->getTable('sales_flat_order_item'), 'total_shippingcost', 'decimal');
$setup->addAttribute('order_item', 'total_shippingcost', array('type' => 'decimal'));

$setup->addAttribute('shipment_item', 'total_shippingcost', array('type' => 'decimal'));

$setup->addAttribute('invoice_item', 'total_shippingcost', array('type' => 'decimal'));

$setup->addAttribute('creditmemo_item', 'total_shippingcost', array('type' => 'decimal'));

$installer->endSetup();
