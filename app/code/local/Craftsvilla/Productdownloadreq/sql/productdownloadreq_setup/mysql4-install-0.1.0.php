<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('productdownloadreq')} (
  `productdownloadreq_id` int(11) unsigned NOT NULL auto_increment,
  `activity` varchar(255) NOT NULL default '',
  `vendorname` varchar(255) NOT NULL default '',
 `status` smallint(6) NOT NULL default '0',
  `csvdownload` varchar(150),
   PRIMARY KEY (`productdownloadreq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
