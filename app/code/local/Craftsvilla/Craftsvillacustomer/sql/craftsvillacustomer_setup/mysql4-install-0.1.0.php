<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('craftsvillacustomer')};
CREATE TABLE {$this->getTable('craftsvillacustomer')} (
  `craftsvillacustomer_id` int(11) unsigned NOT NULL auto_increment,
  `cust_id` int(11) unsigned NULL,
  `cust_email` varchar(255) NULL,
  `cust_name` varchar(255) NULL,
  `cust_dob` varchar(255) NULL,
  `cust_gender` text NULL, 
  `shopping_interest` varchar(255) NULL,
  `shopping_categories` varchar(255) NULL,
  `describes_you` varchar(255) NULL,
  `have_child` varchar(255) NULL,
PRIMARY KEY (`craftsvillacustomer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 


  
