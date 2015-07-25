<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$lifetime = '30';
$lifetime *= 86400;
$msc=microtime(true);
for($i=0;$i<20;$i++){
$quotes = Mage::getModel('sales/quote')->getCollection();

/* @var $quotes Mage_Sales_Model_Mysql4_Quote_Collection */

$quotes->addFieldToFilter('store_id', '1');
$quotes->addFieldToFilter('updated_at', array('lteq'=>date("Y-m-d", time()-$lifetime)));
		$quotes->getSelect()->limit(10000);

//echo $quotes->getSelect()->__toString(); exit;
$quotes->walk('delete');
echo 'Loop'.$i;
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds
?>
