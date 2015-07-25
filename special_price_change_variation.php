<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection()->setOrder('entity_id','ASC');
        $products->addAttributeToFilter('status', 1);//enabled
        $products->addAttributeToFilter('visibility', 4);//catalog, search
    	$products->addAttributeToFilter('udropship_vendor',854);//dropship vendor
	$products->addAttributeToSelect('*');
    	//$products->getSelect()->limit( 1 );
	echo "Updating Total Products:";
	echo $products->count(); 
	$i = 0;
        
        foreach($products as $p) 
		{        
		   echo 'ID'.$p->getId()."<br>"; 
		   // $p->getPrice()."<br>"; 
		   $spcl = $p->getSpecialPrice();        
                   if(!is_null($spcl) && ($spcl > 399))
                   {
			echo 'Special Price'.$spcl;
                        $spcl1 = floor($spcl*0.5);
			$shippingcost = 50;
			echo 'Special Price New'.$spcl1;
		   	$p->setSpecialPrice($spcl1)
			  ->setSpecialFromDate(null)
			  ->setSpecialToDate(null)
			  ->setShippingcost($shippingcost)
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
