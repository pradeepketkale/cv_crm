<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$this->startSetup(); 

$this->run("
CREATE TABLE `{$this->getTable('udropship_vendor_stats')}` (
`id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`stat_type` varchar(50) NOT NULL,
`date` datetime NOT NULL default '0000-00-00 00:00:00',
`pageviews` int(11) NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('udropship_vendor_productstats')}` (
`id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`product_id` int(11) unsigned NOT NULL,
`pageviews` int(11) NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
?>
