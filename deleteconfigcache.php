<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
//$vendorId = '854';
$storeId = Mage::app()->getStore()->getId();
$configCacheId = "CONFIG";
$layoutCacheId = "LAYOUT_GENERAL_CACHE_TAG";
$blockCacheId = "BLOCK_HTML";
	Mage::app()->removeCache($configCacheId);
	Mage::app()->removeCache($layoutCacheId);
	Mage::app()->removeCache($blockCacheId);
	echo "Removed";	
	echo "<br> Done";

?>
