<?php
include('session.php');
?>
<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();
	if (isset($_POST['submit'])) {
			//echo '<pre>'; print_r($_POST);exit;
		if (empty($_POST['vendor_id'])) {
			$error 		= "Invalid Vendor id";
		}else{
			$readcon 	= Mage::getSingleton('core/resource')->getConnection('custom_db');
			$vendor_id	= trim($_POST['vendor_id']);
			$vend_name	=   "select vendor_name from udropship_vendor where vendor_id = $vendor_id";
			$vend 		= $readcon->query($vend_name)->fetch(); 
			$readcon->closeConnection();
		if(!empty($vend)){
			
			$csv_name	= $vend['vendor_name'].'_closing_stock_brand.csv';

			$readcon 	= Mage::getSingleton('core/resource')->getConnection('custom_db');
		
			//$vendor_id = '8489';
			$orderDetail =   "SELECT `e`.entity_id,`e`.sku,`at_name`.`value` AS productname,`at_price`.`value` AS `price`,`qty`.`qty`,`cat`.`category_id`,`cat`.`sub_category_id`,`cat`.`attributes`,`_table_udropship_vendor`.`value` AS `udropship_vendor` 
			FROM `catalog_product_entity` AS `e`

			LEFT JOIN `catalog_product_entity_int` AS `_table_udropship_vendor` 
			ON (`_table_udropship_vendor`.`entity_id` = `e`.`entity_id`)

			LEFT JOIN `catalog_product_entity_varchar` AS `at_name` 
			ON (`at_name`.`entity_id` = `e`.`entity_id`) 
			AND  (`at_name`.`attribute_id` = 56)

			LEFT JOIN `catalog_product_entity_decimal` AS `at_price` 
			ON (`at_price`.`entity_id` = `e`.`entity_id`)
			AND  (`at_price`.`attribute_id` = 61)

			LEFT JOIN `cataloginventory_stock_item` AS `qty` 
			ON (`qty`.`product_id` = `e`.`entity_id`)
			
			LEFT JOIN `cv_category_product` AS `cat` 
			ON (`cat`.`product_id` = `e`.`entity_id`)
			
			AND (`_table_udropship_vendor`.`store_id` = 0)
			WHERE (`_table_udropship_vendor`.`value` = $vendor_id)";

			$ordersRes = $readcon->query($orderDetail)->fetchAll(); 
			//$readcon->closeConnection();
			//echo '<pre>';print_r($ordersRes);
			/*if(!empty($vendor_id)){
			
				$vend_name =   "select vendor_name from udropship_vendor where vendor_id = $vendor_id";
				$vend = $readcon->query($vend_name)->fetch(); 
				$readcon->closeConnection();
				}*/
				//print_r($vend); exit;
			$csvResultArr = array('Entity_id','Vendor_Sku','Sku','ProductName','Price','Qty','Color','Size','Category', 'Category_id','Sub-Category', 'vendor name');	
			$path ='/tmp/'.$csv_name;
			$fp = fopen($path, 'w');
			fputcsv($fp, $csvResultArr, ',', '"');

		foreach ($ordersRes as $key => $value) {
			//print_r($value);exit;
			$entity_id = $value['entity_id'];
			$sku = $value['sku'];
			$productname = $value['productname'];
			$price = $value['price'];
			$qty = $value['qty'];
			$category = $value['category_id'];
			$subcategory = $value['sub_category_id'];
			$attributes = $value['attributes'];
			if(!empty($attributes)){
				$attributes = json_decode($attributes,true);
				//echo '<pre>'; print_r($attributes);exit;
				$attarra = array();
				$attarrasize = array();
				foreach($attributes as $key => $value){
					
					if (preg_match('/Color/i',$key)) {
					
						//$attarra[] = $value['0'];
						//echo '<pre>'; print_r($attarra);
						 $val = explode(':',$value['0']);
						 $attarra[] = $val['0'];
					}
					if (preg_match('/Size/i',$key)) {
						//echo '<pre>'; print_r($value);
						//$attarrasize[] = $value['0'];
						$val2 = explode(':',$value['0']);
						$attarrasize[] = $val2['0'];
					
					}
				
				}
			
			}
			if($attarra){
			   $attarra = implode(' ',$attarra);
			// echo '<pre>'; print_r($attarra); exit;
			}else{
				$attarra = ' ';
			}
			
			if($attarrasize){
			   $attarrasize = implode(' ',$attarrasize);
			// echo '<pre>'; print_r($attarra); exit;
			}else{
				$attarrasize = ' ';
			}
			$readcon = Mage::getSingleton('core/resource')->getConnection('custom_db');
			$vend_sku =   "select value from catalog_product_entity_varchar where entity_id = $entity_id and attribute_id = 644";
			$vend_sku = $readcon->query($vend_sku)->fetch(); 
			$readcon->closeConnection();
			if(!empty($category)){
			$readcon = Mage::getSingleton('core/resource')->getConnection('custom_db');
			$catname =   "select value from catalog_category_entity_varchar where entity_id = $category and attribute_id = 31";
			$catname = $readcon->query($catname)->fetch(); 
			$readcon->closeConnection();
			}
			if(!empty($subcategory)){
			$readcon = Mage::getSingleton('core/resource')->getConnection('custom_db');
			$subcategory =   "select value from catalog_category_entity_varchar where entity_id = $subcategory and attribute_id = 31";
			$subcategory = $readcon->query($subcategory)->fetch(); 
			$readcon->closeConnection();
			}
			
			$vend_sku = '';
			$csvResultArr=array($entity_id, $vend_sku['value'], $sku, $productname, $price, $qty, $attarra, $attarrasize, $catname['value'], $category, $subcategory['value'], $vend['vendor_name']);
		   	fputcsv($fp,$csvResultArr, ',', '"');
		
		   }
			//echo 'You have successfully generated CSV';	
			//header('Content-type: application/csv');
			//header('Content-Disposition: attachment; filename='.$filename);
			//fputcsv($fp, $csvResultArr);
			//fclose($fp);
			$fileatt_type = "text/csv";
			$myfile = $path; #"myfile.csv";

			$file_size = filesize($myfile);
			$handle = fopen($myfile, "r");
			$content = fread($handle, $file_size);
			fclose($handle);

			$content = chunk_split(base64_encode($content));

	   		$message = "<html>
					<head>
					  <title>List of New Price Changes</title>
					</head>
					<body>
						<table>
							<tr>
								<td>Report of Vendor Products Details of date ".date('d-m-Y')."</td>
							</tr>
						</table>
					</body>
					</html>";

	    $uid = md5(uniqid(time()));
	    $to = 'rajesh.bishnoi@tejora.com';
	    $header = "From: Places <malaniraz@gmail.com>\r\n";
	    //$header .= "CC: rajesh.bishnoi@tejora.com \r\n";
	    #$header .= "Reply-To: ".$replyto."\r\n";
	    $header .= "MIME-Version: 1.0\r\n";
	    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	    $header .= "This is a multi-part message in MIME format.\r\n";
	    $header .= "--".$uid."\r\n";
	    $header .= "Content-type:text/html; charset=iso-8859-1\r\n";
	    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	    $header .= $message."\r\n\r\n";
	    $header .= "--".$uid."\r\n";
	    $header .= "Content-Type: text/csv; name=\"".$csv_name."\"\r\n"; // use diff. tyoes here
	    $header .= "Content-Transfer-Encoding: base64\r\n";
	    $header .= "Content-Disposition: attachment; filename=\"".$csv_name."\"\r\n\r\n";
	    $header .= $content."\r\n\r\n";
	    $header .= "--".$uid."--";

		mail($to, 'Vendor Product Details', $message, $header);

		unlink('/tmp/'.$csv_name);
		$success = "Successfully generated CSV, Please check your mail id inbox or Spam";
		}
		$error = "Invalid Vendor";
	}
}
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link rel="stylesheet" href="css/style.css"/>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-1.11.3.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
<script type="text/javascript"></script>

<style>
	#ui-datepicker-div{width:18% !important;}
	#example_wrapper{overflow-x: scroll;}

	#example_paginate .ui-state-default{width: 50px;}
	.dt-button{padding: :10px;}
</style>
</head>
<body>
	<div class="grid Page-container">
		<div class="col-1-1">

			<div class="grid">
				<div class="col-2-12">
					<div class="container">
						<svg class="logo-con" width="170px" height="28px" viewBox="0 0 203 33" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="cv-logo">
					    <title>cv_logo</title>
					    <desc>Created with Sketch.</desc>
					    <defs>
					        <path id="path-1" d="M0,0.444230769 L202.429428,0.444230769 L202.429428,33 L0,33 L0,0.444230769 Z"></path>
					    </defs>
					    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					        <g id="icons" transform="translate(-434.000000, -60.000000)">
					            <g id="cv_logo" transform="translate(434.000000, 60.000000)">
					                <mask id="mask-2" fill="white">
					                    <use xlink:href="#path-1"></use>
					                </mask>
					                <g id="Clip-2"></g>
					                <path d="M13.7259745,27.4887462 C8.56325844,25.6255154 6.62264527,21.1273615 6.20578519,18.2242077 C5.20248066,11.2239769 9.89320082,6.51851538 14.3391514,6.36028462 C20.2177975,6.15128462 21.6387975,9.95305385 21.6387975,12.3908231 C21.6387975,15.2372846 19.785065,16.9397462 17.7425342,16.9397462 C15.1060403,16.9397462 14.7560115,15.2372846 14.7560115,15.2372846 C16.6740691,15.4048231 18.030744,14.2549 18.0015053,12.4475154 C17.9722667,10.6528231 16.8829169,9.68228462 14.9289374,9.7779 C11.6717481,9.93697692 11.8463449,14.3090538 11.8463449,14.3090538 C11.8463449,16.7713615 13.2581556,20.0011308 18.1435218,20.0011308 C27.8290444,20.0011308 27.7722379,6.72751538 27.7722379,6.72751538 C24.5442872,6.72751538 23.3713984,6.20120769 20.8142667,4.99797692 C18.6163531,3.96313077 16.6214395,3.44951538 14.3391514,3.44951538 C4.8390856,3.44951538 -0.000334156379,10.8245923 -0.000334156379,17.3602846 C-0.000334156379,24.7615923 4.68370288,32.9997462 15.5746947,32.9997462 C20.6964765,32.9997462 23.8584313,31.0874385 25.7756535,29.0499 C25.7915259,29.0329769 25.8073984,29.0169 25.8232708,28.9982846 C25.8909374,28.9263615 25.9535918,28.8544385 26.0170815,28.7816692 C26.6085383,28.1047462 27.4096782,27.0259 27.8415753,25.7634385 C21.5995342,29.8317462 15.0834848,27.9786692 13.7259745,27.4887462 L13.7259745,27.4887462 Z M195.111069,24.7649769 C194.040098,24.5052077 192.714333,24.3317462 191.258246,24.3317462 C188.99016,24.3317462 187.620954,25.3725154 187.620954,27.1062846 C187.620954,28.6234385 188.733695,29.4467462 190.402806,29.4467462 C192.071917,29.4467462 193.697588,28.7969 195.111069,27.7561308 L195.111069,24.7649769 Z M202.429929,28.6234385 L202.429929,32.5250538 L195.196279,32.5250538 L195.196279,30.4443615 C193.697588,31.7449 191.429501,32.8719769 188.904114,32.8719769 C185.694542,32.8719769 182.826645,31.0950538 182.826645,27.5395154 C182.826645,23.9848231 185.908402,21.8169769 190.360201,21.8169769 C192.284942,21.8169769 194.083538,22.0775923 195.111069,22.3373615 L195.111069,20.8193615 C195.111069,19.6931308 194.853769,18.8258231 194.382608,18.2191308 C193.612378,17.2223615 192.371822,16.8322846 190.702711,16.8322846 C189.888205,16.8322846 189.290065,16.9617462 188.861509,17.1352077 C188.947555,17.3949769 189.032765,17.8282077 189.032765,18.0888231 C189.032765,19.5196692 187.920859,20.5595923 186.507378,20.5595923 C185.094732,20.5595923 184.024596,19.5628231 184.024596,18.0016692 C184.024596,15.6612077 186.378728,13.1895923 191.472942,13.1895923 C194.682514,13.1895923 196.86539,14.1009 198.192826,15.7475154 C199.176081,16.9617462 199.647242,18.6092077 199.647242,20.5164385 L199.647242,28.6234385 L202.429929,28.6234385 Z M177.579555,0.7909 L170.00423,0.7909 L170.00423,4.77966923 L172.913896,4.77966923 L172.913896,28.6234385 L170.00423,28.6234385 L170.00423,32.5250538 L180.361407,32.5250538 L180.361407,28.6234385 L177.579555,28.6234385 L177.579555,0.7909 Z M164.94009,0.7909 L157.364765,0.7909 L157.364765,4.77966923 L160.275267,4.77966923 L160.275267,28.6234385 L157.364765,28.6234385 L157.364765,32.5250538 L167.721942,32.5250538 L167.721942,28.6234385 L164.94009,28.6234385 L164.94009,0.7909 Z M149.37425,10.6325154 C151.042526,10.6325154 152.541217,9.24482308 152.541217,7.51105385 C152.541217,5.77728462 151.128571,4.38959231 149.45946,4.38959231 C147.790349,4.38959231 146.292493,5.77728462 146.292493,7.51105385 C146.292493,9.24482308 147.705139,10.6325154 149.37425,10.6325154 L149.37425,10.6325154 Z M152.198707,13.5365154 L144.665987,13.5365154 L144.665987,17.5252846 L147.533049,17.5252846 L147.533049,28.6234385 L144.665987,28.6234385 L144.665987,32.5250538 L154.980559,32.5250538 L154.980559,28.6234385 L152.198707,28.6234385 L152.198707,13.5365154 Z M133.126732,17.3086692 L135.952024,17.3086692 L131.457621,27.1925923 L126.964053,17.3086692 L129.659024,17.3086692 L129.659024,13.5365154 L120.072077,13.5365154 L120.072077,17.5252846 L122.255789,17.5252846 L129.7033,32.8719769 L133.040686,32.8719769 L140.488197,17.5252846 L142.671073,17.5252846 L142.671073,13.5365154 L133.126732,13.5365154 L133.126732,17.3086692 Z M111.09246,21.0368231 C108.224563,20.2566692 107.155263,19.6931308 107.155263,18.6092077 C107.155263,17.6115923 108.181958,16.6579769 110.279625,16.6579769 C111.22111,16.6579769 112.162596,16.8322846 112.933662,17.1352077 C112.847616,17.3949769 112.847616,17.6547462 112.847616,17.9585154 C112.847616,19.3893615 113.875147,20.2998231 115.373003,20.2998231 C116.742209,20.2998231 117.68453,19.2599 117.68453,17.7419 C117.68453,16.8322846 117.34202,16.0081308 116.656999,15.3142846 C115.373003,14.0137462 112.633756,13.1464385 110.36567,13.1464385 C105.101036,13.1464385 102.703464,16.2247462 102.703464,19.1727462 C102.703464,22.4245154 104.887176,23.6810538 110.065765,25.0687462 C112.933662,25.8489 113.746497,26.4124385 113.746497,27.3660538 C113.746497,28.7537462 112.206036,29.4035923 110.32223,29.4035923 C108.995629,29.4035923 107.797678,29.1429769 106.941402,28.7969 C106.984007,28.5371308 107.026612,28.2765154 107.026612,28.0167462 C107.026612,26.3684385 105.785221,25.5028231 104.501226,25.5028231 C103.045974,25.5028231 102.019279,26.6722077 102.019279,28.0599 C102.019279,29.0566692 102.319184,29.8368231 103.088579,30.6178231 C104.330806,31.8743615 107.412563,32.9151308 110.279625,32.9151308 C114.516728,32.9151308 118.19746,30.7912846 118.19746,26.8025154 C118.19746,23.3772846 115.586863,22.2510538 111.09246,21.0368231 L111.09246,21.0368231 Z M93.7589333,27.7561308 C93.5450733,27.3660538 93.459028,26.9759769 93.459028,26.3684385 L93.459028,17.5252846 L99.1522173,17.5252846 L99.1522173,13.5365154 L93.459028,13.5365154 L93.459028,7.51105385 L88.8368099,9.54859231 L88.8368099,13.5365154 L85.1986823,13.5365154 L85.1986823,17.5252846 L88.8368099,17.5252846 L88.8368099,27.2797462 C88.8368099,28.4931308 89.05067,29.4899 89.4783901,30.2709 C90.4207111,32.0918231 92.2610774,32.8719769 94.5291638,32.8719769 C96.2417152,32.8719769 97.7395712,32.3947462 99.0235671,31.7449 L99.4086823,27.6698231 C98.2959416,28.1902077 96.9259004,28.6665923 95.7279498,28.6665923 C94.7856288,28.6665923 94.1006082,28.3636692 93.7589333,27.7561308 L93.7589333,27.7561308 Z M79.4679004,5.34320769 C79.6817605,4.99628462 80.0668757,4.73651538 80.4519909,4.56305385 C80.9657564,5.55982308 81.6933819,6.33997692 83.1486329,6.33997692 C84.4760691,6.33997692 85.6731844,5.34320769 85.6731844,3.65259231 C85.6731844,1.74451538 83.9614683,0.444823077 81.5221267,0.444823077 C79.4252955,0.444823077 77.712744,1.13782308 76.3861432,2.43836154 C74.5883819,4.21613077 73.9894066,6.38397692 73.9894066,9.85151538 L73.9894066,13.5365154 L70.7798346,13.5365154 L70.7798346,17.5252846 L73.9894066,17.5252846 L73.9894066,28.6234385 L71.1641144,28.6234385 L71.1641144,32.5250538 L82.2497523,32.5250538 L82.2497523,28.6234385 L78.6116247,28.6234385 L78.6116247,17.5252846 L83.4911432,17.5252846 L83.4911432,13.5365154 L78.6116247,13.5365154 L78.6116247,9.15766923 C78.6116247,7.33759231 78.91153,6.16651538 79.4679004,5.34320769 L79.4679004,5.34320769 Z M61.4785918,24.7649769 C60.408456,24.5052077 59.0818551,24.3317462 57.6266041,24.3317462 C55.3576823,24.3317462 53.9884765,25.3725154 53.9884765,27.1062846 C53.9884765,28.6234385 55.1012173,29.4467462 56.7703284,29.4467462 C58.4394395,29.4467462 60.0659457,28.7969 61.4785918,27.7561308 L61.4785918,24.7649769 Z M66.0156,28.6234385 L68.7974519,28.6234385 L68.7974519,32.5250538 L61.5638016,32.5250538 L61.5638016,30.4443615 C60.0659457,31.7449 57.7978593,32.8719769 55.2724724,32.8719769 C52.062065,32.8719769 49.1950033,31.0950538 49.1950033,27.5395154 C49.1950033,23.9848231 52.2767605,21.8169769 56.7277235,21.8169769 C58.6532996,21.8169769 60.4518963,22.0775923 61.4785918,22.3373615 L61.4785918,20.8193615 C61.4785918,19.6931308 61.2212914,18.8258231 60.7509663,18.2191308 C59.9807358,17.2223615 58.7393449,16.8322846 57.0702337,16.8322846 C56.256563,16.8322846 55.6575877,16.9617462 55.2298675,17.1352077 C55.3150774,17.3949769 55.4011226,17.8282077 55.4011226,18.0888231 C55.4011226,19.5196692 54.2883819,20.5595923 52.8749004,20.5595923 C51.4630897,20.5595923 50.3929539,19.5628231 50.3929539,18.0016692 C50.3929539,15.6612077 52.7470856,13.1895923 57.8404642,13.1895923 C61.0500362,13.1895923 63.2337481,14.1009 64.560349,15.7475154 C65.5444395,16.9617462 66.0156,18.6092077 66.0156,20.5164385 L66.0156,28.6234385 Z M47.6186206,16.3110538 C47.6186206,17.9153615 46.378065,19.2599 44.8367687,19.2599 C43.424958,19.2599 42.482637,18.4357462 42.0975218,17.5684385 C40.770921,18.0448231 39.2296247,19.5196692 38.3741844,21.1662846 L38.3741844,28.6234385 L41.6271967,28.6234385 L41.6271967,32.5250538 L30.9266741,32.5250538 L30.9266741,28.6234385 L33.7085259,28.6234385 L33.7085259,17.5252846 L30.9266741,17.5252846 L30.9266741,13.5365154 L38.2029292,13.5365154 L38.2029292,17.8282077 C39.7859951,15.2702846 41.7550115,13.1895923 44.1525835,13.1895923 C46.4641103,13.1895923 47.6186206,14.6204385 47.6186206,16.3110538 L47.6186206,16.3110538 Z" id="Fill-1" fill="#981937" mask="url(#mask-2)"></path>
					            </g>
					        </g>
					    </g>
					</svg>
					</div>
				</div>

				<div class="col-10-12">
					<div class="container">
						<div class="page-breadcrumb">

							<div class="page-heading">
								<h1>Closing Stock Report For Brand</h1>
								<!--<div class="clear" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>||
		                       		 <a href="logout.php" ><b>Logout</b> </a>
		                       	</div> -->
		                       	<div class="FRnavigation" style="align="right";">
                       		 <a href="logout.php" ><b>Logout</b> </a>
                       	 </div>
							</div>

							<div class="clear"></div>
						</div>


					</div>
				</div>

			</div>
		</div>
	</div>


	<div class="grid grid-pad">
		<div class="col-1-1">
			<div class="container-wrapper2">
				<div style="width:100%; height:auto;border:1px solid #e1e1e1;border-radius:5px;margin: 14px 0px 0px;">
				<h2>Vendor Product Report</h2>
					<form action='' method='POST' name="myForm">
						<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">

							<tr>
								<td colspan="2" align="center"><?php echo $error; ?>
						<?php echo $success; ?></td>

							</tr>
							
							<tr>
								<td><b>Enter Vendor ID: </b></td>
								<td><input type="text"  tabindex="1" size="9" value="" class="field text datpik" name="vendor_id" id="vendorid"></td>
							</tr>

							<tr>
								
								<td colspan="2"><button id="search" class="btn btn-submit" name='submit' type='submit' tabindex="2" style='margin-left:36%;' onclick="loader()" > Generate CSV </button></td>
							</tr>
							
							
						
						</table>
					</form>
				</div>

			</div>

		</div>
	</div>

			<div class="dataloaddiv">

				<h5>Please wait while your CSV gets Prepared. <br> Please Reload the page After file gets Download. </h5>
				<div class="loader"></div>
			</div>


</body>
</html>

<script language="javascript" type="text/javascript">
function loader() {
    $(".dataloaddiv").show();
}
</script>
