<?php

set_time_limit(0);
ini_set("display_errors", "0");
$filename = 'product.xls';
$somecontent = '<table><tr><td>Category</td><td>SKU ID</td><td>Title</td><td>Long Description</td><td>Size</td><td>Weight</td><td>Price</td><td>Image</td></tr>';

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

    $conn = mysql_connect("localhost", "root", "root123");
    mysql_select_db("magento");

//    $conn = mysql_connect("dbhost", "dbusername", "password");
//    mysql_select_db("dbname");

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
    $sql = "SELECT entity_id,name,price,short_description,sku,small_image,special_price,thumbnail, url_path,weight from catalog_product_flat_1 WHERE type_id='simple' order by entity_id desc limit 10";
    $results = mysql_query($sql);
    while ($row = mysql_fetch_row($results)) {
        $sql = "SELECT cce.value, cp.category_id  FROM catalog_category_product as cp, catalog_category_entity_varchar as cce  where cp.product_id ='$row[0]' and cce.entity_id=cp.category_id AND cce.attribute_id = '31'";
        $resCatName = mysql_query($sql);
        $categoryIds = "";
        $categoryNames = "";
        $productGroupId = explode("-", $row[4]);

        while ($rowCatName = mysql_fetch_row($resCatName)) {
            $categoryIds .= $rowCatName[1] . ",";
            $categoryNames .= $rowCatName[0] . ",";
            $catName = $rowCatName[0];
            $catId = $rowCatName[1];
        }
        $categoryIds = substr($categoryIds, 0, -1);
        $categoryNames = substr($categoryNames, 0, -1);

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
        $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaou, catalog_product_entity_int as cpei WHERE cpei.attribute_id='515' and cpei.entity_id='$row[0]' and store_id='1' and cpei.value=eaou.option_id and eaou.store_id='1'";
        $sizeRes = mysql_query($sql);
        if ($sizeRes) {
            while ($sizeRow = mysql_fetch_row($sizeRes)) {
                $size .=$sizeRow[0] . ", ";
            }
            $size = substr($size, 0, -2);
        }
        $color = "";
        $sql = "SELECT eaov.value FROM eav_attribute_option_value as eaou, catalog_product_entity_int as cpei WHERE cpei.attribute_id='531' and cpei.entity_id='$row[0]' and store_id='1' and cpei.value=eaou.option_id and eaou.store_id='1'";
        $colorRes = mysql_query($sql);
        if ($colorRes) {
            while ($colorRow = mysql_fetch_row($colorRes)) {
                $color .=$colorRow[0] . ", ";
            }
            $color = substr($color, 0, -2);
        }
        $prd =  '<tr><td>'.$catName . '</td><td>' . $row[4] . '</td><td>' . $row[1] . '</td><td>' . utf8_encode($rowDesc[0]) . '</td><td>' . $tags . '</td><td>' . $row[9] . '</td><td>' . $row[2] . '</td><td>http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95' . $row[7] .'</td></tr>';
        fwrite($handle, $prd);
        $ctr++;
    }
    $string = "</table>";
    fwrite($handle, $string);
    echo "Success, wrote $ctr product details to file ($filename)\n";

    fclose($handle);
} else {
    echo "The file $filename is not writable";
}
?>
