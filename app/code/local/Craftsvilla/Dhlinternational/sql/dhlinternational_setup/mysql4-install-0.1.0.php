<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('dhlinternational')};
CREATE TABLE {$this->getTable('dhlinternational')} (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '',
  `status_awb` text NOT NULL,
  `tracking_awb` varchar(100) DEFAULT NULL,
 
  PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 



