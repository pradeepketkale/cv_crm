<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

Mage::app()->getCacheInstance()->cleanType('config');
Mage::app()->getCacheInstance()->cleanType('layout');

//Mage::app()->getCacheInstance()->cleanType('block_html');
//Mage::app()->getCacheInstance()->cleanType('translate');
//Mage::app()->getCacheInstance()->cleanType('collections');
//Mage::app()->getCacheInstance()->cleanType('eav');
//Mage::app()->getCacheInstance()->cleanType('config_api');


echo "cleaned pls chk";
?>
