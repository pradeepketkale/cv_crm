<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('uagent')};
CREATE TABLE {$this->getTable('uagent')} (
`agent_id` int(11) unsigned NOT NULL auto_increment,
  `agent_name` varchar(255) NOT NULL default '',
  `agent_attn` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `zip` int(11) NOT NULL default '0',
  `country_id` varchar(255) NOT NULL default '',
  `telephone` varchar(255) NOT NULL default '',
  `region` VARCHAR(60) NOT NULL default '',
  `region_id` VARCHAR(60) NOT NULL default '',
  `bank_account_number` VARCHAR(60) NOT NULL default '',
  `bank_name` VARCHAR(60) NOT NULL default '',
  `check_pay_to` VARCHAR(60) NOT NULL default '',
  `bank_ifsc_code` VARCHAR(60) NOT NULL default '',
  `remote_ip` VARCHAR(60) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `password_hash` varchar(255) NOT NULL default '',
  `password_enc` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 