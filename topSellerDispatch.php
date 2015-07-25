<?php

require_once 'app/Mage.php';
Mage::app();


$readId =Mage::getSingleton('core/resource')->getConnection('core_read');

$totalshipment = "select `udropship_vendor`, COUNT(`entity_id`) AS count FROM sales_flat_shipment where `udropship_status` = '1' AND `created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY) GROUP BY `udropship_vendor`";

$sum = 0;
 
$resultshipment =$readId->query($totalshipment)->fetchAll();
$message1 =   
'Last 90 Days Dispatch Report <br>
<table border=1>
<tr bgcolor=#ccc>
<th width=166>Name</th>
<th width=166>Dispatch Time</th>
<th width=166>Email</th>
<th width=166>Telephone</th>
<th width=166>Total Shipments</th>
</tr>
';
$a ='';
$vendordiff = array();

 
foreach($resultshipment as $row){
$vendorID = $row['udropship_vendor'];

$paymentDiff = "SELECT s.created_at as createdat, s.updated_at as updatedat, p.method as paymethod, TIMESTAMPDIFF(DAY,s.created_at,s.updated_at) AS diff FROM `sales_flat_shipment` as s LEFT JOIN `sales_flat_order_payment` as p ON s.order_id = p.parent_id where p.method != 'cashondelivery' AND s.udropship_status = '1' AND s.udropship_vendor = ".$vendorID." AND s.created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)";
$diff = 0;
$diffnum = 0;
$resultpaymentDiff = $readId->query($paymentDiff)->fetchAll();
//while($row1 = mysql_fetch_array($resultpaymentDiff)){
foreach($resultpaymentDiff as $row1){
//arsort($row1['udropship_vendor']);
$diff += $row1['diff'];
$diffnum++;
}
$average = $diff/$diffnum;

$count = $row['count'];

$vendordiff[$vendorID] = round($average,1);

$vendorcount[$vendorID] = $count;
}
$vendordiffsorted = asort($vendordiff);

$numa = count($vendordiff['count']);

$j = 0;

foreach($vendordiff as $keyvendor => $_vendordiffsorted)
{

$countship = $vendorcount[$keyvendor];

if($countship >50){
$vendorDetail = "select `vendor_name`, `email`, `telephone`, `url_key` from udropship_vendor where `vendor_id`='".$keyvendor."'";
$resultvendorDeatail = $readId->query($vendorDetail);
$row2 = $resultvendorDeatail->fetch();
$vname = $row2['vendor_name'];
$vemail = $row2['email'];
$vtelephone = $row2['telephone'];
$urlvendor = "http://www.craftsvilla.com/".$row2['url_key'];
//<td width=100 align='center'>".$keyvendor."</td>
$message2 .= "
<tr>
<td width=200 align='center'><a href='".$urlvendor."'> ".$vname." </a></td>
<td width=200 align='center'> ".$_vendordiffsorted." Days</td>
<td width=200 align='center'> ".$vemail." </td>
<td width=200 align='center'> ".$vtelephone." </td>
<td width=200 align='center'> ".$countship." </td>
";

}
}
$message = $message1.$message2."</table>";

$mail = Mage::getModel('core/email');

$mail->setToName('Craftsvilla');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject('Top Sellers by Dispatch Report');
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Craftsvilla Stats");
$mail->setType('html');

if($mail->send()){
echo 'Email has been send successfully';
$mail->setToEmail('monica@craftsvilla.com');
$mail->send();
}
else
echo "Error";


?>
