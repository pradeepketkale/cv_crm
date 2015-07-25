<?php

$installer = $this;

$installer->startSetup();

$installer->run("


CREATE TABLE {$this->getTable('bulkuploadcsv')} (
  `bulkuploadid` int(11) unsigned NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `vendor` varchar(255) NOT NULL default '',
  `productsactiveted` varchar(255) NOT NULL default '',
  `productsrejected` varchar(255) NOT NULL default '',
  `totalproducts` varchar(255) NOT NULL default '',
  `errorreport` varchar(255) NOT NULL default '',  
  `uploaded` datetime NULL,
   PRIMARY KEY (`bulkuploadid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 