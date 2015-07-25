<?php
error_reporting(E_ALL & ~E_NOTICE);
//require_once '../app/Mage.php';
//Mage::app();
//$xml = 'http://development.craftsvilla.com/rssxmlfiles/product.xml';
//$xml = '/var/www/html/rssxmlfiles/cv-product-1.xml';
//$xml = '/var/www/html/rssxmlfiles/cv-product-2.xml';
//$xml = '/var/www/html/rssxmlfiles/cv-product-3.xml';
$xml = '/var/www/html/rssxmlfiles/cv-product-4.xml';

$savexml = file_get_contents($xml);
//file_put_contents('/var/www/html/media/vendorxml/file.xml', $savexml);

//$doc = new DOMDocument();
// $result =  $doc->loadXML($savexml);
libxml_use_internal_errors(true);
$doc = simplexml_load_file($xml);

if ($doc == false) {
    echo "Failed loading XML\n";
    foreach(libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}
else{
echo 'Load Successful Without Errors!';
//foreach($doc->item as $item){
//echo 'id'.$item->productid; }
//print_r($doc);exit;}
}
