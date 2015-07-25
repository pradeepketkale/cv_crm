<?php

$installer = $this;

$installer->startSetup();

$installer->run("


CREATE TABLE {$this->getTable('noticeboard')} (
  `noticeid` int(11) unsigned NOT NULL auto_increment,
  `content` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `vendor` varchar(255) NOT NULL default '',
  `priority` varchar(255) NOT NULL default '',
  `created` datetime NULL,
  `image` varchar(255) NOT NULL default '',
   PRIMARY KEY (`noticeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");

$installer->endSetup(); 