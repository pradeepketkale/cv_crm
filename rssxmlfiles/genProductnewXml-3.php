<?php

set_time_limit(0);
ini_set("display_errors", "0");
$filename = '/var/www/html/rssxmlfiles/cv-product-3.xml';
$somecontent = '<?xml version="1.0" encoding="UTF-8"?>';
date_default_timezone_set('Asia/Kolkata');

if (is_writable($filename)) {

    if (!$handle = fopen($filename, 'w')) {
        echo "Cannot open file ($filename)";
        exit;
    }

    // Write $somecontent to our opened file.
    if (fwrite($handle, $somecontent) === FALSE) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    $string = "<catalog>";
//echo $string; exit;
    fwrite($handle, $string);

//    $conn = mysql_connect("localhost", "root", "root123");
//    mysql_select_db("magento");

    $conn = mysql_connect("newserver2.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com", "poqfcwgwub", "2gvrwogof9vnw");
    mysql_select_db("nzkrqvrxme");
	
//	$conn = mysql_connect("localhost", "doejofinal1", "123456");
  // 	 mysql_select_db("doejofinal");
    
    $ctr = 0;
    $ind = 1;
    $sql = "SELECT entity_id,name,price,short_description,sku,small_image,special_price,thumbnail, url_path,weight,DATE_FORMAT(created_at,'%d %b %Y %T'),udropship_vendor, shippingcost from catalog_product_flat_1 order by entity_id desc";
    $results = mysql_query($sql);
echo "Total Rows:";
echo mysql_num_rows($results); 
    while (($row = mysql_fetch_row($results))) {
	if (($ind >= 200000) && ($ind < 300000)) {
		if($row[0] < 100000) continue;
    //while (($row = mysql_fetch_row($results))) {
//	if($row[0] == '315513'){
        $sql = "SELECT qty FROM cataloginventory_stock_item WHERE product_id='$row[0]'";
        $resQty = mysql_query($sql);
        if ($resQty) {
            $rowQty = mysql_fetch_row($resQty);
            $qty = (int) $rowQty[0];
            if ($qty > 0) {

                $sql = "SELECT cce.value, cp.category_id  FROM catalog_category_product as cp, catalog_category_entity_varchar as cce  where cp.product_id ='$row[0]' and cce.entity_id=cp.category_id AND cce.attribute_id = '31'";
                $resCatName = mysql_query($sql);
                $categoryIds = "";
                $categoryNames = "";
                $catName = "";
                $catId = "";
                $productGroupId = explode("-", $row[4]);

		$nn = 0;
                while ($rowCatName = mysql_fetch_row($resCatName)) {
                    if (strstr($categoryIds, $rowCatName[1]) === false) {
                        $categoryIds .= $rowCatName[1] . ",";
                        $categoryNames .= xml_encode($rowCatName[0]) . ",";
                    }
                   $catName = $rowCatName[0];
                    $catId = $rowCatName[1];
			
		if ($nn == 1)	break;
			$nn++;
                }
                $categoryIds = substr($categoryIds, 0, -1);
                //$categoryNames = substr($categoryNames, 0, -1);
                //echo $categoryNames = $rowCatName[0];exit;
                $categoryNames = $catName;
		if((strlen($categoryNames) > 150) || ($categoryNames[0]== '@') || empty($categoryNames)) continue;

$sqlspecialto = "SELECT value FROM catalog_product_entity_datetime where attribute_id='63' and entity_id='".$row[0]."'";
                $resSpecialTo = mysql_query($sqlspecialto);
                $rowSpecialTo = mysql_fetch_row($resSpecialTo);
                //echo "Entity Id:"; echo $row[0];
                //echo "Special To Date:"; echo $rowSpecialTo[0];
                if(!empty($rowSpecialTo[0]))
                $strtimeSpecialTo = strtotime($rowSpecialTo[0]);
                else
                $strtimeSpecialTo = 2405742400;
                //echo "Today:"; echo $strtotimeToday = strtotime(date("Y/m/d"));
                $strtotimeToday = strtotime('today');

                $onSale = "False";
                $discount = 0;
		if (($row[6] != "") && ($strtimeSpecialTo > $strtotimeToday) ) {
                    $onSale = "True";
                    $discount = $row[2] - $row[6];
			$discountedprice = $row[6];
                } else {
                    $onSale = "False";
			$discountedprice = $row[2];
                }
		$discount_amount = 0.2*$discountedprice;
		$_producturl = $row[8];
		if($row[8]==''){$_producturl='catalog/product/view/id/'. $row[0];}

                $sql = "SELECT value FROM catalog_product_entity_text where attribute_id='57' and entity_id='$row[0]' and store_id='0'";
                $resDesc = mysql_query($sql);
                $rowDesc = mysql_fetch_row($resDesc);
		$_productName = strtr($row[1], array("\x0B" => "&#x0B;"));
		$_productName = str_replace("Online Shopping","",$_productName);
		$_productName = str_replace("Online","",$_productName);
                $_productName = str_replace("Shopping","",$_productName);
		$_productDesc = strtr($rowDesc[0], array("\x0B" => "&#x0B;"));
		$_productName = strip_tags(html_entity_decode($_productName));
                $_productDesc = strip_tags(html_entity_decode($_productDesc));
		$salestartdate = "15-05-2014";
		$saleenddate = "07-06-2014";
		$jungleeexclusiveoffer = "Flat 20% Off on All Craftsvilla.com Products. Use Coupon Code JCRAFTS20 At Checkout on Craftsvilla.com. Hurry Limited Time Offer Only.";
		$colorchoices = array('red', 'green','white','black','yellow','magenta','purple','grey','blue','brown','silver','beige','gold','multicolour');
		$color = findstringmatch(strtolower($_productName),$colorchoices);
		if (empty($color)) $color = findstringmatch(strtolower($_productDesc),$colorchoices);

		$materialchoices =array('chiffon','georgette','silk','dupion','cotton','net','satin','jacquard','cashmere','wool','velvet');
		$material = findstringmatch(strtolower($_productName),$materialchoices);
                if (empty($material)) $material = findstringmatch(strtolower($_productDesc),$materialchoices);
                if (empty($material)) $material = findstringmatch(strtolower($catName),$materialchoices);
		$vendorNameQuery = "select `vendor_name` from `udropship_vendor` where `vendor_id` = $row[11]"; 
		$vendorNameRes = mysql_query( $vendorNameQuery);
		$vendorName = mysql_fetch_row($vendorNameRes);
		$brand = $vendorName[0]; 
 		$style = "Fashion";
                if($brand == "Surat Diamond") $style = "Fine";
                $metaltype = "Base Metal";
		$shipping_cost = $row[12];

                $prd = '
            <item id="'.$row[0].'">
           	<title><![CDATA[' . xml_encode($_productName) . ']]></title>
		<link><![CDATA[' . xml_encode('http://www.craftsvilla.com/' . $_producturl) . ']]></link>
		<productid><![CDATA[' . $row[0] . ']]></productid>
		<sku><![CDATA[' . xml_encode($row[4]) . ']]></sku>
		<originalprice><![CDATA[' . $row[2] . ']]></originalprice>
		<discountedprice><![CDATA[' . $discountedprice . ']]></discountedprice>
		<productname><![CDATA[' . xml_encode($_productName) . ']]></productname>
		<imagelink><![CDATA[http://assets1.craftsvilla.com/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95' . xml_encode($row[5]) . ']]></imagelink>
		<categoryname><![CDATA[' . xml_encode($catName) . ']]></categoryname>
		<categoryid><![CDATA[' . $catId . ']]></categoryid>
		<subcategoryname><![CDATA[' . xml_encode($categoryNames) . ']]></subcategoryname>
		<subcategoryid><![CDATA[' . $categoryIds . ']]></subcategoryid>
		<description><![CDATA[' . xml_encode($_productDesc) . ']]></description> 
		<PromoCode>JCRAFTS20</PromoCode>
                <salestartdate><![CDATA[' . xml_encode($salestartdate) . ']]></salestartdate>
                <saleenddate><![CDATA[' . xml_encode($saleenddate) . ']]></saleenddate>
                <jungleeexclusiveoffer>Yes</jungleeexclusiveoffer>
                <OfferNote><![CDATA[' . xml_encode($jungleeexclusiveoffer) . ']]></OfferNote>
		<size><![CDATA[' . xml_encode("Free") . ']]></size> 
		<color><![CDATA[' . xml_encode($color) . ']]></color> 
		<material><![CDATA[' . xml_encode($material) . ']]></material> 
		<brand><![CDATA[' . xml_encode($brand) . ']]></brand> 
		<style><![CDATA[' . xml_encode($style) . ']]></style>
                <metaltype><![CDATA[' . xml_encode($metaltype) . ']]></metaltype>
		<discount_amount><![CDATA[' . xml_encode($discount_amount) . ']]></discount_amount>
		<shipping_cost><![CDATA[' . xml_encode($shipping_cost) . ']]></shipping_cost>
	   </item>';
                fwrite($handle, $prd);
                $ctr++;
		if (($ctr%1000) == 0) echo $ctr.'-';
            }
        }
	}
	$ind++;
    }//end of foreach
    $string = "</catalog>";
//echo $string; exit;
    fwrite($handle, $string);
    echo "Success, wrote $ctr product details to file ($filename)\n";

    fclose($handle);
} else {
    echo "The file $filename is not writable";
}
function xml_encode($string)
{
    $string1 = utf8_for_xml($string);
    $string1=preg_replace("/&/", "&amp;", $string1);
    $string1=preg_replace("/</", "&lt;", $string1);
    $string1=preg_replace("/>/", "&gt;", $string1);
    $string1=preg_replace("/\"/", "&quot;", $string1);
    $string1=preg_replace("/%/", "&#37;", $string1);
    $string1=preg_replace("/]/", "", $string1);
    $string1=preg_replace("/!/", ".", $string1);

    return utf8_encode($string1);
}
function utf8_for_xml($string)
{
    return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}

function findstringmatch($haystack, $needles)
{
   foreach($needles as $needle) {
                $res = strpos($haystack, strtolower($needle));
                if ($res !== false) return $needle;
        }
	return "";
}
?>
