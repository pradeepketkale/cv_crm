<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('disputeraised')};
CREATE TABLE {$this->getTable('disputeraised')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `increment_id` varchar(255) NOT NULL DEFAULT '', 
  `customer_id` varchar(255),
  `vendor_id` varchar(255),
  `image` varchar(255) NOT NULL default '',
  `content` varchar(255),
  `addedby` varchar(30),
  `status` varchar(15) NOT NULL default '',
  `created_at` datetime NULL,
  `updated_at` datetime NULL, 
   PRIMARY KEY ( `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 