<?php
require_once 'app/Mage.php';
Mage::App();

	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	$vendorquery = "SELECT `vendor_id` , `vendor_name` , `custom_vars_combined` from `udropship_vendor`"; 
//	$vendorquery = "SELECT `vendor_id` , `vendor_name` , `custom_vars_combined` from `udropship_vendor` WHERE `vendor_id` = 515 "; 
	$vendordetails = $db->query($vendorquery)->fetchAll();
$k=0;
try{
	//while($result = mysql_fetch_array($vendordetails)){
	 foreach($vendordetails as $result){
	$vendorId = $result["vendor_id"];
	$vendorName = $result["vendor_name"];
	$vname = mysql_real_escape_string($vendorName);
	$array = $result["custom_vars_combined"];
	$data = Zend_Json::decode($array);
	$bankaccno = mysql_real_escape_string($data['bank_ac_number']);
	$bankifsc = mysql_real_escape_string($data['bank_ifsc_code']);
	$pannum = mysql_real_escape_string($data['pan_number']);
	//echo $vendorId; echo $vname; echo $bankaccno.'</br>'; 
	 //exit; 

	$vendorexist = "SELECT * FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorId."'";
	$execvendor = $db->query($vendorexist);
	$vendorResult = $execvendor->fetch();
	$vendorid = $vendorResult['vendor_id'];
	$queryOfCountcatalog = "SELECT count(*) AS countcatalog FROM `catalog_product_craftsvilla3` WHERE `udropship_vendor` = '".$vendorId."'";
	$resultOfCountcatalog = $db->query($queryOfCountcatalog);
	$countCatalogResult =$resultOfCountcatalog->fetch();
	echo $catalogcount = $countCatalogResult['countcatalog']; 
	
	if($vendorid) {
		$vendorupdate = "UPDATE `vendor_info_craftsvilla` SET `bank_account_number`='".$bankaccno."',`bank_ifsc_code`='".$bankifsc."',`pan_number`='".$pannum."', `num_of_products`='".$catalogcount."' WHERE  vendor_id = '".$vendorId."'";	
//echo $vendorupdate; exit;
		$updateResult = $db->query($vendorupdate);
		$row1 = $updateResult->fetch();
		$k++;	
	} else {
		$vendorinsert = "INSERT INTO `vendor_info_craftsvilla`(`vendor_id`, `vendor_name`, `international_order`, `bank_account_number`,`bank_ifsc_code`, `pan_number`,`num_of_products`) VALUES ($vendorId,'$vname',1,'$bankaccno','$bankifsc','$pannum','$catalogcount')";
		//echo $vendorinsert; exit;
		$vendor = $db->query($vendorinsert);
		$row = $vendor->fetch();
		$vendorid1 = $row['vendor_id']; 
		$vendorname1 = $row['vendor_name'];
		$k++;
	}
if($k%100 == 1) echo $k.":";	
	}
} catch(Exception $e) { echo "Exception Occurred";}
?>
