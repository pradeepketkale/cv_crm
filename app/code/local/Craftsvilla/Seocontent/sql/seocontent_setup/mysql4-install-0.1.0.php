<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('seocontent')};
CREATE TABLE {$this->getTable('seocontent')} (
  `seocontent_id` int(11) unsigned NOT NULL auto_increment,
  `category` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`seocontent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 