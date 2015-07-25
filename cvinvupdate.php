<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$newstocklevel = 1;//add the quantity no.
$product_id = Mage::getModel('catalog/product')->getIdBySku('CMUADIVA1000368');
$product = Mage::getModel('catalog/product');
$product ->load($product_id);
$stockData = $product->getStockData();
$stockData['qty'] = $newstocklevel;
$stockData['is_in_stock'] = 1;
$product->setStockData($stockData);
$product->save();
echo 'sku updated';
?>
