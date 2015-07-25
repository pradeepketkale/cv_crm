<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$vendorId = '854';
$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection();
        $products->addAttributeToFilter('udropship_vendor',$vendorId);//dropship vendor
	$products->addAttributeToSelect('*');
   
        
        foreach($products as $p) {        
  
	$pId = $p->getId();
        $id='categorycache-'.$pId.'-currency-INR-';
        $id1='categorycache-'.$pId.'-currency-INR-1';
	echo "Removing".$id;
	echo "Removing".$id1;
	Mage::app()->removeCache($id);
	Mage::app()->removeCache($id1);
	echo "Removed".$id;	
	} 
	echo "<br> Done";

?>
