<?php

$this->startSetup();

$c = $this->_conn;

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eav->updateAttribute('catalog_product', 'udropship_vendor', 'is_required', 0);

$c->addColumn($this->getTable('udropship/vendor'), 'fax', 'varchar(50) after telephone');

$c->addColumn($this->getTable('udropship/vendor_shipping'), 'carrier_code', 'varchar(50)');
$c->addColumn($this->getTable('udropship/vendor_shipping'), 'est_carrier_code', 'varchar(50)');

$this->endSetup();