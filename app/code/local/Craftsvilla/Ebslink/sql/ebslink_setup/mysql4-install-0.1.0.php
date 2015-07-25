<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('ebslink')};
CREATE TABLE {$this->getTable('ebslink')} (
  `ebslink_id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(255) NOT NULL default '',
  `ebslinkurl` varchar(255) NOT NULL default '',
  `comment` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  PRIMARY KEY (`ebslink_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 