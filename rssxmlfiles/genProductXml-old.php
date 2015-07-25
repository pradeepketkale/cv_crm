<?php

set_time_limit(0);
ini_set("display_errors", "0");
$filename = 'product.xml';
$somecontent = '<?xml version="1.0" encoding="UTF-8"?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">						
    <CV_Product_Catalog>';

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

    $tagArr = array();
    $sql = "SELECT  tag_id, name FROM tag where status = 1";
    $tagRes = mysql_query($sql);
    while ($tagRow = mysql_fetch_row($tagRes)) {
        $tagArr[$tagRow[0]] = $tagRow[1];
    }

    $catArr = array();
    $sql = "SELECT  entity_id, parent_id,name FROM catalog_category_flat_store_1 where parent_id > 1";
    $resCat = mysql_query($sql);
    while ($rowCat = mysql_fetch_row($resCat)) {
        $catArr[$rowCat[0]]['parent'] = $rowCat[1];
        $catArr[$rowCat[0]]['name'] = $rowCat[2];
    }
    $ctr = 0;
    $sql = "SELECT entity_id,name,price,short_description,sku,small_image,special_price,thumbnail, url_path,weight from catalog_product_flat_1 WHERE type_id='simple' order by entity_id desc";
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

                $tags = "";
                $sql = "SELECT tag_id FROM tag_relation WHERE product_id='$row[0]'";
                $tagIdRes = mysql_query($sql);
                while ($tagIdRow = mysql_fetch_row($tagIdRes)) {
                    $tags .=$tagRow[$tagIdRow[0]] . ", ";
                }
                $tags = substr($tags, 0, -2);

                $size = "";
                //$sql = "SELECT eaov.value FROM eav_attribute_option_value as eaou, catalog_product_entity_int as cpei WHERE cpei.attribute_id='515' and cpei.entity_id='$row[0]' and store_id='1' and cpei.value=eaou.option_id and eaou.store_id='1'";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='515' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                $sizeRes = mysql_query($sql);
                if ($sizeRes) {
                    while ($sizeRow = mysql_fetch_row($sizeRes)) {
                        $size .=$sizeRow[0] . ", ";
                    }
                    $size = substr($size, 0, -2);
                }
                $color = "";
                //$sql = "SELECT eaov.value FROM eav_attribute_option_value as eaou, catalog_product_entity_int as cpei WHERE cpei.attribute_id='76' and cpei.entity_id='$row[0]' and cpei.value=eaou.option_id and eaou.store_id='1'";
                $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaov, catalog_product_entity_int as cpei WHERE cpei.attribute_id='76' and cpei.entity_id='$row[0]' and cpei.value=eaov.option_id and eaov.store_id='1'";
                $colorRes = mysql_query($sql);
                if ($colorRes) {
                    while ($colorRow = mysql_fetch_row($colorRes)) {
                        $color .=$colorRow[0] . ", ";
                    }
                    $color = substr($color, 0, -2);
                }
                $prd = '
            <Product>
                <productid><![CDATA[' . $row[0] . ']]></productid>
                <imageurl1><![CDATA[http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95' . $row[5] . ']]></imageurl1>
                <imageurl2><![CDATA[http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95' . $row[7] . ']]></imageurl2>
                <PageURL><![CDATA[' . utf8_encode('http://www.craftsvilla.com/' . $row[8]) . ']]></PageURL>
                <ProductName><![CDATA[' . utf8_encode($row[1]) . ']]></ProductName>
                <ProductDesc><![CDATA[' . utf8_encode($row[3]) . ']]></ProductDesc>
                <ProductFullDesc><![CDATA[' . utf8_encode($rowDesc[0]) . ']]></ProductFullDesc>
                <SubCategoryName><![CDATA[' . utf8_encode($categoryNames) . ']]></SubCategoryName>
                <SubCategoryId><![CDATA[' . $categoryIds . ']]></SubCategoryId>
                <CategoryName><![CDATA[' . utf8_encode($catName) . ']]></CategoryName>
                <CategoryID><![CDATA[' . $catId . ']]></CategoryID>
                <Keywords><![CDATA[' . utf8_encode($tags) . ']]></Keywords>
                <Size><![CDATA[' . $size . ']]></Size>
                <Unit><![CDATA[' . $row[9] . ']]></Unit>
                <Color><![CDATA[' . utf8_encode($color) . ']]></Color>
                <MRP><![CDATA[' . $row[2] . ']]></MRP>
                <Discount><![CDATA[' . $discount . ']]></Discount>
                <OnSale><![CDATA[' . $onSale . ']]></OnSale>
                <CurrentStock><![CDATA[' . $qty . ']]></CurrentStock>
                <Gender><![CDATA[BOTH]]></Gender>
                <AgeFrom><![CDATA[]]></AgeFrom>
                <AgeTo><![CDATA[]]></AgeTo>
                <IsActive><![CDATA[1]]></IsActive>
                <Product_Group_ID><![CDATA[' . $productGroupId[0] . ']]></Product_Group_ID>
                <Product_Sequence><![CDATA[' . $row[4] . ']]></Product_Sequence>is
            </Product>';
                fwrite($handle, $prd);
                $ctr++;
            }
        }
    }
    $string = "
        </CV_Product_Catalog>
</rss>";
    fwrite($handle, $string);
    echo "Success, wrote $ctr product details to file ($filename)\n";

    fclose($handle);
} else {
    echo "The file $filename is not writable";
}
?>
