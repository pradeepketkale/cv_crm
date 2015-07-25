<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('refundrequestedcustomer')};
CREATE TABLE {$this->getTable('refundrequestedcustomer')} (
  `refundrequestedcustomer_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(32) NOT NULL default '',
  `customer_name` varchar(60) NOT NULL default '',
  `account_number` varchar(32) NOT NULL default '',
  `name_on_account` varchar(120) NOT NULL default '',
  `ifsccode` varchar(20) NOT NULL default '',
  `trackingcode` varchar(32) NOT NULL,
  `couriername` varchar(32) NOT NULL default '',
  `created_at` DATETIME NULL,
  `refund_status` varchar(30) NOT NULL default '',
  PRIMARY KEY (`refundrequestedcustomer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
