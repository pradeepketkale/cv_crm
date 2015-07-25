<?php 
session_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();
$header = getHeader();

$orders = Mage::getModel('sales/order')->getCollection()
->addFieldToFilter('status', array('in' => array('complete')))
->getData();


$rowData = '';
foreach($orders as $ord) {
		$order = Mage::getModel('sales/order')->load($ord['entity_id']);
		$rowData .= getRowData($order);
}


$data = '<div>	
            <table width="100%" border="1" cellspacing="0" cellpadding="5">';
$data .= $header;
$data .= $rowData;
$data .= '</table></div><br>';

$mail_data=$data;

header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=file.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo $mail_data;

function getHeader() {
	return 	'<tr>
                    <th width="35%">Product Name</th>
                    <th width="20%">SKU</th>
                    <th width="15%">Price</th>
                    <th width="15%">Qty</th>
                </tr>';
}

function getRowData($order) {
	$items = $order->getAllVisibleItems();

	$i=0;
	foreach ($items as $itemId => $item)
	{
            $sku = $item->getSku();
            $product_name = $item->getName();
            $price=$item->getPrice();
            $qty=$item->getQtyOrdered();

           $data .= '<tr>
			<td>'.$product_name.'</td> 
                        <td>'.$sku.'</td> 
                        <td>'.$price.'</td>
                        <td>'.$qty.'</td>
                      </tr>';
        }

	return $data;
}
?>