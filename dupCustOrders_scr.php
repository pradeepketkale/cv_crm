<?php
require_once 'app/Mage.php';
Mage::app();
$hlp = Mage::helper('generalcheck');

$mainconn = $hlp->getMaindbconnection();

$message1 ='<table border=1>
			<tr bgcolor=#ccc>
			<th width=300>Customer Email</th>
			<th width=150>Total Order Count</th>
			</tr>';
$message2 = '';
$duplicate_query = mysql_query("SELECT `customer_email`, COUNT(*) as `order_count` FROM `sales_flat_order` WHERE `created_at` > DATE_SUB(NOW(), INTERVAL 1 DAY)  GROUP BY `customer_email` HAVING COUNT(*)>1", $mainconn);
mysql_close($mainconn);

		while($duplicate_result = mysql_fetch_array($duplicate_query))
		{

			echo $customer_email = $duplicate_result['customer_email'];
			echo $order_count = $duplicate_result['order_count']; exit;

			$message2 .= "  <tr>
							<td width=300 align='center'> ".$customer_email." </td>
							<td width=150 align='center'> ".$order_count." </td>
							</tr>";

		}

			$message = $message1.$message2."</table>";//echo $message;


					$mail = Mage::getModel('core/email');
					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('Duplicate Orders In last 24hr');
					$mail->setFromEmail('dileswar@craftsvilla.com');
					$mail->setFromName('Craftsvilla Tech');
					$mail->setType('html');
					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('manoj@craftsvilla.com');
					}
					else 
					echo "error";

?>
