<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$rcvdquery1day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` > DATE_SUB(NOW(), INTERVAL 1 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvd1day = $db->query($rcvdquery1day)->fetch();
echo $countrecvd1day = mysql_num_rows($resrecvd1day);

$rcvdquery2day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvd2day = $db->query($rcvdquery2day)->fetch();
echo $countrecvd2day = mysql_num_rows($resrecvd2day);

$rcvdquery3day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 3 DAY) AND DATE_SUB(NOW(), INTERVAL 2 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvd3day = $db->query($rcvdquery3day)->fetch();
echo $countrecvd3day = mysql_num_rows($resrecvd3day);

$rcvdquery4day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 4 DAY) AND DATE_SUB(NOW(), INTERVAL 3 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvd4day = $db->query($rcvdquery4day)->fetch();
echo $countrecvd4day = mysql_num_rows($resrecvd4day);

$rcvdquery5day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 5 DAY) AND DATE_SUB(NOW(), INTERVAL 4 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvd5day = $db->query($rcvdquery5day)->fetch();
echo $countrecvd5day = mysql_num_rows($resrecvd5day);

$rcvdqueryL7day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND DATE_SUB(NOW(), INTERVAL 0 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvdL7day = $db->query($rcvdqueryL7day)->fetch();
echo $countrecvdL7day = mysql_num_rows($resrecvdL7day);

$rcvdqueryL14day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 14 DAY) AND DATE_SUB(NOW(), INTERVAL 7 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvdL14day = $db->query($rcvdqueryL14day)->fetch();
echo $countrecvdL14day = mysql_num_rows($resrecvdL14day);

$rcvdqueryL30day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND DATE_SUB(NOW(), INTERVAL 0 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvdL30day = $db->query($rcvdqueryL30day)->fetch();
echo $countrecvdL30day = mysql_num_rows($resrecvdL30day);

$rcvdqueryL60day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY) AND `sales_flat_shipment`.`udropship_status` = 17";
$resrecvdL60day = $db->query($rcvdqueryL60day)->fetch();
echo $countrecvdL60day = mysql_num_rows($resrecvdL60day);



//Shipped to Craftsvilla Updated Count Day Wise

$rcvdquery1day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` > DATE_SUB(NOW(), INTERVAL 1 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvd1day = $db->query($rcvdquery1day)->fetch();
echo $countshcvd1day = mysql_num_rows($resrecvd1day);

$rcvdquery2day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvd2day = $db->query($rcvdquery2day)->fetch();
echo $countshcvd2day = mysql_num_rows($resrecvd2day);

$rcvdquery3day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 3 DAY) AND DATE_SUB(NOW(), INTERVAL 2 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvd3day = $db->query($rcvdquery3day)->fetch();
echo $countshcvd3day = mysql_num_rows($resrecvd3day);

$rcvdquery4day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 4 DAY) AND DATE_SUB(NOW(), INTERVAL 3 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvd4day = $db->query($rcvdquery4day)->fetch();
echo $countshcvd4day = mysql_num_rows($resrecvd4day);

$rcvdquery5day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 5 DAY) AND DATE_SUB(NOW(), INTERVAL 4 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvd5day = $db->query($rcvdquery5day)->fetch();
echo $countshcvd5day = mysql_num_rows($resrecvd5day);

$rcvdqueryL7day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND DATE_SUB(NOW(), INTERVAL 0 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvdL7day = $db->query($rcvdqueryL7day)->fetch();
echo $countshcvdL7day = mysql_num_rows($resrecvdL7day);

$rcvdqueryL14day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 14 DAY) AND DATE_SUB(NOW(), INTERVAL 7 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvdL14day = $db->query($rcvdqueryL14day)->fetch();
echo $countshcvdL14day = mysql_num_rows($resrecvdL14day);

$rcvdqueryL30day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND DATE_SUB(NOW(), INTERVAL 0 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvdL30day = $db->query($rcvdqueryL30day)->fetch();
echo $countshcvdL30day = mysql_num_rows($resrecvdL30day);

$rcvdqueryL60day = "SELECT `increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.`updated_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY) AND `sales_flat_shipment`.`udropship_status` = 15";
$resrecvdL60day = $db->query($rcvdqueryL60day)->fetch();
echo $countshcvdL60day = mysql_num_rows($resrecvdL60day);



// Shipped to Customer Day Wise
$shcuquery1day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at > DATE_SUB(now(), interval 1 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcu1day = $db->query($shcuquery1day)->fetch();
echo $countshcu1day = mysql_num_rows($resshcu1day);

$shcuquery2day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcu2day = $db->query($shcuquery2day)->fetch();
echo $countshcu2day = mysql_num_rows($resshcu2day);

$shcuquery3day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcu3day = $db->query($shcuquery3day)->fetch();
echo $countshcu3day = mysql_num_rows($resshcu3day);

$shcuquery4day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcu4day = $db->query($shcuquery4day)->fetch();
echo $countshcu4day = mysql_num_rows($resshcu4day);

$shcuquery5day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcu5day = $db->query($shcuquery5day)->fetch();
echo $countshcu5day = mysql_num_rows($resshcu5day);


$shcuqueryL7day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcuL7day = $db->query($shcuqueryL7day)->fetch();
echo $countshcuL7day = mysql_num_rows($resshcuL7day);

$shcuqueryL14day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcuL14day = $db->query($shcuqueryL14day)->fetch();
echo $countshcuL14day = mysql_num_rows($resshcuL14day);

$shcuqueryL30day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcuL30day = $db->query($shcuqueryL30day)->fetch();
echo $countshcuL30day = mysql_num_rows($resshcuL30day);

$shcuqueryL60day = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.`udropship_status` = 1";
$resshcuL60day = $db->query($shcuqueryL60day)->fetch();
echo $countshcuL60day = mysql_num_rows($resshcuL60day);

//International Shipments created
$shcuquery1dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcu1dayc = $db->query($shcuquery1dayc)->fetch();
echo $countshcu1dayc = mysql_num_rows($resshcu1dayc);

$shcuquery2dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcu2dayc = $db->query($shcuquery2dayc)->fetch();
echo $countshcu2dayc = mysql_num_rows($resshcu2dayc);

$shcuquery3dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcu3dayc = $db->query($shcuquery3dayc)->fetch();
echo $countshcu3dayc = mysql_num_rows($resshcu3dayc);

$shcuquery4dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcu4dayc = $db->query($shcuquery4dayc)->fetch();
echo $countshcu4dayc = mysql_num_rows($resshcu4dayc);

$shcuquery5dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcu5dayc = $db->query($shcuquery5dayc)->fetch();
echo $countshcu5dayc = mysql_num_rows($resshcu5dayc);


$shcuqueryL7dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcuL7dayc = $db->query($shcuqueryL7dayc)->fetch();
echo $countshcuL7dayc = mysql_num_rows($resshcuL7dayc);

$shcuqueryL14dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcuL14dayc = $db->query($shcuqueryL14dayc->fetch());
echo $countshcuL14dayc = mysql_num_rows($resshcuL14dayc);

$shcuqueryL30dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcuL30dayc = $db->query($shcuqueryL30dayc)->fetch();
echo $countshcuL30dayc = mysql_num_rows($resshcuL30dayc);

$shcuqueryL60dayc = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' ";
$resshcuL60dayc = $db->query($shcuqueryL60dayc)->fetch();
echo $countshcuL60dayc = mysql_num_rows($resshcuL60dayc);

//Total Shipments Created
$shcuquery1dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) ";
$resshcu1dayct = $db->query($shcuquery1dayct)->fetch();
echo $countshcu1dayct = mysql_num_rows($resshcu1dayct);

$shcuquery2dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) ";
$resshcu2dayct = $db->query($shcuquery2dayct)->fetch();
echo $countshcu2dayct = mysql_num_rows($resshcu2dayct);

$shcuquery3dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) ";
$resshcu3dayct = $db->query($shcuquery3dayct)->fetch();
echo $countshcu3dayct = mysql_num_rows($resshcu3dayct);

$shcuquery4dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) ";
$resshcu4dayct = $db->query($shcuquery4dayct)->fetch();
echo $countshcu4dayct = mysql_num_rows($resshcu4dayct);

$shcuquery5dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) ";
$resshcu5dayct = $db->query($shcuquery5dayct)->fetch();
echo $countshcu5dayct = mysql_num_rows($resshcu5dayct);

$shcuqueryL7dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) ";
$resshcuL7dayct = $db->query($shcuqueryL7dayct)->fetch();
echo $countshcuL7dayct = mysql_num_rows($resshcuL7dayct);

$shcuqueryL14dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) ";
$resshcuL14dayct = $db->query($shcuqueryL14dayct)->fetch();
echo $countshcuL14dayct = mysql_num_rows($resshcuL14dayct);

$shcuqueryL30dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) ";
$resshcuL30dayct = $db->query($shcuqueryL30dayct)->fetch();
echo $countshcuL30dayct = mysql_num_rows($resshcuL30dayct);

$shcuqueryL60dayct = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) ";
$resshcuL60dayct = $db->query($shcuqueryL60dayct)->fetch();
echo $countshcuL60dayct = mysql_num_rows($resshcuL60dayct);

//Total COD Shipments Created
$numcodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod1d = $db->query($numcodquery1d)->fetch();
$num_cod1d = mysql_num_rows($resultnumcod1d);

$numcodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod2d = $db->query($numcodquery2d)->fetch();
$num_cod2d = mysql_num_rows($resultnumcod2d);

$numcodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod3d = $db->query($numcodquery3d)->fetch();
$num_cod3d = mysql_num_rows($resultnumcod3d);

$numcodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod4d = $db->query($numcodquery4d)->fetch();
$num_cod4d = mysql_num_rows($resultnumcod4d);

$numcodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod5d = $db->query($numcodquery5d)->fetch();
$num_cod5d = mysql_num_rows($resultnumcod5d);

$numcodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod7d = $db->query($numcodquery7d)->fetch();
$num_cod7d = mysql_num_rows($resultnumcod7d);

$numcodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod14d = $db->query($numcodquery14d)->fetch();
$num_cod14d = mysql_num_rows($resultnumcod14d);

$numcodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod30 = $db->query($numcodquery30)->fetch();
$num_cod30 = mysql_num_rows($resultnumcod30);

$numcodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod60 = $db->query($numcodquery60)->fetch();
$num_cod60 = mysql_num_rows($resultnumcod60);

$numcodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcod365 = $db->query($numcodquery365)->fetch();
$num_cod365 = mysql_num_rows($resultnumcod365);

//total COD Shipments processing
$numprocodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod1d = $db->query($numprocodquery1d)->fetch();
$numpro_cod1d = mysql_num_rows($resultnumprocod1d);

$numprocodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod2d = $db->query($numprocodquery2d)->fetch();
$numpro_cod2d = mysql_num_rows($resultnumprocod2d);

$numprocodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod3d = $db->query($numprocodquery3d)->fetch();
$numpro_cod3d = mysql_num_rows($resultnumprocod3d);

$numprocodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod4d = $db->query($numprocodquery4d)->fetch();
$numpro_cod4d = mysql_num_rows($resultnumprocod4d);

$numprocodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod5d = $db->query($numprocodquery5d)->fetch();
$numpro_cod5d = mysql_num_rows($resultnumprocod5d);

$numprocodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod7d = $db->query($numprocodquery7d)->fetch();
$numpro_cod7d = mysql_num_rows($resultnumprocod7d);

$numprocodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod14d = $db->query($numprocodquery14d)->fetch();
$numpro_cod14d = mysql_num_rows($resultnumprocod14d);

$numprocodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod30 = $db->query($numprocodquery30)->fetch();
$numpro_cod30 = mysql_num_rows($resultnumprocod30);

$numprocodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod60 = $db->query($numprocodquery60)->fetch();
$numpro_cod60 = mysql_num_rows($resultnumprocod60);

$numprocodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumprocod365 = $db->query($numprocodquery365)->fetch();
$numpro_cod365 = mysql_num_rows($resultnumprocod365);

//Total COD Shipments Cancelled
$numcancodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod1d = $db->query($numcancodquery1d)->fetch();
$numcan_cod1d = mysql_num_rows($resultnumcancod1d);

$numcancodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod2d = $db->query($numcancodquery2d)->fetch();
$numcan_cod2d = mysql_num_rows($resultnumcancod2d);

$numcancodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod3d = $db->query($numcancodquery3d)->fetch();
$numcan_cod3d = mysql_num_rows($resultnumcancod3d);

$numcancodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod4d = $db->query($numcancodquery4d)->fetch();
$numcan_cod4d = mysql_num_rows($resultnumcancod4d);

$numcancodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod5d = $db->query($numcancodquery5d)->fetch();
$numcan_cod5d = mysql_num_rows($resultnumcancod5d);

$numcancodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod7d = $db->query($numcancodquery7d)->fetch();
$numcan_cod7d = mysql_num_rows($resultnumcancod7d);

$numcancodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod14d = $db->query($numcancodquery14d)->fetch();
$numcan_cod14d = mysql_num_rows($resultnumcancod14d);

$numcancodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod30 = $db->query($numcancodquery30)->fetch();
$numcan_cod30 = mysql_num_rows($resultnumcancod30);

$numcancodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod60 = $db->query($numcancodquery60)->fetch();
$numcan_cod60 = mysql_num_rows($resultnumcancod60);

$numcancodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumcancod365 = $db->query($numcancodquery365)->fetch();
$numcan_cod365 = mysql_num_rows($resultnumcancod365);

//Total COD Shipments Shipped
$numshicodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod1d = $db->query($numshicodquery1d)->fetch();
$numshi_cod1d = mysql_num_rows($resultnumshicod1d);

$numshicodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod2d = $db->query($numshicodquery2d)->fetch();
$numshi_cod2d = mysql_num_rows($resultnumshicod2d);

$numshicodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod3d = $db->query($numshicodquery3d)->fetch();
$numshi_cod3d = mysql_num_rows($resultnumshicod3d);

$numshicodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod4d = $db->query($numshicodquery4d)->fetch();
$numshi_cod4d = mysql_num_rows($resultnumshicod4d);

$numshicodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod5d = $db->query($numshicodquery5d)->fetch();
$numshi_cod5d = mysql_num_rows($resultnumshicod5d);

$numshicodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod7d = $db->query($numshicodquery7d)->fetch();
$numshi_cod7d = mysql_num_rows($resultnumshicod7d);

$numshicodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod14d = $db->query($numshicodquery14d)->fetch();
$numshi_cod14d = mysql_num_rows($resultnumshicod14d);

$numshicodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod30 = $db->query($numshicodquery30)->fetch();
$numshi_cod30 = mysql_num_rows($resultnumshicod30);

$numshicodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod60 = $db->query($numshicodquery60)->fetch();
$numshi_cod60 = mysql_num_rows($resultnumshicod60);

$numshicodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumshicod365 = $db->query($numshicodquery365)->fetch();
$numshi_cod365 = mysql_num_rows($resultnumshicod365);

//total COD shipments accepted
$numacccodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod1d = $db->query($numacccodquery1d)->fetch();
$numacc_cod1d = mysql_num_rows($resultnumacccod1d);

$numacccodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod2d = $db->query($numacccodquery2d)->fetch();
$numacc_cod2d = mysql_num_rows($resultnumacccod2d);

$numacccodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod3d = $db->query($numacccodquery3d)->fetch();
$numacc_cod3d = mysql_num_rows($resultnumacccod3d);

$numacccodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod4d = $db->query($numacccodquery4d)->fetch();
$numacc_cod4d = mysql_num_rows($resultnumacccod4d);

$numacccodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod5d = $db->query($numacccodquery5d)->fetch();
$numacc_cod5d = mysql_num_rows($resultnumacccod5d);

$numacccodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod7d = $db->query($numacccodquery7d)->fetch();
$numacc_cod7d = mysql_num_rows($resultnumacccod7d);

$numacccodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod14d = $db->query($numacccodquery14d)->fetch();
$numacc_cod14d = mysql_num_rows($resultnumacccod14d);

$numacccodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod30 = $db->query($numacccodquery30)->fetch();
$numacc_cod30 = mysql_num_rows($resultnumacccod30);

$numacccodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod60 = $db->query($numacccodquery60)->fetch();
$numacc_cod60 = mysql_num_rows($resultnumacccod60);

$numacccodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumacccod365 = $db->query($numacccodquery365)->fetch();
$numacc_cod365 = mysql_num_rows($resultnumacccod365);

// Total COD Shipments Disputed
$numdiscodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod1d = $db->query($numdiscodquery1d)->fetch();
$numdis_cod1d = mysql_num_rows($resultnumdiscod1d);

$numdiscodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod2d = $db->query($numdiscodquery2d)->fetch();
$numdis_cod2d = mysql_num_rows($resultnumdiscod2d);

$numdiscodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod3d = $db->query($numdiscodquery3d)->fetch();
$numdis_cod3d = mysql_num_rows($resultnumdiscod3d);

$numdiscodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod4d = $db->query($numdiscodquery4d)->fetch();
$numdis_cod4d = mysql_num_rows($resultnumdiscod4d);

$numdiscodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod5d = $db->query($numdiscodquery5d)->fetch();
$numdis_cod5d = mysql_num_rows($resultnumdiscod5d);

$numdiscodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod7d = $db->query($numdiscodquery7d)->fetch();
$numdis_cod7d = mysql_num_rows($resultnumdiscod7d);


$numdiscodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod14d = $db->query($numdiscodquery14d)->fetch();
$numdis_cod14d = mysql_num_rows($resultnumdiscod14d);

$numdiscodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod30 = $db->query($numdiscodquery30)->fetch();
$numdis_cod30 = mysql_num_rows($resultnumdiscod30);

$numdiscodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod60 = $db->query($numdiscodquery60)->fetch();
$numdis_cod60 = mysql_num_rows($resultnumdiscod60);

$numdiscodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (20) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdiscod365 = $db->query($numdiscodquery365)->fetch();
$numdis_cod365 = mysql_num_rows($resultnumdiscod365);

//total COD shipments Returned
$numretcodquery1d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod1d = $db->query($numretcodquery1d)->fetch();
$numret_cod1d = mysql_num_rows($resultnumretcod1d);

$numretcodquery2d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod2d = $db->query($numretcodquery2d)->fetch();
$numret_cod2d = mysql_num_rows($resultnumretcod2d);

$numretcodquery3d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod3d = $db->query($numretcodquery3d)->fetch();
$numret_cod3d = mysql_num_rows($resultnumretcod3d);

$numretcodquery4d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod4d = $db->query($numretcodquery4d)->fetch();
$numret_cod4d = mysql_num_rows($resultnumretcod4d);

$numretcodquery5d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod5d = $db->query($numretcodquery5d)->fetch();
$numret_cod5d = mysql_num_rows($resultnumretcod5d);

$numretcodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod7d = $db->query($numretcodquery7d)->fetch();
$numret_cod7d = mysql_num_rows($resultnumretcod7d);

$numretcodquery14d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod14d = $db->query($numretcodquery14d)->fetch();
$numret_cod14d = mysql_num_rows($resultnumretcod14d);

$numretcodquery30 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod30 = $db->query($numretcodquery30)->fetch();
$numret_cod30 = mysql_num_rows($resultnumretcod30);

$numretcodquery60 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod60 = $db->query($numretcodquery60)->fetch();
$numret_cod60 = mysql_num_rows($resultnumretcod60);

$numretcodquery365 = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumretcod365 = $db->query($numretcodquery365)->fetch();
$numret_cod365 = mysql_num_rows($resultnumretcod365);

//total COD shipments delivered
$numdelcodquery1d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod1d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 1 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod1d = $db->query($numdelcodquery1d)->fetch();
$numdel_cod1d = mysql_num_rows($resultnumdelcod1d);

$numdelcodquery2d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod2d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 2 day) AND DATE_SUB(now(), interval 1 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod2d = $db->query($numdelcodquery2d)->fetch();
$numdel_cod2d = mysql_num_rows($resultnumdelcod2d);

$numdelcodquery3d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod3d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 3 day) AND DATE_SUB(now(), interval 2 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod3d = $db->query($numdelcodquery3d)->fetch();
$numdel_cod3d = mysql_num_rows($resultnumdelcod3d);

$numdelcodquery4d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod4d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 4 day) AND DATE_SUB(now(), interval 3 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod4d = $db->query($numdelcodquery4d)->fetch();
$numdel_cod4d = mysql_num_rows($resultnumdelcod4d);

$numdelcodquery5d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod5d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 5 day) AND DATE_SUB(now(), interval 4 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod5d = $db->query($numdelcodquery5d)->fetch();
$numdel_cod5d = mysql_num_rows($resultnumdelcod5d);


$numdelcodquery7d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod7d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod7d = $db->query($numdelcodquery7d)->fetch();
$numdel_cod7d = mysql_num_rows($resultnumdelcod7d);

$numdelcodquery14d = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod14d FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 14 day) AND DATE_SUB(now(), interval 7 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod14d = $db->query($numdelcodquery14d)->fetch();
$numdel_cod14d = mysql_num_rows($resultnumdelcod14d);

$numdelcodquery30 = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod30 = $db->query($numdelcodquery30)->fetch();
$numdel_cod30 = mysql_num_rows($resultnumdelcod30);

$numdelcodquery60 = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod60 = $db->query($numdelcodquery60)->fetch();
$numdel_cod60 = mysql_num_rows($resultnumdelcod60);

$numdelcodquery365 = "SELECT `sales_flat_shipment`.`increment_id` as numdelcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 365 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";
$resultnumdelcod365 = $db->query($numdelcodquery365)->fetch();
$numdel_cod365 = mysql_num_rows($resultnumdelcod365);


$vendorKhantil = getVendorStatsCOD('5003');
$vendorRaashi = getVendorStatsCOD('5777'); //colour trendz
$vendorVandV = getVendorStatsCOD('4489'); //JD Creation
$vendorFabfiza = getVendorStatsCOD('4780');
$vendorJahnavi = getVendorStatsCOD('4763'); //Harshika
$vendorJTInt = getVendorStatsCOD('4733'); //Astrock
echo "Khantil";
//print_r($vendorKhantil); exit;
	
$sql = "SELECT t1.`created_at` AS CreatedAt, t1.`updated_at` AS UpdatedAt, t1.`increment_id` AS ShipmentId,
		t3.`increment_id` AS Orderid,
		CASE t1.`udropship_status`
		WHEN 11
		THEN 'Processing'
		WHEN 15
		THEN 'Shipped To Craftsvilla'
		WHEN 16
		THEN 'QC Rejected'
		WHEN 17
		THEN 'Received In Craftsvilla'
		WHEN 18
		THEN 'Product Out Of Stock'
		END AS STATUS, t2.`vendor_name` AS VendorName, t2.`telephone` AS VendorTelephone, t4.`firstname` AS custFName, t4.`lastname` AS custLName, t4.`country_id` AS CountryId
		FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
		`sales_flat_order` AS t3, `sales_flat_order_address` AS t4
		WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
		AND (t1.`order_id` = t3.entity_id)
		AND (t3.`entity_id` = t4.`parent_id`)
		AND (t4.`country_id` != 'IN')
		AND (t4.`address_type` = 'shipping')
		AND t1.`udropship_status` IN('11','15','16','17','18') ORDER BY t3.`increment_id` ASC";
	
	$result = $db->query($sql)->fetchAll();
	$result1 = $db->query($sql)->fetchAll();
	$result2 = $db->query($sql)->fetchAll() ;
	$result3 = $db->query($sql)->fetchAll();
	$fields_cnt = mysql_num_fields($result);
	$total_intshipments = mysql_num_rows($result);
	$contents ='Customer Name,CreatedAt,ShipmentId,Orderid,Shipment Status,CountryID,VendorName,Order Shipping Status,Days Delayed Created,Days Delayed Updated,Only QCR Pending,Total Related Shipments,Order Percentage Complete,Seller Telephone';
	$contents .="\n";
   
		$delay_array=array();
		$delayup_array=array();
		$k=0;
		$order80com = 0;
		$lastOrderId = '999';
		$orderShipStatus = 'Ready To Ship';
		$notcompleteOrder = array();
 		//while($row1 = mysql_fetch_array($result1))
		foreach($result1 as $row1)
                        {
			   if(($row1['STATUS'] == 'Processing') || ($row1['STATUS'] == 'Shipped To Craftsvilla') || ($row1['STATUS'] == 'QC Rejected'))
				{
					$notcompleteOrder[] = $row1['Orderid'];	
				}
				
			}

		$notoutofstockOrder = array();
		//while($row2 = mysql_fetch_array($result2))
		foreach($result2 as $row2)
                        {
                           if(($row2['STATUS'] == 'Processing') || ($row2['STATUS'] == 'Shipped To Craftsvilla') || ($row2['STATUS'] == 'QC Rejected') || ($row2['STATUS'] == 'Received In Craftsvilla'))
                                {
                                        $notoutofstockOrder[] = $row2['Orderid'];
                                }

                        }

		 $procshipOrder = array();
               // while($row3 = mysql_fetch_array($result3))
		foreach($result3 as $row3)
                        {
                           if(($row3['STATUS'] == 'Processing') || ($row3['STATUS'] == 'Shipped To Craftsvilla'))
                                {
                                        $procshipOrder[] = $row3['Orderid'];
                                }

                        }
		$i = 0;
		$totalReady =0;
		$readyOrder = array();
		$ready80Order = array();
	  	//while($row = mysql_fetch_array($result))
		foreach($result as $row)
			{
				$shipment_date = date('Y-m-d h:i:s', strtotime(trim($row['CreatedAt'])));
				$shipmentupdate_date = date('Y-m-d h:i:s', strtotime(trim($row['UpdatedAt'])));
				$to_day_date = time();
				$your_date = strtotime($shipment_date);
				$your_update = strtotime($shipmentupdate_date);
				$datediff = ($to_day_date - $your_date);
				$dateupdiff = ($to_day_date - $your_update);
				$delay_array[$i] = floor(($datediff)/(60*60*24));
				$delayup_array[$i] = floor(($dateupdiff)/(60*60*24));
				if (in_array($row['Orderid'],$notcompleteOrder))
				{
					$orderShipStatus = 'Order Not Complete';
				}
				elseif((!in_array($row['Orderid'],$notoutofstockOrder)) && ($row['STATUS'] == 'Product Out Of Stock'))
				{
					$orderShipStatus = 'Order Out Of Stock';
				}
				else
				{
					$orderShipStatus = 'Ready To Ship';
					$totalReady++;
					$readyOrder[] =$row['Orderid'];
				}
				$qcpendingdelayed = 'Not QC pending';
				if((!in_array($row['Orderid'],$procshipOrder)) && ($row['STATUS'] == 'QC Rejected'))
                                {
                                        $orderShipStatus = 'Order Only QC Reject Pending';
					if($delayup_array[$i] > 7)
					$qcpendingdelayed = 'QC Pending Delayed - Ship Today';
					else
					$qcpendingdelayed = 'QC Pending - Wait';
                                }


			$resultOrdQuery = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
                        `sales_flat_order` AS t3, `sales_flat_order_address` AS t4
                        WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
                        AND (t1.`order_id` = t3.entity_id)
                        AND (t3.`entity_id` = t4.`parent_id`)
                        AND (t4.`country_id` != 'IN')
                        AND (t4.`address_type` = 'shipping')
                        AND t3.`increment_id` = '".$row['Orderid']."'"; 

		        $resultOrd = $db->query($resultOrdQuery);
        		$num_shipments = mysql_num_rows($resultOrd); 

			$resultOrdComQuery = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
                        `sales_flat_order` AS t3, `sales_flat_order_address` AS t4
                        WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
                        AND (t1.`order_id` = t3.entity_id)
                        AND (t3.`entity_id` = t4.`parent_id`)
                        AND (t4.`country_id` != 'IN')
                        AND (t4.`address_type` = 'shipping')
			AND t1.`udropship_status` IN (1,17,18)
                        AND t3.`increment_id` = '".$row['Orderid']."'"; 

                        $resultOrdCom = $db->query($resultOrdComQuery)->fetch();
                        $num_shipments_complete = mysql_num_rows($resultOrdCom);
			$perCom = round(($num_shipments_complete/$num_shipments)*100); 
			if($perCom >= '80') $ready80Order[] = $row['Orderid'];
			$vendorName = str_replace(',', '', $row['VendorName']);
			$vendorPhone = str_replace(',', '/', $row['VendorTelephone']);
			$custName = $row['custFName'].' '.$row['custLName'];


				$contents .=''.$custName.',';
				$contents .=''.$row['CreatedAt'].',';
				$contents .=''.$row['ShipmentId'].',';
				$contents .=''.$row['Orderid'].',';
				$contents .=''.$row['STATUS'].',';
				$contents .=''.$row['CountryId'].',';
                                $contents .=''.$vendorName.',';
                                $contents .=''.$orderShipStatus.',';
                                $contents .=''.$delay_array[$i].' Days,';
                                $contents .=''.$delayup_array[$i].' Days,';
                                $contents .=''.$qcpendingdelayed.',';
                                $contents .=''.$num_shipments.',';
                                $contents .=''.$perCom.'%,';
				$contents .=''.$vendorPhone.',';
				$contents .= "\n";		
				$i++;
	 		} 

		$average_delay= round(array_sum($delay_array)/count($delay_array));
		$fp=fopen('/var/www/html/media/misreport/InternationalShipmentNew.csv','w');
		fputs($fp, $contents);
    	fclose($fp);
		$resultPrcQuery = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
			`sales_flat_order` AS t3, `sales_flat_order_address` AS t4
			WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
			AND (t1.`order_id` = t3.entity_id)
			AND (t3.`entity_id` = t4.`parent_id`)
			AND (t4.`country_id` != 'IN')
			AND (t4.`address_type` = 'shipping')
			AND t1.`udropship_status` = '11'";

	$resultPrc = $db->query($resultPrcQuery)->fetch();
	$num_rows = mysql_num_rows($resultPrc);

	$resultPrcQuery1 = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
		`sales_flat_order` AS t3, `sales_flat_order_address` AS t4
		WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
		AND (t1.`order_id` = t3.entity_id)
		AND (t3.`entity_id` = t4.`parent_id`)
		AND (t4.`country_id` != 'IN')
		AND (t4.`address_type` = 'shipping')
		AND t1.`udropship_status` = '15'";

	$resultPrc1 = $db->query($resultPrcQuery1)->fetch();
	$num_rows1 = mysql_num_rows($resultPrc1);
	
	$resultPrcQuery2 = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
		`sales_flat_order` AS t3, `sales_flat_order_address` AS t4
		WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
		AND (t1.`order_id` = t3.entity_id)
		AND (t3.`entity_id` = t4.`parent_id`)
		AND (t4.`country_id` != 'IN')
		AND (t4.`address_type` = 'shipping')
		AND t1.`udropship_status` = '16'";

	$resultPrc2 = $db->query($resultPrcQuery2)->fetch();
	$num_rows2 = mysql_num_rows($resultPrc2);

	$resultPrcQuery3 = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
		`sales_flat_order` AS t3, `sales_flat_order_address` AS t4
		WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
		AND (t1.`order_id` = t3.entity_id)
		AND (t3.`entity_id` = t4.`parent_id`)
		AND (t4.`country_id` != 'IN')
		AND (t4.`address_type` = 'shipping')
		AND t1.`udropship_status` = '17'";

	$resultPrc3 = $db->query($resultPrcQuery3)->fetch();
	$num_rows3 = mysql_num_rows($resultPrc3);

	$resultPrcQuery4 = "SELECT t1.`udropship_status`FROM `sales_flat_shipment` AS t1, `udropship_vendor` AS t2,
                `sales_flat_order` AS t3, `sales_flat_order_address` AS t4
                WHERE (t1.`udropship_vendor` = t2.`vendor_id`)
                AND (t1.`order_id` = t3.entity_id)
                AND (t3.`entity_id` = t4.`parent_id`)
                AND (t4.`country_id` != 'IN')
                AND (t4.`address_type` = 'shipping')
                AND t1.`udropship_status` = '18'";

        $resultPrc4 = $db->query($resultPrcQuery4)->fetch();
        $num_rows4 = mysql_num_rows($resultPrc4);

	//Email Function
	$arrayrecvdcv = array($countrecvd1day,$countrecvd2day,$countrecvd3day,$countrecvd4day,$countrecvd5day);
	$arrayshcu = array($countshcu1day,$countshcu2day,$countshcu3day,$countshcu4day,$countshcu5day);
	$arrayshcuc = array($countshcu1dayc,$countshcu2dayc,$countshcu3dayc,$countshcu4dayc,$countshcu5dayc);
	$arrayshcuct = array($countshcu1dayct,$countshcu2dayct,$countshcu3dayct,$countshcu4dayct,$countshcu5dayct);
	$arrayshcv = array($countshcvd1day,$countshcvd2day,$countshcvd3day,$countshcvd4day,$countshcvd5day);
	$arraydelcod = array($numdel_cod1d,$numdel_cod2d,$numdel_cod3d,$numdel_cod4d,$numdel_cod5d);
	$arrayacccod = array($numacc_cod1d,$numacc_cod2d,$numacc_cod3d,$numacc_cod4d,$numacc_cod5d);
	$arrayprocod = array($numpro_cod1d,$numpro_cod2d,$numpro_cod3d,$numpro_cod4d,$numpro_cod5d);
	$arrayretcod = array($numret_cod1d,$numret_cod2d,$numret_cod3d,$numret_cod4d,$numret_cod5d);
	$arraycancod = array($numcan_cod1d,$numcan_cod2d,$numcan_cod3d,$numcan_cod4d,$numcan_cod5d);
	$arraydiscod = array($numdis_cod1d,$numdis_cod2d,$numdis_cod3d,$numdis_cod4d,$numdis_cod5d);
	$arrayshicod = array($numshi_cod1d,$numshi_cod2d,$numshi_cod3d,$numshi_cod4d,$numshi_cod5d);
	$arraycod = array($num_cod1d,$num_cod2d,$num_cod3d,$num_cod4d,$num_cod5d);
	$orderTotalReady = sizeof(array_unique($readyOrder));
	$orderTotal80Ready = sizeof(array_unique($ready80Order));
	$message = 'International Shipment Report: Total Pending Shipments: '.$total_intshipments.', Total Orders Ready To Ship: '.$orderTotalReady;
	$body ='<p> Total Shipments Ready to Ship Today: '.$totalReady.'</p><br> <p> Total Orders Ready To Ship: '.$orderTotalReady.'</p><br><p> Orders 80% Ready : '.$orderTotal80Ready.'</p> <br><table border="1"><th>Processing</th><th>Shipped To Craftsvilla</th><th>QC Rejected</th><th>Received In Craftsvilla</th><th>Product Out Of Stock<tr><td>'.$num_rows.'</td><td>'.$num_rows1.'</td><td>'.$num_rows2.'</td><td>'.$num_rows3.'</td><td>'.$num_rows4.'</td><td></table><br><br><table border="1"><th>Minimum Delay</th><th>Maximum Delay</th><th>Average Delay</th><tr><td>'.min($delay_array).'</td><td>'.max($delay_array).'</td><td>'.$average_delay.'</td></tr></table>';
	$body .='<br><table border="1"><tr><td>Status</td><td>Last 24 Hours</td><td>2nd Day</td><td>3rd Day</td><td>4th Day</td><td>5th Day</td><td>Total</td><td>Last 7 Days</td><td>Previous Week</td><td>Last 30 Days</td><td>Previous Month</td><td>Last 365 Days</td></tr>
                <tr><td>Shipped To CV</td><td>'.$countshcvd1day.'</td><td>'.$countshcvd2day.'</td><td>'.$countshcvd3day.'</td><td>'.$countshcvd4day.'</td><td>'.$countshcvd5day.'</td><td>'.array_sum($arrayshcv).'</td><td>'.$countshcvdL7day.'</td><td>'.$countshcvdL14day.'</td><td>'.$countshcvdL30day.'</td><td>'.$countshcvdL60day.'</td><td>NA</td></tr>
                <tr><td>Received in CV</td><td>'.$countrecvd1day.'</td><td>'.$countrecvd2day.'</td><td>'.$countrecvd3day.'</td><td>'.$countrecvd4day.'</td><td>'.$countrecvd5day.'</td><td>'.array_sum($arrayrecvdcv).'</td><td>'.$countrecvdL7day.'</td><td>'.$countrecvdL14day.'</td><td>'.$countrecvdL30day.'</td><td>'.$countrecvdL60day.'</td><td>NA</td></tr>
                <tr><td>Shipped To Cust</td><td>'.$countshcu1day.'</td><td>'.$countshcu2day.'</td><td>'.$countshcu3day.'</td><td>'.$countshcu4day.'</td><td>'.$countshcu5day.'</td><td>'.array_sum($arrayshcu).'</td><td>'.$countshcuL7day.'</td><td>'.$countshcuL14day.'</td><td>'.$countshcuL30day.'</td><td>'.$countshcuL60day.'</td><td>NA</td></tr>
                <tr><td>International Shipments Created</td><td>'.$countshcu1dayc.'</td><td>'.$countshcu2dayc.'</td><td>'.$countshcu3dayc.'</td><td>'.$countshcu4dayc.'</td><td>'.$countshcu5dayc.'</td><td>'.array_sum($arrayshcuc).'</td><td>'.$countshcuL7dayc.'</td><td>'.$countshcuL14dayc.'</td><td>'.$countshcuL30dayc.'</td><td>'.$countshcuL60dayc.'</td><td>NA</td></tr>
                <tr><td>Total Shipments Created</td><td>'.$countshcu1dayct.'</td><td>'.$countshcu2dayct.'</td><td>'.$countshcu3dayct.'</td><td>'.$countshcu4dayct.'</td><td>'.$countshcu5dayct.'</td><td>'.array_sum($arrayshcuct).'</td><td>'.$countshcuL7dayct.'</td><td>'.$countshcuL14dayct.'</td><td>'.$countshcuL30dayct.'</td><td>'.$countshcuL60dayct.'</td><td>NA</td></tr>
                <tr><td>Total COD Created</td><td>'.$num_cod1d.'</td><td>'.$num_cod2d.'</td><td>'.$num_cod3d.'</td><td>'.$num_cod4d.'</td><td>'.$num_cod5d.'</td><td>'.array_sum($arraycod).'</td><td>'.$num_cod7d.'</td><td>'.$num_cod14d.'</td><td>'.$num_cod30.'</td><td>'.$num_cod60.'</td><td>'.$num_cod365.'</td></tr>
                <tr><td>Total COD Delivered</td><td>'.$numdel_cod1d.'</td><td>'.$numdel_cod2d.'</td><td>'.$numdel_cod3d.'</td><td>'.$numdel_cod4d.'</td><td>'.$numdel_cod5d.'</td><td>'.array_sum($arraydelcod).'</td><td>'.$numdel_cod7d.'</td><td>'.$numdel_cod14d.'</td><td>'.$numdel_cod30.'</td><td>'.$numdel_cod60.'</td><td>'.$numdel_cod365.'</td></tr>
                <tr><td>Total COD Processing</td><td>'.$numpro_cod1d.'</td><td>'.$numpro_cod2d.'</td><td>'.$numpro_cod3d.'</td><td>'.$numpro_cod4d.'</td><td>'.$numpro_cod5d.'</td><td>'.array_sum($arrayprocod).'</td><td>'.$numpro_cod7d.'</td><td>'.$numpro_cod14d.'</td><td>'.$numpro_cod30.'</td><td>'.$numpro_cod60.'</td><td>'.$numpro_cod365.'</td></tr>
                <tr><td>Total COD Accepted</td><td>'.$numacc_cod1d.'</td><td>'.$numacc_cod2d.'</td><td>'.$numacc_cod3d.'</td><td>'.$numacc_cod4d.'</td><td>'.$numacc_cod5d.'</td><td>'.array_sum($arrayacccod).'</td><td>'.$numacc_cod7d.'</td><td>'.$numacc_cod14d.'</td><td>'.$numacc_cod30.'</td><td>'.$numacc_cod60.'</td><td>'.$numacc_cod365.'</td></tr>
                <tr><td>Total COD Returned</td><td>'.$numret_cod1d.'</td><td>'.$numret_cod2d.'</td><td>'.$numret_cod3d.'</td><td>'.$numret_cod4d.'</td><td>'.$numret_cod5d.'</td><td>'.array_sum($arrayretcod).'</td><td>'.$numret_cod7d.'</td><td>'.$numret_cod14d.'</td><td>'.$numret_cod30.'</td><td>'.$numret_cod60.'</td><td>'.$numret_cod365.'</td></tr>
                <tr><td>Total COD Cancelled</td><td>'.$numcan_cod1d.'</td><td>'.$numcan_cod2d.'</td><td>'.$numcan_cod3d.'</td><td>'.$numcan_cod4d.'</td><td>'.$numcan_cod5d.'</td><td>'.array_sum($arraycancod).'</td><td>'.$numcan_cod7d.'</td><td>'.$numcan_cod14d.'</td><td>'.$numcan_cod30.'</td><td>'.$numcan_cod60.'</td><td>'.$numcan_cod365.'</td></tr>
                <tr><td>Total COD Disputed</td><td>'.$numdis_cod1d.'</td><td>'.$numdis_cod2d.'</td><td>'.$numdis_cod3d.'</td><td>'.$numdis_cod4d.'</td><td>'.$numdis_cod5d.'</td><td>'.array_sum($arraydiscod).'</td><td>'.$numdis_cod7d.'</td><td>'.$numdis_cod14d.'</td><td>'.$numdis_cod30.'</td><td>'.$numdis_cod60.'</td><td>'.$numdis_cod365.'</td></tr>
                <tr><td>Total COD Shipped</td><td>'.$numshi_cod1d.'</td><td>'.$numshi_cod2d.'</td><td>'.$numshi_cod3d.'</td><td>'.$numshi_cod4d.'</td><td>'.$numshi_cod5d.'</td><td>'.array_sum($arrayshicod).'</td><td>'.$numshi_cod7d.'</td><td>'.$numshi_cod14d.'</td><td>'.$numshi_cod30.'</td><td>'.$numshi_cod60.'</td><td>'.$numshi_cod365.'</td></tr>
                </table>
		<br>Total COD Return %: 
';
$retTotalPercentage = round(($numret_cod365/($numret_cod365+$numref_cod365+$numdel_cod365))*100);
$canTotalPercentage = round(($numcan_cod365/($num_cod365))*100);
$delTotalPercentage = round(($numdel_cod365/($num_cod365))*100);
$body .= $retTotalPercentage;
$body .='<br>Total Cancelled %:'.$canTotalPercentage;
$body .='<br>Total Delivered %:'.$delTotalPercentage;

$body .='<br><br><table border="1"><tr><td>Vendor Name</td><td>Total Created</td><td>Total Created Last 30 Days</td><td>Total Created Last 7 Days</td><td>Delivered Last 30 Days</td><td>Processing</td><td>Accepted</td><td>Refunded</td><td>Dispute Raised</td><td>Cancelled</td><td>Returned</td><td>Total Delivered</td><td>Return %</td></tr>
	<tr><td>Khantil</td><td>'.$vendorKhantil[5].'</td><td>'.$vendorKhantil[6].'</td><td>'.$vendorKhantil[7].'</td><td>'.$vendorKhantil[0].'</td><td>'.$vendorKhantil[1].'</td><td>'.$vendorKhantil[2].'</td><td>'.$vendorKhantil[3].'</td><td>'.$vendorKhantil[4].'</td><td>'.$vendorKhantil[8].'</td><td>'.$vendorKhantil[9].'</td><td>'.$vendorKhantil[10].'</td><td>'.$vendorKhantil[11].'%</td></tr>
	<tr><td>Fabfiza</td><td>'.$vendorFabfiza[5].'</td><td>'.$vendorFabfiza[6].'</td><td>'.$vendorFabfiza[7].'</td><td>'.$vendorFabfiza[0].'</td><td>'.$vendorFabfiza[1].'</td><td>'.$vendorFabfiza[2].'</td><td>'.$vendorFabfiza[3].'</td><td>'.$vendorFabfiza[4].'</td><td>'.$vendorFabfiza[8].'</td><td>'.$vendorFabfiza[9].'</td><td>'.$vendorFabfiza[10].'</td><td>'.$vendorFabfiza[11].'%</td></tr>
	<tr><td>JD Creation</td><td>'.$vendorVandV[5].'</td><td>'.$vendorVandV[6].'</td><td>'.$vendorVandV[7].'</td><td>'.$vendorVandV[0].'</td><td>'.$vendorVandV[1].'</td><td>'.$vendorVandV[2].'</td><td>'.$vendorVandV[3].'</td><td>'.$vendorVandV[4].'</td><td>'.$vendorVandV[8].'</td><td>'.$vendorVandV[9].'</td><td>'.$vendorVandV[10].'</td><td>'.$vendorVandV[11].'%</td></tr>
	<tr><td>Colour Trendz</td><td>'.$vendorRaashi[5].'</td><td>'.$vendorRaashi[6].'</td><td>'.$vendorRaashi[7].'</td><td>'.$vendorRaashi[0].'</td><td>'.$vendorRaashi[1].'</td><td>'.$vendorRaashi[2].'</td><td>'.$vendorRaashi[3].'</td><td>'.$vendorRaashi[4].'</td><td>'.$vendorRaashi[8].'</td><td>'.$vendorRaashi[9].'</td><td>'.$vendorRaashi[10].'</td><td>'.$vendorRaashi[11].'%</td></tr>
	<tr><td>Harshika</td><td>'.$vendorJahnavi[5].'</td><td>'.$vendorJahnavi[6].'</td><td>'.$vendorJahnavi[7].'</td><td>'.$vendorJahnavi[0].'</td><td>'.$vendorJahnavi[1].'</td><td>'.$vendorJahnavi[2].'</td><td>'.$vendorJahnavi[3].'</td><td>'.$vendorJahnavi[4].'</td><td>'.$vendorJahnavi[8].'</td><td>'.$vendorJahnavi[9].'</td><td>'.$vendorJahnavi[10].'</td><td>'.$vendorJahnavi[11].'%</td></tr>
	<tr><td>Astrock International</td><td>'.$vendorJTInt[5].'</td><td>'.$vendorJTInt[6].'</td><td>'.$vendorJTInt[7].'</td><td>'.$vendorJTInt[0].'</td><td>'.$vendorJTInt[1].'</td><td>'.$vendorJTInt[2].'</td><td>'.$vendorJTInt[3].'</td><td>'.$vendorJTInt[4].'</td><td>'.$vendorJTInt[8].'</td><td>'.$vendorJTInt[9].'</td><td>'.$vendorJTInt[10].'</td><td>'.$vendorJTInt[11].'%</td></tr>
	</table>
	<br>';
	$body .= '<a href="http://175.41.147.59/media/misreport/InternationalShipmentNew.csv">click here to download the Excel</a>';


	$mail = Mage::getModel('core/email');
	$mail->setToName('Craftsvilla Places');
	$mail->setToEmail('monica@craftsvilla.com');
	$mail->setBody($body);
	$mail->setSubject($message);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Tech");
	$mail->setType('html');
	
	if($mail->send())
	{echo "Email sent to your emailid sucessfully";}
	else {echo "Failed to send Email";}

 $mail1 = Mage::getModel('core/email');
        $mail1->setToName('Craftsvilla Places');
        $mail1->setToEmail('manoj@craftsvilla.com');
        $mail1->setBody($body);
        $mail1->setSubject($message);
        $mail1->setFromEmail('places@craftsvilla.com');
        $mail1->setFromName("Tech");
        $mail1->setType('html');

 if($mail1->send())
        {echo "Email sent to your emailid sucessfully";}
        else {echo "Failed to send Email";}



function getVendorStatsCOD($vendorid)
{

	$countStat = array();

 $numcodquery7d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 7 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumcod7d = $db->query($numcodquery7d)->fetch();
echo $num_cod7d = mysql_num_rows($resultnumcod7d);

$numcodquery30d = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumcod30d = $db->query($numcodquery30d)->fetch();
echo $num_cod30d = mysql_num_rows($resultnumcod30d);

$numcodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumcod = $db->query($numcodquery)->fetch();
echo $num_cod = mysql_num_rows($resultnumcod);


	$numdelcodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.updated_at BETWEEN DATE_SUB(now(), interval 30 day) AND DATE_SUB(now(), interval 0 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumdelcod = $db->query($numdelcodquery)->fetch();
echo $numdel_cod = mysql_num_rows($resultnumdelcod);

$numdelTcodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumdelTcod = $db->query($numdelTcodquery)->fetch();
echo $numdelT_cod = mysql_num_rows($resultnumdelTcod);

	$numprocodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumprocod = $db->query($numprocodquery)->fetch();
echo $numpro_cod = mysql_num_rows($resultnumprocod);

	$numacccodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumacccod = $db->query($numacccodquery)->fetch();
echo $numacc_cod = mysql_num_rows($resultnumacccod);

	 $numdiscodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status = 20 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumdiscod = $db->query($numdiscodquery)->fetch();
echo $numdis_cod = mysql_num_rows($resultnumdiscod);

 $numcancodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status = 6 AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumcancod = $db->query($numcancodquery)->fetch();
echo $numcan_cod = mysql_num_rows($resultnumcancod);

$numretcodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumretcod = $db->query($numretcodquery)->fetch();
echo $numret_cod = mysql_num_rows($resultnumretcod);



	$numrefcodquery = "SELECT `sales_flat_shipment`.`increment_id` FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.udropship_status IN (12,23) AND `sales_flat_order_payment`.method = 'cashondelivery' AND `sales_flat_shipment`.udropship_vendor = '".$vendorid."'";
$resultnumrefcod = $db->query($numrefcodquery)->fetch();
echo $numref_cod = mysql_num_rows($resultnumrefcod);


	$countStat[] = $numdel_cod;
	$countStat[] = $numpro_cod;
	$countStat[] = $numacc_cod;
	$countStat[] = $numref_cod;
	$countStat[] = $numdis_cod;
	$countStat[] = $num_cod;
	$countStat[] = $num_cod30d;
	$countStat[] = $num_cod7d;
	$countStat[] = $numcan_cod;
	$countStat[] = $numret_cod;
	$countStat[] = $numdelT_cod;
	$countStat[] = round(($numret_cod/($numdelT_cod+$numref_cod+$numret_cod))*100);

       return $countStat;

}

?>
