<?php

require_once 'app/Mage.php';
Mage::app();



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

//mysql_select_db('doejofinal',mysql_connect('localhost','root','admin123'));

 $querydayFedex= $db->query("select * from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND courier_name = 'Fedex' ")->fetch();
 $num_rows_day_Fedex = mysql_num_rows($querydayFedex);
$queryweekFedex= $db->query("select * from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND courier_name = 'Fedex' ")->fetch();
$num_rows_week_Fedex = mysql_num_rows($queryweekFedex);
$querymonthFedex= $db->query("select * from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND courier_name = 'Fedex' ")->fetch();
$num_rows_month_Fedex = mysql_num_rows($querymonthFedex);



 $querydayAramex= $db->query("select 	* from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND courier_name = 'Aramex' ")->fetch();
 $num_rows_day_Aramex = mysql_num_rows($querydayAramex);
$queryweekAramex= $db->query("select * from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND courier_name = 'Aramex' ")->fetch();
$num_rows_week_Aramex = mysql_num_rows($queryweekAramex);
$querymonthAramex= $db->query("select * from sales_flat_shipment_track where `created_at` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND courier_name = 'Aramex' ")->fetch();
$num_rows_month_Aramex = mysql_num_rows($querymonthAramex);



$message = "
<table align= center>
<caption><b>Shipments Assigned to Fedex</b></caption>
<tr bgcolor=#ccc >
<th width=150 >24 hours</th>
<th width=150>7 days</th>
<th width=150>30 days</th>
</tr>
<tr bgcolor=#ccc align= center >
<td width=150;>".$num_rows_day_Fedex."</td>
<td width=150>".$num_rows_week_Fedex."</td>
<td width=150>".$num_rows_month_Fedex."</td>
</tr>
</table>
<br><br>
<table align= center>
<caption><b>Shipments Assigned to Aramex</b></caption>
<tr bgcolor=#ccc >
<th width=150  >24 hours</th>
<th width=150>7 days</th>
<th width=150>30 days</th>
</tr>
<tr align= center bgcolor=#ccc >
<td width=150;>".$num_rows_day_Aramex."</td>
<td width=150>".$num_rows_week_Aramex."</td>
<td width=150>".$num_rows_month_Aramex."</td>
</tr>
</table>

";
 echo "$message"; 

					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('Fedex Aramex Count');
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Logistics");
					$mail->setType('html');
 

					if($mail->send())
	{
		$mail->setToEmail('monica@craftsvilla.com');
		$mail->send();
	echo "Email sent to your emailid sucessfully";
	}
	else {
		echo "Failed to send Email";
	}
echo "<br>";
?>

