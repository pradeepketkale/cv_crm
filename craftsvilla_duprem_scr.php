<?php
require_once 'app/Mage.php';
Mage::app();

$_products = array('1770019','1772398','978495','1682343','1540444');

foreach($_products as $_prodId)
{
    $_product = Mage::helper('catalog/product')->loadnew($_prodId);
    //echo "<pre>";print_r($_product);exit;
    
    $base_image = $_product->getImage();
     
    //echo "<pre>";print_r($base_image);exit;
    $imagedata = file_get_contents("http://assets1.craftsvilla.com/catalog/product/cache/1/small_image/166x166/9df78eab33525d08d6e5fb8d27136e95/".$base_image);

    $md1 = md5($imagedata);

    echo $_prodId." = ".$md1."<br>";
}


?>

