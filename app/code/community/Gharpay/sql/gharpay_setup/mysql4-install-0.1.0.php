<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('gharpay')};
CREATE TABLE IF NOT EXISTS `gharpay` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL default '0',
  `gharpay_order_id` varchar(100) NOT NULL default '',
  `order_status` varchar(100) NOT NULL default 'New',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 