<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('wholesale')};
CREATE TABLE {$this->getTable('wholesale')} (
  `wholesale_id` int(11) unsigned NOT NULL auto_increment,
  `productid` varchar(255) NOT NULL default '',
  `productname` varchar(255) NOT NULL default '',
  `vendorid` varchar(255) NOT NULL default '',
  `sku` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `quantity` varchar(255) NOT NULL default '',
  `offer_price` varchar(255) NOT NULL default '',
  `custom` varchar(255) NOT NULL default '',
  `comments` varchar(255) NOT NULL default '',
  `created_date` datetime NULL,
  `expected_date` datetime NULL,
  PRIMARY KEY (`wholesale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 