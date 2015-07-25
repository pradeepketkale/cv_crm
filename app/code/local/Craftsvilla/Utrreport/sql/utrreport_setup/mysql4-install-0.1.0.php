<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('utrreport')};
CREATE TABLE {$this->getTable('utrreport')} (
  `utrreport_id` int(11) unsigned NOT NULL auto_increment,
  `utrno` varchar(255) NOT NULL default '',
  `payin_date` datetime NULL,
  `amount` varchar(255) NOT NULL default '',
  `balance` varchar(255) NOT NULL default '',
  `utrpaid` varchar(255) NOT NULL default '',
  PRIMARY KEY (`utrreport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 