<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('managemkt')};
CREATE TABLE {$this->getTable('managemkt')} (
  `managemkt_id` int(11) unsigned NOT NULL auto_increment,
  `parent_id` int(11),
  `activity` varchar(255) NOT NULL default '',
  `vendorname` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `start_date` datetime NULL,
  `end_date` datetime NULL,
  PRIMARY KEY (`managemkt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 