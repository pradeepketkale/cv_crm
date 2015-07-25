<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('follow')};
CREATE TABLE {$this->getTable('follow')} (
  `follow_id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `status` smallint(1) NOT NULL default '1',
  PRIMARY KEY (`follow_id`),
KEY `FK_follow_vendor` (`vendor_id`),
KEY `FK_follow_customer` (`customer_id`),
CONSTRAINT `FK_follow_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_follow_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->endSetup(); 
