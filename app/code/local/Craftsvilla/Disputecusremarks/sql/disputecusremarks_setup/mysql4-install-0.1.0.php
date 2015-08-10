<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('disputecusremarks')};
CREATE TABLE {$this->getTable('disputecusremarks')} (
  `disputecusremarks_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(20) NOT NULL default '',
  `vendor_id` varchar(20) NOT NULL default '',
  `vendor_name` varchar(32) NOT NULL default '',
  `remarks` varchar(120) NOT NULL default '',
  PRIMARY KEY (`disputecusremarks_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
