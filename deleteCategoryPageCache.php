<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$storeId = Mage::app()->getStore()->getId();
$jewelCacheId = "jewelleryjewelryhtml-catalogcategoryviewcategorypathjewelleryjewelryhtmlcategoryjewelleryjewelry";
	Mage::app()->removeCache($jewelCacheId);
	echo "Removed";	
	echo "<br> Done";

?>
