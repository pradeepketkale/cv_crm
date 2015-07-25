<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('homepage')};
CREATE TABLE {$this->getTable('homepage')} (
  `homepage_id` int(11) unsigned NOT NULL auto_increment,
  `sku` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `product_id` VARCHAR(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
   PRIMARY KEY (`homepage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 