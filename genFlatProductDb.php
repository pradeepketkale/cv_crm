<?php

set_time_limit(0);
ini_set("display_errors", "0");

$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "GenFlatProductDB Solr Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

 
$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
 

if ($conn) {
    echo "<li>" . date('H:i:s');
    
	$dres = $conn->query("truncate table `solr_product_details_flat`; ");

 

    $tagArr = array();
    $sql = "SELECT  tag_id, name FROM tag where status = 1";
   // $tagRes = mysql_query($sql);
    $tagRes = $conn->query($sql)->fetchAll();
   // while ($tagRow = mysql_fetch_row($tagRes)) {
	  foreach($tagRes as $tagRow){
        $tagArr[$tagRow[0]] = $tagRow[1];
    }

    $catArr = array();
    $sql = "SELECT  entity_id, parent_id,name FROM catalog_category_flat_store_1 where parent_id > 1";
    //$resCat = mysql_query($sql);
    $resCat = $conn->query($sql);
    //while ($rowCat = mysql_fetch_row($resCat)) {
    foreach($resCat as $rowCat){
        $catArr[$rowCat[0]]['parent'] = $rowCat[1];
        $catArr[$rowCat[0]]['name'] = $rowCat[2];
    }

    $vendorArr = array();
    $sql = "SELECT vendor_id, vendor_name, city, zip, vendor_attn, custom_vars_combined FROM udropship_vendor";
    //$sql = "SELECT vendor_id, vendor_name, city, zip, company_name, vendor_attn, custom_vars_combined FROM udropship_vendor";
    //$resVendor = mysql_query($sql);
    $resVendor = $conn->query($sql);
    //while ($rowVendor = mysql_fetch_row($resVendor)) {
    foreach($resVendor as $rowVendor){
        //print_r($rowVendor);
        //$vendor_json_str = $rowVendor[0];
        //$vendor_obj = json_decode($vendor_json_str); 
        //print_r($vendor_json_str);
        $vendorArr[$rowVendor[0]]['name'] = $rowVendor[1];
        $vendorArr[$rowVendor[0]]['city'] = $rowVendor[2];
        $vendorArr[$rowVendor[0]]['zip'] = $rowVendor[3];
        $vendorArr[$rowVendor[0]]['owner'] = $rowVendor[4];
        if($rowVendor[5])
        {
        	$vendor_obj = json_decode($rowVendor[5]); 
        	$vendorArr[$rowVendor[0]]['url'] = $vendor_obj->url_key;
        	if($vendor_obj->shop_logo)
        	{
        		$vendorArr[$rowVendor[0]]['logo'] = $vendor_obj->shop_logo;
        	}
        }
    }

    $ctr = 0;
    //$sql = "SELECT entity_id, name, price, short_description, sku, small_image, special_price, thumbnail, url_path, weight, shipment_type, type_id, special_from_date, special_to_date from catalog_product_flat_1 WHERE visibility != 1 order by entity_id desc";
    $sql = "SELECT cpf.entity_id, cpf.name, cpf.price, cpf.short_description, cpf.sku, cpf.small_image, cpf.special_price, cpf.thumbnail, cpf.url_path, cpf.weight, cpf.shipment_type, cpf.type_id, cpf.special_from_date, cpf.special_to_date, csi.qty from `catalog_product_flat_1` as cpf join `cataloginventory_stock_item` as csi on csi.product_id=cpf.entity_id  WHERE cpf.visibility !=1 And csi.is_in_stock=1";
    //$results = mysql_query($sql);
    $results = $conn->query($sql);
    $values = array();
   // while ($row = mysql_fetch_row($results)) {
    foreach($results as $row){    
        //$sql = "SELECT qty FROM cataloginventory_stock_item WHERE product_id='$row[0]'";
        //$resQty = mysql_query($sql);
        if ($row[14]>0 && $row[11] == "simple") {
            //$rowQty = mysql_fetch_row($resQty);
            //$qty = (int) $rowQty[0];
            if ($row[14]>0) {
                $sql = "SELECT cce.value, cp.category_id  FROM catalog_category_product as cp, catalog_category_entity_varchar as cce  where cp.product_id ='$row[0]' and cce.entity_id=cp.category_id AND cce.attribute_id = '31'";
                //$resCatName = mysql_query($sql);
                $resCatName = $conn->query($sql);
                $categoryIds = "";
                $categoryNames = "";
                $aa = 0;
                //while ($rowCatName = mysql_fetch_row($resCatName)) {
    		foreach($resCatName as $rowCatName){ 
                    if (strstr($categoryIds, $rowCatName[1]) === false) {
                        $categoryIds .= $rowCatName[1] . ",";
                        //echo "<br />Bow Bow:".$rowCatName[0];
                        //$categoryNames .= mysql_real_escape_string($rowCatName[0]) . ",";
                        if($aa == 0 && $rowCatName[0])
                        {
                        	$categoryNames = mysql_real_escape_string($rowCatName[0]);
                        	$aa++;
                        }
                    }
                }
                
                $categoryIds = substr($categoryIds, 0, -1);
                //$categoryNames = substr($categoryNames, 0, -1);

                $onSale = "False";
                $discount = 0;
                if ($row[6] != "") {
                    $onSale = "True";
                    $discount = $row[2] - $row[6];
                } else {
                    $onSale = "False";
                }

                $sql = "SELECT value FROM catalog_product_entity_text where attribute_id='57' and entity_id='$row[0]' and store_id='0'";
                //$resDesc = mysql_query($sql);
                $resDesc =$conn->query($sql);
                $rowDesc = $resDesc->fetch();

                $tags = "";
                $sql = "SELECT tag_id FROM tag_relation WHERE product_id='$row[0]'";
                //$tagIdRes = mysql_query($sql);
                $tagIdRes = $conn->query($sql);
               // while ($tagIdRow = mysql_fetch_row($tagIdRes)) {
		foreach($tagIdRes as $tagIdRow){ 
                    //$get_tag_value_Qr = mysql_query("select name from tag where tag_id = '".$tagIdRow[0]."'");
                    $get_tag_value_Qr = $conn->query("select name from tag where tag_id = '".$tagIdRow[0]."'")->fetch();
                    if($get_tag_value_Qr && mysql_num_rows($get_tag_value_Qr)>0)
                    {
                    	$get_tag_value_Row = $get_tag_value_Qr;
                    	if($get_tag_value_Row['name'])
                    	{
                    		$tags .=$get_tag_value_Row['name'] . ", ";	
                    	}
                    }
                }
                $tags = substr($tags, 0, -2);
                $size = "";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='515' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                //$sizeRes = mysql_query($sql);
		$sizeRes = $conn->query($sql);
                if ($sizeRes) {
                    //while ($sizeRow = mysql_fetch_row($sizeRes)) {
		    foreach($sizeRes as $sizeRow){ 
                        $size .=$sizeRow[0] . ", ";
                    }
                    $size = substr($size, 0, -2);
                }

                $payment_method = "";
                $sql = "SELECT eaov.cod FROM  catalog_product_craftsvilla3 as eaov WHERE eaov.entity_id='$row[0]' ";
				
                //$pmRes = mysql_query($sql);
		$pmRes = $conn->query($sql);	
                if ($pmRes) {
                    //while ($pmRow = mysql_fetch_array($pmRes)) {
		     foreach($pmRes as $pmRow){ 
                     $payment_method .=$pmRow[0];
                    }
                   // $payment_method = substr($payment_method, 0, -2);
                }

                $color = "";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='76' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                //$colorRes = mysql_query($sql);
		$colorRes = $conn->query($sql);
                if ($colorRes) {
                   // while ($colorRow = mysql_fetch_row($colorRes)) {
		    foreach($colorRes as $colorRow){ 
                        $color .=$colorRow[0] . ", ";
                    }
                    $color = substr($color, 0, -2);
                }

                $vid = "";
                $sql = "SELECT value FROM catalog_product_entity_int where entity_id ='$row[0]' and entity_type_id='4' and attribute_id='531' ";
                //$res = mysql_query($sql);
		$res = $conn->query($sql);
                //while ($rowa = mysql_fetch_row($res)) {
		foreach($res as $rowa){ 
                    $vid = mysql_real_escape_string($rowa[0]);
                }
                
                $shipping_method = $row[10];
				
				//$values[] = "('" . $row[0] . "', '" . mysql_real_escape_string($row[1]) . "', '" . mysql_real_escape_string($row[3]) . "', '" . mysql_real_escape_string($rowDesc[0]) . "', '" . $row[4] . "', '" . mysql_real_escape_string($categoryNames) . "', '" . $categoryIds . "', '" . mysql_real_escape_string($color) . "', '" . mysql_real_escape_string($size) . "', '" . mysql_real_escape_string($tags) . "', '" . mysql_real_escape_string($vendorArr[$vid]['name']) . "', '$vid', '".$vendorArr[$vid]['city']."', '".$vendorArr[$vid]['zip']."', '$row[14]', '" . $row[2] . "', '$discount', ' $row[5]', '" . mysql_real_escape_string($payment_method) . "', '" . mysql_real_escape_string($shipping_method) . "', '".mysql_real_escape_string($row[8])."')";
				
				/***************** values insert into values array ********************************************************************/
				
                if($row[6] && $row[12] >= date('Y-m-d h:i:s'))
                {
                	if($row[13])
                	{
                		if($row[13] < date('Y-m-d h:i:s'))
                		{
                			$row[2] = $row[6];
                		}
                	}
                	else
                	{
                		$row[2] = $row[6];
                	}
                	
                }
                
                if($row[2] < 1)
                {
                	echo "<br />SIMPLE::Entity:".$row[0]."Price:".$row[2];
                	//exit();
                }	
               if($row[8] == '')
		{
			$row[8] = 'catalog/product/view/id/'.$row[0];
		} 
                $ins = "INSERT INTO `solr_product_details_flat` (`entity_id`, `name`, `short_description`, `description`, `sku`, `category_name`, `category_id`, `color`, `size`, `tags`, `vendor`,`vendor_id`,`vendor_city`,`vendor_zip`, `vendor_owner`, `vendor_logo`, `vendor_url`, `qty`, `price`, `discount`, `Image`, `payment_method`, `shipping_method`, `url_path`) VALUES ('" . $row[0] . "', '" . mysql_real_escape_string($row[1]) . "', '" . mysql_real_escape_string($row[3]) . "', '" . mysql_real_escape_string($rowDesc[0]) . "', '" . $row[4] . "', '" . mysql_real_escape_string($categoryNames) . "', '" . $categoryIds . "', '" . mysql_real_escape_string($color) . "', '" . mysql_real_escape_string($size) . "', '" . mysql_real_escape_string($tags) . "', '" . mysql_real_escape_string($vendorArr[$vid]['name']) . "', '$vid', '".$vendorArr[$vid]['city']."', '".$vendorArr[$vid]['zip']."', '".mysql_real_escape_string($vendorArr[$vid]['owner'])."', '".mysql_real_escape_string($vendorArr[$vid]['logo'])."', '".mysql_real_escape_string($vendorArr[$vid]['url'])."', '$row[14]', '" . $row[2] . "', '$discount', ' $row[5]', '" . mysql_real_escape_string($payment_method) . "', '" . mysql_real_escape_string($shipping_method) . "', '".mysql_real_escape_string($row[8])."');";
                //$dres = mysql_query($ins);
		$dres = $conn->query($ins);
                if (!$dres) {
                    echo "<hr>" . $ins . "<br/>" . mysql_error();
                }
                $ctr++;
            }
        }
        else if($row[11] != "simple"){
        	//$rowQty = mysql_fetch_row($resQty);
            //$qty = (int) $rowQty[0];
            //if ($qty > 0) {
                $sql = "SELECT cce.value, cp.category_id  FROM catalog_category_product as cp, catalog_category_entity_varchar as cce  where cp.product_id ='$row[0]' and cce.entity_id=cp.category_id AND cce.attribute_id = '31'";
               // $resCatName = mysql_query($sql);
		$resCatName = $conn->query($sql);
                $categoryIds = "";
                $categoryNames = "";
                $aa = 0;
                while ($rowCatName = mysql_fetch_row($resCatName)) {
                    if (strstr($categoryIds, $rowCatName[1]) === false) {
                        $categoryIds .= $rowCatName[1] . ",";
                        //echo "<br />Bow Bow:".$rowCatName[0];
                        //$categoryNames .= mysql_real_escape_string($rowCatName[0]) . ",";
                        if($aa == 0 && $rowCatName[0])
                        {
                        	$categoryNames = mysql_real_escape_string($rowCatName[0]);
                        	$aa++;
                        }
                    }
                }
                
                $categoryIds = substr($categoryIds, 0, -1);
                //$categoryNames = substr($categoryNames, 0, -1);

                $onSale = "False";
                $discount = 0;
                if ($row[6] != "") {
                    $onSale = "True";
                    $discount = $row[2] - $row[6];
                } else {
                    $onSale = "False";
                }

                $sql = "SELECT value FROM catalog_product_entity_text where attribute_id='57' and entity_id='$row[0]' and store_id='0'";
                //$resDesc = mysql_query($sql);
		$resDesc = $conn->query($sql);
                $rowDesc = $resDesc->fetch();

                $tags = "";
                $sql = "SELECT tag_id FROM tag_relation WHERE product_id='$row[0]'";
                //$tagIdRes = mysql_query($sql);
		$tagIdRes = $conn->query($sql);
                //while ($tagIdRow = mysql_fetch_row($tagIdRes)) {
		foreach($tagIdRes as $tagIdRow){ 
                    //$get_tag_value_Qr = mysql_query("select name from tag where tag_id = '".$tagIdRow[0]."'");
		    $get_tag_value_Qr = $conn->query("select name from tag where tag_id = '".$tagIdRow[0]."'")->fetch();
                    if($get_tag_value_Qr && mysql_num_rows($get_tag_value_Qr)>0)
                    {
                    	$get_tag_value_Row = $get_tag_value_Qr;
                    	if($get_tag_value_Row['name'])
                    	{
                    		$tags .=$get_tag_value_Row['name'] . ", ";	
                    	}
                    }
                }
                $tags = substr($tags, 0, -2);
                $size = "";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='515' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                //$sizeRes = mysql_query($sql);
	        $sizeRes = $conn->query($sql)->fetch();
                if ($sizeRes) {
                    //while ($sizeRow = mysql_fetch_row($sizeRes)) {
		    foreach($sizeRes as $sizeRow){
                        $size .=$sizeRow[0] . ", ";
                    }
                    $size = substr($size, 0, -2);
                }

                $payment_method = "";
               $sql = "SELECT eaov.cod FROM  catalog_product_craftsvilla3 as eaov WHERE eaov.entity_id='$row[0]' ";
				
                //$pmRes = mysql_query($sql);
		$pmRes =$conn->query($sql);		
                if ($pmRes) {
                    //while ($pmRow = mysql_fetch_array($pmRes)) {
		   foreach($pmRes as $pmRow){
                     $payment_method .=$pmRow[0];
                    }
                   // $payment_method = substr($payment_method, 0, -2);
                }

                $color = "";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='76' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                //$colorRes = mysql_query($sql);
		$colorRes = $conn->query($sql)->fetch();
                if ($colorRes) {
                   // while ($colorRow = mysql_fetch_row($colorRes)) {
		    foreach($colorRes as $colorRow){
                        $color .=$colorRow[0] . ", ";
                    }
                    $color = substr($color, 0, -2);
                }

                $vid = "";
                $sql = "SELECT value FROM catalog_product_entity_int where entity_id ='$row[0]' and entity_type_id='4' and attribute_id='531' ";
                //$res = mysql_query($sql);
		 $res = $conn->query($sql)->fetch();
                //while ($rowa = mysql_fetch_row($res)) {
		foreach($res as $rowa){
                    $vid = mysql_real_escape_string($rowa[0]);
                }
                
                $shipping_method = $row[10];
				
				//$values[] = "('" . $row[0] . "', '" . mysql_real_escape_string($row[1]) . "', '" . mysql_real_escape_string($row[3]) . "', '" . mysql_real_escape_string($rowDesc[0]) . "', '" . $row[4] . "', '" . mysql_real_escape_string($categoryNames) . "', '" . $categoryIds . "', '" . mysql_real_escape_string($color) . "', '" . mysql_real_escape_string($size) . "', '" . mysql_real_escape_string($tags) . "', '" . mysql_real_escape_string($vendorArr[$vid]['name']) . "', '$vid', '".$vendorArr[$vid]['city']."', '".$vendorArr[$vid]['zip']."', '$row[14], '" . $row[2] . "', '$discount', ' $row[5]', '" . mysql_real_escape_string($payment_method) . "', '" . mysql_real_escape_string($shipping_method) . "', '".mysql_real_escape_string($row[8])."')";
				
				/***************** values insert into values array ********************************************************************/
				
				/*if($row[6])
                {
                	$row[2] = $row[6];
                }*/
                
                
                if($row[6] && $row[12] >= date('Y-m-d h:i:s'))
                {
                	if($row[13])
                	{
                		if($row[13] < date('Y-m-d h:i:s'))
                		{
                			$row[2] = $row[6];
                		}
                	}
                	else
                	{
                		$row[2] = $row[6];
                	}
                	
                }
                
                
                if($row[2] < 1)
                {
                	//$query_config = mysql_query("select entity_id, price from catalog_product_flat_1 where sku like '".$row[4]."%'");
			$query_config = $conn->query("select entity_id, price from catalog_product_flat_1 where sku like '".$row[4]."%'")->fetch();
                	$price_val_arr = array();
                	if($query_config && mysql_num_rows($query_config))
                	{
                		//while($row_config = mysql_fetch_array($query_config))
				foreach($query_config as $row_config){
                		{
                			$sql = "SELECT qty FROM cataloginventory_stock_item WHERE product_id='".$row_config['entity_id']."'";
        					//$resQty = mysql_query($sql);
						$resQty = $conn->query($sql);
        					if ($resQty && mysql_num_rows($resQty)>0)
        					{ 
                				if($row_config['price']>0)
                				{
									$price_val_arr[] =  $row_config['price'];                				
                				}
                			}	
                		}	
                		sort($price_val_arr);
                		
                		foreach($price_val_arr as $abcd_val)
                		{
                			if($abcd_val>1)
                			{
                				unset($row[2]);
								$row[2] = $abcd_val;
                				break;
                			}		
                		}
                	}
				}
				
				echo "<br />NOTSIMPLE::Entity:".$row[0]."Price:".$row[2];
				
                $ins = "INSERT INTO `solr_product_details_flat` (`entity_id`, `name`, `short_description`, `description`, `sku`, `category_name`, `category_id`, `color`, `size`, `tags`, `vendor`,`vendor_id`,`vendor_city`,`vendor_zip`, `vendor_owner`, `vendor_logo`, `vendor_url`, `qty`, `price`, `discount`, `Image`, `payment_method`, `shipping_method`, `url_path`) VALUES ('" . $row[0] . "', '" . mysql_real_escape_string($row[1]) . "', '" . mysql_real_escape_string($row[3]) . "', '" . mysql_real_escape_string($rowDesc[0]) . "', '" . $row[4] . "', '" . mysql_real_escape_string($categoryNames) . "', '" . $categoryIds . "', '" . mysql_real_escape_string($color) . "', '" . mysql_real_escape_string($size) . "', '" . mysql_real_escape_string($tags) . "', '" . mysql_real_escape_string($vendorArr[$vid]['name']) . "', '".$vid."', '".$vendorArr[$vid]['city']."', '".$vendorArr[$vid]['zip']."', '".mysql_real_escape_string($vendorArr[$vid]['owner'])."', '".mysql_real_escape_string($vendorArr[$vid]['logo'])."', '".mysql_real_escape_string($vendorArr[$vid]['url'])."', '".$row[14]."', '" . $row[2] . "', '".$discount."', '".$row[5]."', '" . mysql_real_escape_string($payment_method) . "', '" . mysql_real_escape_string($shipping_method) . "', '".mysql_real_escape_string($row[8])."');";
               // $dres = mysql_query($ins);
		$dres = $conn->query($ins);	
                if (!$dres) {
                    echo "<hr>" . $ins . "<br/>" . mysql_error();
                }
                $ctr++;
            //}

        }
    }
    /*$query = "INSERT INTO `aa_product_details_flat` (`entity_id`, `name`, `short_description`, `description`, `sku`, `category_name`, `category_id`, `color`, `size`, `tags`, `vendor`,`vendor_id`,`vendor_city`,`vendor_zip`, `qty`, `price`, `discount`, `Image`, `payment_method`, `shipping_method`, `url_path`) VALUES ".implode(',', $values);
	$result = mysql_query($query);*/
    echo "<li>" . date('H:i:s');
} else {
    echo "Unable to connect to Database. Kindly check database settings and try again";
}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "GenFlatProductDB Solr Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();

?>
