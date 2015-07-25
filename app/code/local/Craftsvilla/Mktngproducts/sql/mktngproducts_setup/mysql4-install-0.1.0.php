<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mktngproducts')};
CREATE TABLE {$this->getTable('mktngproducts')} (
  `mktngproducts_id` int(11) unsigned NOT NULL auto_increment,
  `product_sku` varchar(32) NOT NULL default '',
  `created_at` DATETIME NULL,
  `product_url` varchar(120) NOT NULL default '',
  `vendor_name` varchar(32) NOT NULL default '',
  `sale_one_day` varchar(32) NOT NULL default '',
  `sale_seven_days` varchar(32) NOT NULL default '',
  `sale_thirty_days` varchar(32) NOT NULL default '',
  PRIMARY KEY (`mktngproducts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
