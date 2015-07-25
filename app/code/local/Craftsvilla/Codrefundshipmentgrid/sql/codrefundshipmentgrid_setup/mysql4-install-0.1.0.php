<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codrefundshipmentgrid')};
CREATE TABLE {$this->getTable('codrefundshipmentgrid')} (
  `codrefundshipmentgrid_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(50) NOT NULL DEFAULT '',
  `order_id` varchar(50) NOT NULL DEFAULT '',
  `cust_name` varchar(50) NOT NULL DEFAULT '',
  `accountno` VARCHAR(60) NOT NULL DEFAULT '',	
  `ifsccode` varchar(20) NOT NULL DEFAULT '',
  `paymentamount` varchar(20) NOT NULL DEFAULT '',	
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`codrefundshipmentgrid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
