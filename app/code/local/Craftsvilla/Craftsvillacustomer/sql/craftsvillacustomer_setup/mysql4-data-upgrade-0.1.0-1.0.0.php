<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('emailcommunication')};
CREATE TABLE {$this->getTable('emailcommunication')} (
  `emailcommunication_id` int(11) unsigned NOT NULL auto_increment,
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `message_id` varchar(20) NOT NULL,
  `recepient_email` varchar(255) NULL,
  `recepient_name` varchar(255) NULL,
  `image` varchar(255) NULL,
  `subject` varchar(255) NULL, 
  `content` varchar(255) NULL,
  `type` tinyint(1) default 0 NOT NULL,
  `created_at` datetime not null,  
PRIMARY KEY (`emailcommunication_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 


  
