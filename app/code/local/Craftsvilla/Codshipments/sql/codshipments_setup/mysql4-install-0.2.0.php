<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codshipments')};
CREATE TABLE {$this->getTable('codshipments')} (
  `codshipments_id` int(11) unsigned NOT NULL auto_increment,
  `waybill` varchar(255) NOT NULL default '',
  `orderno` varchar(255) NOT NULL default '',
  `consigneename` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `state` varchar(255) NOT NULL default '',
  `country` varchar(255) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `pincode` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `mobile` varchar(255) NOT NULL default '',
  `paymentmode` varchar(255) NOT NULL default '',
  `packageamount` varchar(255) NOT NULL default '',
  `codamount` varchar(255) NOT NULL default '',
  `productshipped` varchar(255) NOT NULL default '',
  `shippingclient` varchar(255) NOT NULL default '',
  `shipclientaddress` varchar(255) NOT NULL default '',
  `shipclientphone` varchar(255) NOT NULL default '',

  PRIMARY KEY (`codshipments_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 