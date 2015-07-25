<?php
/*$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('uagent'),'closing_balance','varchar(50)');
$installer->endSetup();*/



$installer = $this;
 
$installer->startSetup();
$installer->run("

CREATE TABLE {$this->getTable('uagent/cataloguecraftsvilla')}(
`catalog_id` int(11) unsigned NOT NULL auto_increment,
`created_at` datetime NULL,
`filename` varchar(255) NOT NULL default '',
`coupon_code` varchar(255) NOT NULL default '',
`catalogtitle` varchar(255) NOT NULL default '',
`agentid` varchar(60) NOT NULL default '',
`type` VARCHAR(60) NOT NULL default '',
 PRIMARY KEY (`catalog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");



$installer->endSetup(); 