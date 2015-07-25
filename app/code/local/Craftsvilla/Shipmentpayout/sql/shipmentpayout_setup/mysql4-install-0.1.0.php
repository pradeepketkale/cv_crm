<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('shipmentpayout')};
CREATE TABLE {$this->getTable('shipmentpayout')} (
  `shipmentpayout_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(50) NOT NULL default '',
  `order_id` varchar(50) NOT NULL default '',
  `shipmentpayout_status` smallint(6) NOT NULL default '0',
  `shipmentpayout_created_time` datetime NULL,
  `citibank_utr` varchar(50) NOT NULL default '',
  `shipmentpayout_update_time` datetime NULL,
  PRIMARY KEY (`shipmentpayout_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
