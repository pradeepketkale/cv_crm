<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection()->setOrder('entity_id','DESC');
        $products->addAttributeToFilter('status', 1);//enabled
        $products->addAttributeToFilter('visibility', 4);//catalog, search
    	$products->addAttributeToFilter('udropship_vendor',854);//dropship vendor
	$products->addAttributeToSelect('*');
    	//$products->getSelect()->limit( 1 );
	echo "Updating Total Products:";
	echo $products->count(); 
	$i = 0;
       $count = 0; 
        foreach($products as $p) 
		{        
		   echo 'ID'.$p->getId()."\n"; 
		   // $p->getPrice()."<br>"; 
		   echo $spcl = $p->getSpecialPrice();        
		   echo $priceOrg = $p->getPrice();        
                   if(!is_null($spcl) && ($spcl > 199) && ($priceOrg > 2.2*$spcl))
                   {

			echo '\n Special Price'.$spcl;
                        $spcl1 = floor($spcl*2);
			echo '\n New Special Price'.$spcl1; 
			$shippingcost = 0;
			echo 'Special Price New'.$spcl1;
		   	$p->setSpecialPrice($spcl1)
			  ->setSpecialFromDate(null)
			  ->setSpecialToDate(null)
			  ->setShippingcost($shippingcost)
			  ->save();
			$count++;
		   }
		   else
		   {
			echo "Product Skipped";
		   }
	           echo $i++;
       		} 
	echo "<br> Done";
	echo '\n Total Changed'.$count;

?>
