<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('customerreturn')};
CREATE TABLE {$this->getTable('customerreturn')} (
  `customerreturn_id` int(11) unsigned NOT NULL auto_increment,
 `shipment_id` varchar(60) NOT NULL default '',
  `trackingcode` varchar(60) NOT NULL default '',
  `couriername` varchar(60) NOT NULL default '',
  `status` varchar(60) NOT NULL default '',	
  `created_at` datetime NULL,
  `update_at` datetime NULL,
  PRIMARY KEY (`customerreturn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
