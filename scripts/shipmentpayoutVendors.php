<?php
require_once '../app/Mage.php';
Mage::app();
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function mailSend($vendor,$random) { 
	
	 $readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
	 $message = '<a href="local.craftsvilla.com/media/shipmentpayout/payout'.$vendor.'_'.$random.'.csv">ClickHereForShipmentPayoutDetails</a>'; 
	 $vendorEmail = "SELECT `email` FROM `udropship_vendor` WHERE `vendor_id` = '".$vendor."'"; 
	 $vendorEmailRes  = $readcon->query($vendorEmail)->fetch(); 
		
	 $mail = Mage::getModel('core/email');
					
					$mail->setToName('craftsvilla');
					$mail->setToEmail($vendorEmailRes['email']);
					$mail->setBody($message);
					$mail->setSubject('Vendor Payout Details');
					$mail->setFromEmail('customercare@craftsvilla.com');
					$mail->setFromName("Craftsvilla Places");
					$mail->setType('html');
					//$mailerror = $mail->send();
					//print_r($mailerror); exit;
					

					if($mail->send())
	{
	echo "Email sent to your emailid sucessfully - ".$vendor."\n"; 
	}
	else {
	echo "Failed to send Email"; 
	} 
	
}

	$header_row= array('Shipment Id','Date','Payment Amount','Commission Amount','Payment Type');
	$basepath = Mage::getBaseDir();
	
	$filepath = $basepath.DS.'media'.DS.'shipmentpayout'.DS;

$shipVendors = "SELECT sp.shipment_id,sp.shipmentpayout_update_time,sp.`payment_amount`,sp.`commission_amount`,sp.`type`,sfs.udropship_vendor FROM `shipmentpayout` as sp LEFT JOIN `sales_flat_shipment` as sfs ON sp.shipment_id = sfs.increment_id WHERE `shipmentpayout_status` = 1 AND `shipmentpayout_update_time` > DATE_SUB(NOW(), INTERVAL 24 HOUR) order by sfs.udropship_vendor";


$shipVendorsRes = $readcon->query($shipVendors)->fetchAll();
//echo '<pre>'; print_r($shipVendorsRes); exit;
$current_vendor=0;
$current_random = '';
 foreach($shipVendorsRes as $_shipVendorsRes) {
	 
	 
if($_shipVendorsRes['udropship_vendor']!=$current_vendor){
	
	if($current_vendor != 0) {
	mailSend($current_vendor,$current_random);
	
	}
	$current_vendor=$_shipVendorsRes['udropship_vendor'];
	
	
	$current_random = generateRandomString();
	$filename = 'payout'.$current_vendor.'_'.$current_random.'.csv'; 

			$path = $filepath.$filename; 
			//echo $path; exit;
		if($fp != '') {
			fclose($fp);
		}
			$fp = fopen($path,'w');
			fputcsv($fp,$header_row);
}



unset($_shipVendorsRes['udropship_vendor']);


 fputcsv($fp,array_values($_shipVendorsRes));
 
 
}

mailSend($current_vendor,$current_random);


 
echo 'updated all vendors successfully......'; exit; 

?>
