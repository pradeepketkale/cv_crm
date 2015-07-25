<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('couponrequest')};
CREATE TABLE {$this->getTable('couponrequest')} (
  `couponrequest_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` varchar(255) NOT NULL default '',
  `price` varchar(10) NOT NULL default '',
  `expire_date_ofcoupon` datetime NULL,
  `agent_user` varchar(32) NOT NULL default '',
  `created_time` datetime NULL,
  `status_coupon` varchar(30) NOT NULL default '',
  
  PRIMARY KEY (`couponrequest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
