<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('agentpayout')};
CREATE TABLE {$this->getTable('agentpayout')} (
  `agentpayout_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(50) NOT NULL default '',
  `agentpayout_status` smallint(6) NOT NULL default '0',
  `agentpayout_created_time` datetime NULL,
  `agentpayout_update_time` datetime NULL,
  `payment_amount` decimal(12,4),
  `couponcode` varchar(50),
  `couponcodeamount` varchar(60),
  `commission_amount` varchar(60),
  `agent_commission` varchar(50), 	
  `comment` varchar(60),
  PRIMARY KEY (`agentpayout_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
