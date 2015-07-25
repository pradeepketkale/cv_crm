<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mktvendors')};
CREATE TABLE {$this->getTable('mktvendors')} (
  `mktvendors_id` int(11) unsigned NOT NULL auto_increment,
  `package_name` varchar(255) NOT NULL default '',
  `vendor` varchar(255) NOT NULL default '',
  `paidamount` varchar(255) NOT NULL default '',
  `balance` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `date_bought` datetime NULL,
  `valid_till` datetime NULL,
  PRIMARY KEY (`mktvendors_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 