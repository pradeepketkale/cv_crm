<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
//$vendorId = '854';
$storeId = Mage::app()->getStore()->getId();
$inrCacheId = "newarrivals-INR-count-$storeId";
$usdCacheId = "newarrivals-USD-count-$storeId";
	Mage::app()->removeCache($inrCacheId);
	Mage::app()->removeCache($usdCacheId);
	echo "Removed";	
	echo "<br> Done";

?>
