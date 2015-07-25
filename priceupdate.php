<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection()->setOrder('entity_id','DESC');
        $products->addAttributeToFilter('status', 1);//enabled
        $products->addAttributeToFilter('visibility', 4);//catalog, search
    	$products->addAttributeToFilter('udropship_vendor',1619);//dropship vendor
		$products->addAttributeToSelect('*');
    	//$products->getSelect()->limit( 1 );
        //echo'<pre>';print_r($products);exit;
	echo 'Total Products to Update:'.$products->count(); 
	$i = 0;
        foreach($products as $p) {        
			   $i++;
			   echo ' ID'.$p->getId()."<br>"; 
			   $originalprice = $p->getPrice();
		       	   $spcl = $p->getSpecialPrice();        
			   echo 'original price:'.$originalprice.' special price: '.$spcl.'<br>';
		           $neworigprice = floor($originalprice*1.07);
			   $p->setPrice($neworigprice);
				if(!is_null($spcl))
				{
					$newspclprice = floor($spcl*1.07);
					//$newspclprice = floor($neworigprice*0.7);
					$p->setSpecialPrice($newspclprice)
					->setSpecialFromDate(null)
					->setSpecialToDate(null);
				}
				$p->save();
		echo $i.'<br> done product'.$p->getSku();
			} 
		echo 'Done Total updated '.$i;

?>
