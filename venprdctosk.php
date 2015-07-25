<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$qty = 0;

$storeId = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->setStoreId($storeId);
        $products = $_products->getCollection()->setOrder('entity_id','ASC');
        $products->addAttributeToFilter('status', 1);//enabled
        $products->addAttributeToFilter('visibility', 4);//catalog, search
    	$products->addAttributeToFilter('udropship_vendor',2211);//dropship vendor
		$products->addAttributeToSelect('*');
		$products->getSelect()->limit(15);

	foreach($products as $pdt){
		$product_id = $pdt->getSku();
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$product_id);
			if($product)
			{
	 		$productId = $product->getId();
	 		$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	 		$stockItemId = $stockItem->getId();
	 		$stockItem['is_in_stock'] = 1;
	 		$stock = array();
	  
					 if (!$stockItemId) {
					 $stockItem->setData('product_id', $product->getId());
					 $stockItem->setData('stock_id', 1);
					 $stockItem['is_in_stock'] = 0;			
					 } else {
					 $stock = $stockItem->getData();
					 }
	  
	  
	 	foreach($stock as $_stock)
	 		{
			$stockItem->setData('qty',$qty);
			}
	 //print_r($stockItem->getData());
	 $stockItem->save();
	 }
	 echo 'sku saved'.$product_id;
}
