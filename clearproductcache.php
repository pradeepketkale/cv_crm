<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
//$id='productcache-165393-currency-INR';
//$id='productcache-165040-currency-INR';
$id='productcache-195428-currency-INR';
echo "Removing".$id;
Mage::app()->removeCache($id);
echo "Removed".$id;
?>
