<?php

define('ADMIN_USERNAME','finance');    // Admin Username
define('ADMIN_PASSWORD','financemerchant');   // Admin Password


////////// END OF DEFAULT CONFIG AREA /////////////////////////////////////////////////////////////

///////////////// Password protect ////////////////////////////////////////////////////////////////
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
           $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME ||$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
                        Header("WWW-Authenticate: Basic realm=\"Vendor_Id(Without merchantId) Login\"");
                        Header("HTTP/1.0 401 Unauthorized");

                        echo <<<EOB
                                <html><body>
                                <h1>Rejected!</h1>
                                <big>Wrong Username or Password!</big>
                                </body></html>
EOB;
                        exit;
}

?>
<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql = "SELECT DISTINCT `vendor_id` FROM `vendor_info_craftsvilla` AS uv,`sales_flat_shipment` AS sfs,`shipmentpayout` AS sp WHERE sfs.`udropship_vendor` = uv.`vendor_id` AND sfs.`increment_id` = sp.`shipment_id` AND uv.`merchant_id_city` = '' AND sp.`shipmentpayout_status` = '0' ";
$result = $db->query($sql)->fetchAll();
$resultQuery .="VendorId";
$resultQuery .="\n";
//while($sql_row=mysql_fetch_array($result))
 foreach($result as $sql_row)
		{
				$resultQuery .= ''.$sql_row['vendor_id'].',';
				$resultQuery .="\n";
			
			}
		
		
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=vendormerchantdetails.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $resultQuery;
	?>
