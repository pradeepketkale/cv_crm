<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('vendoractivityremark')};
CREATE TABLE {$this->getTable('vendoractivityremark')} (
  `vendoractivityremark_id` int(11) unsigned NOT NULL auto_increment,
  `vendorid` varchar(20) NOT NULL default '',
   `catalogprivilegesremark` varchar(255) NOT NULL default '',
  `logisticsprivilegesremark` varchar(255) NOT NULL default '',
   `paymentprivilegesremark` varchar(255) NOT NULL default '',
  PRIMARY KEY (`vendoractivityremark_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
