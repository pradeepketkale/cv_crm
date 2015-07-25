<?php

set_time_limit(0);
ini_set("display_errors", "0");
$filename = '/var/www/html/rssxmlfiles/product.xml';
$somecontent = '<!-- RSS for craftsvilla.com -->
<rss version="2.0"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:ecommerce="http://shopping.discovery.com/erss/"
  xmlns:media="http://search.yahoo.com/mrss/">
<channel>

<title>Craftsvilla - Buy Indian Handmade, Handcrafted and Gift Items Online</title>
<link>http://www.craftsvilla.com</link>
<description>Craftsvilla is India\'s largest marketplace for unique handmade, designer and gift items. Choose from over 100,000 products including jewellery, clothing, bags, etc</description>

<webMaster>customercare@craftsvilla.com (craftsvilla.com)</webMaster>
<language>en</language>
<lastBuildDate>'.date("D M j G:i:s").'</lastBuildDate>
';

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

//    $conn = mysql_connect("localhost", "root", "root123");
//    mysql_select_db("magento");

    $conn = mysql_connect("newserver2.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com", "poqfcwgwub", "2gvrwogof9vnw");
    mysql_select_db("nzkrqvrxme");
	
	//$conn = mysql_connect("localhost", "root", "root123");
   // mysql_select_db("craftsvilla_slave");
    
    $ctr = 0;
    $sql = "SELECT entity_id,name,price,short_description,sku,small_image,special_price,thumbnail, url_path,weight,DATE_FORMAT(created_at,'%d %b %Y %T') from catalog_product_flat_1 WHERE created_at > ('2013-01-01 00:00:00') order by entity_id desc";
    $results = mysql_query($sql);
    while ($row = mysql_fetch_row($results)) {
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

                while ($rowCatName = mysql_fetch_row($resCatName)) {
                    if (strstr($categoryIds, $rowCatName[1]) === false) {
                        $categoryIds .= $rowCatName[1] . ",";
                        $categoryNames .= utf8_encode($rowCatName[0]) . ",";
                    }
                    $catName = $rowCatName[0];
                    $catId = $rowCatName[1];
                }
                $categoryIds = substr($categoryIds, 0, -1);
                $categoryNames = substr($categoryNames, 0, -1);

                $onSale = "False";
                $discount = 0;
                if ($row[6] != "") {
                    $onSale = "True";
                    $discount = $row[2] - $row[6];
                } else {
                    $onSale = "False";
                }

                $sql = "SELECT value FROM catalog_product_entity_text where attribute_id='57' and entity_id='$row[0]' and store_id='0'";
                $resDesc = mysql_query($sql);
                $rowDesc = mysql_fetch_row($resDesc);

                $prd = '
            <item>
				<title><![CDATA[' . utf8_encode($row[1]) . ']]></title>
				<link><![CDATA[' . utf8_encode('http://www.craftsvilla.com/' . $row[8]) . ']]></link>
				<productid><![CDATA[' . $row[0] . ']]></productid>
				<originalprice><![CDATA[' . $row[2] . ']]></originalprice>
				<discountedprice><![CDATA[' . $discount . ']]></discountedprice>
				<productdesc><![CDATA[' . utf8_encode($row[3]) . ']]></productdesc>
                <Imagelink><![CDATA[http://d1g63s1o9fthro.cloudfront.net/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95' . $row[5] . ']]></Imagelink>
				<categoryname><![CDATA[' . utf8_encode($catName) . ']]></categoryname>
				<categoryid><![CDATA[' . $catId . ']]></categoryid>
				<subcategoryname><![CDATA[' . utf8_encode($categoryNames) . ']]></subcategoryname>
                <subcategoryid><![CDATA[' . $categoryIds . ']]></subcategoryid>
                <description><![CDATA[' . utf8_encode($rowDesc[0]) . ']]></description>
				<pubDate><![CDATA[' . $row[10] . ']]></pubDate>
            </item>';
                fwrite($handle, $prd);
                $ctr++;
		if (($ctr % 1000) == 0) echo $ctr.'-';
            }
        }
    }
    $string = "</channel></rss>";
//echo $string; exit;
    fwrite($handle, $string);
    echo "Success, wrote $ctr product details to file ($filename)\n";

    fclose($handle);
} else {
    echo "The file $filename is not writable";
}
?>
