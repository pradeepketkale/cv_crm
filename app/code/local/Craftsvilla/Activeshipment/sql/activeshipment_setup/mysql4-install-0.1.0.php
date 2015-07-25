<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('activeshipment')};
CREATE TABLE {$this->getTable('activeshipment')} (
  `activeshipment_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(255) NOT NULL default '',
  `cust_status` varchar(255) NOT NULL default '',
  `primary_category` text NOT NULL default '',
  `expected_shipingdate` datetime NULL,
  `vendor_claimedfrom` varchar(255) NOT NULL default '',
  `vendor_claimedto` varchar(255) NOT NULL default '',
  `claimed_date` datetime NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`activeshipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 