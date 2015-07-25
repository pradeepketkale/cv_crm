<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection()->setOrder('entity_id','DESC');
        $products->addAttributeToFilter('status', 1);//enabled
        $products->addAttributeToFilter('visibility', 4);//catalog, search
    	$products->addAttributeToFilter('udropship_vendor',2958);//dropship vendor
	$products->addAttributeToSelect('*');
//    	$products->getSelect()->limit( 1 );
	echo "Updating Total Products:"; 
	echo $products->count(); exit;
	$i = 0;
        
        foreach($products as $p) 
		{        
		   echo 'ID'.$p->getId()."<br>";
		   $spcl = $p->getSpecialPrice();        
                   if(is_null($spcl))
                   {
			$originalprice = $p->getPrice();
			echo 'Current Price:'.$originalprice;
			$neworigprice = 2*$originalprice;
                        $spcl1 = floor($originalprice);
			echo 'Special Price New'.$spcl1;
		   	$p->setSpecialPrice($spcl1)
			  ->setPrice($neworigprice)
			  ->setSpecialFromDate(null)
			  ->setSpecialToDate(null)
			  ->save();
		   }
		   else
		   {
			echo "Product Skipped";
		   }
	           echo $i++;
       		} 
	echo "<br> Done";

?>
