<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

//0-30 Days
$avgorder = "SELECT round(AVG(`base_grand_total`), 2) as grand30 FROM `sales_flat_order` WHERE `created_at` > DATE_SUB(now(), interval 30 day)";

	$result = $db->query($avgorder);
	$num_rows = $result->fetch();

$avgshipment = "SELECT round(AVG(`base_total_value`),2) as avgship30 FROM `sales_flat_shipment` WHERE `created_at` > DATE_SUB(now(), interval 30 day)";

  $resultquery = $db->query($avgshipment);
	$num_rows1 = $resultquery->fetch();

$revenue = "SELECT round(SUM( `base_total_value` ),2) as totrev FROM `sales_flat_shipment` WHERE `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$revenuere = $db->query($revenue);
	$revenueres =$revenuere->fetch();

$totshipment = "SELECT count(`increment_id`) as totship30 FROM `sales_flat_shipment` WHERE `created_at` > DATE_SUB(now(), interval 30 day)";

$result1 = $db->query($totshipment);
	$num_rows2 = $result1->fetch();

$totorder = "SELECT count(`increment_id`) as totord FROM `sales_flat_order` WHERE `status` IN ('complete', 'closed', 'processing') AND `created_at` > DATE_SUB(now(), interval 30 day)";

$result2 = $db->query($totorder);
	$num_ord30 = $result2->fetch();

//cash on delivery
$numcodquery30 = "SELECT count(`increment_id`) as numcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumcod30 = $db->query($numcodquery30);
        $num_cod30 = $resultnumcod30->fetch();

//cash on delivery - delivered
$numdelcodquery30 = "SELECT count(`increment_id`) as numdelcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumdelcod30 = $db->query($numdelcodquery30);
        $numdel_cod30 = $resultnumdelcod30->fetch();

//cash on delivery - accepted
$numacccodquery30 = "SELECT count(`increment_id`) as numacccod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumacccod30 = $db->query($numacccodquery30);
        $numacc_cod30 = $resultnumacccod30->fetch();

//cash on delivery - shipped
$numshipcodquery30 = "SELECT count(`increment_id`) as numshipcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumshipcod30 = $db->query($numshipcodquery30);
        $numship_cod30 = $resultnumshipcod30->fetch();

//cash on delivery - processing
$numproccodquery30 = "SELECT count(`increment_id`) as numproccod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumproccod30 = $db->query($numproccodquery30);
        $numproc_cod30 = $resultnumproccod30->fetch();

//cash on delivery - returned
$numretcodquery30 = "SELECT count(`increment_id`) as numretcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumretcod30 = $db->query($numretcodquery30);
        $numret_cod30 = $resultnumretcod30->fetch();


//COD Sales

$salecodquery30 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as salecod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultsalecod30 = $db->query($salecodquery30);
        $sale_cod30 = $resultsalecod30->fetch();


//International Shipments
$numintlquery30 = "SELECT count(`increment_id`) as numintl30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultnumintl30 = $db->query($numintlquery30);
        $num_intl30 = $resultnumintl30->fetch();

//International Shipments - Sales
$saleintlquery30 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as saleintl30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultsaleintl30 = $db->query($saleintlquery30);
        $sale_intl30 = $resultsaleintl30->fetch();


//Refund Initiated
$numrefundquery30 = "SELECT count(`increment_id`) as numrefund30 FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status IN (12,18,19,23)";

$resultnumrefund30 = $db->query($numrefundquery30);
        $numrefund_30 = $resultnumrefund30->fetch();

//Prepaid Delivery Time
$dispatchquery30 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatch30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method != 'cashondelivery'";

$resultdispatch30 = $db->query($dispatchquery30);
        $dispatch_30 = $resultdispatch30->fetch();
echo $dispatch_30['dispatch30']; 


//COD Delivery Time
$dispatchcodquery30 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchcod30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultdispatchcod30 = $db->query($dispatchcodquery30);
        $dispatch_cod30 = $resultdispatchcod30->fetch();
echo $dispatch_cod30['dispatchcod30'];


//International Shipping Time
$dispatchintlquery30 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchintl30 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at > DATE_SUB(now(), interval 30 day) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.udropship_status = 1";

$resultdispatchintl30 = $db->query($dispatchintlquery30);
        $dispatch_intl30 = $resultdispatchintl30->fetch();

//31-60 Days
$today = date("Y-m-d H:i:s");
	//$today1 = date("Y-m-d H:i:s",strtotime("+31 days"));
	//$today2 = date("Y-m-d H:i:s",strtotime("+60 days"));

//$avgorder1 = "SELECT round(AVG(`base_grand_total`),2) as avg60 FROM `sales_flat_order` WHERE `created_at` > DATE_SUB('".$today1."', interval 30 day)";
$avgorder1 = "SELECT ROUND(AVG(`base_grand_total`),2) AS avg60 FROM `sales_flat_order` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 31 DAY)";
	 $resultavg = $db->query($avgorder1);
	 $_num_avg = $resultavg->fetch();
	
	
$avgshipment1 = "SELECT round(AVG(`base_total_value`),2) as avgship60 FROM `sales_flat_shipment` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 31 DAY)";

  $resultquery1 = $db->query($avgshipment1);
	$_num_avgship60 = $resultquery1->fetch();

$revenue1 = "SELECT round(SUM( `base_total_value` ),2) as totrev60 FROM `sales_flat_shipment` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 31 DAY)";
$revenuere1 = $db->query($revenue1);
	$revenueres1 = $revenuere1->fetch();

$totshipment1 = "SELECT count(`increment_id`) as totship60 FROM `sales_flat_shipment` WHERE  `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 31 DAY)";

$resulttot = $db->query($totshipment1);
	$_num_ship60 = $resulttot->fetch();

$totorder1 = "SELECT count(`increment_id`) as totord60 FROM `sales_flat_order` WHERE `status` IN ('complete', 'closed', 'processing') AND `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 31 DAY)";

$resultord = $db->query($totorder1);
	$_num_rows3 = $resultord->fetch();

//cash on delivery
$numcodquery60 = "SELECT count(`increment_id`) as numcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumcod60 = $db->query($numcodquery60);
        $num_cod60 = $resultnumcod60->fetch();
echo $num_cod60['numcod60'];

//cash on delivery - delivered
$numdelcodquery60 = "SELECT count(`increment_id`) as numdelcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumdelcod60 = $db->query($numdelcodquery60);
        $numdel_cod60 = $resultnumdelcod60->fetch();
echo $numdel_cod60['numdelcod60'];

//cash on delivery - accepted
$numacccodquery60 = "SELECT count(`increment_id`) as numacccod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumacccod60 = $db->query($numacccodquery60);
        $numacc_cod60 = $resultnumacccod60->fetch();
echo $numacc_cod60['numacccod60'];

//cash on delivery - shipped
$numshipcodquery60 = "SELECT count(`increment_id`) as numshipcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumshipcod60 = $db->query($numshipcodquery60);
        $numship_cod60 = $resultnumshipcod60->fetch();
echo $numship_cod60['numshipcod60'];

//cash on delivery - processing
$numproccodquery60 = "SELECT count(`increment_id`) as numproccod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumproccod60 = $db->query($numproccodquery60);
        $numproc_cod60 = $resultnumproccod60->fetch();

//cash on delivery - returned
$numretcodquery60 = "SELECT count(`increment_id`) as numretcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumretcod60 = $db->query($numretcodquery60);
        $numret_cod60 = $resultnumretcod60->fetch();


//cash on delivery
$salecodquery60 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as salecod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultsalecod60 = $db->query($salecodquery60);
        $sale_cod60 = $resultsalecod60->fetch();
echo $sale_cod60['salecod60'];

//International Shipments
$numintlquery60 = "SELECT count(`increment_id`) as numintl60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultnumintl60 = $db->query($numintlquery60);
        $num_intl60 = $resultnumintl60->fetch();
echo $num_intl60['numintl60'];

//International Shipments - Sales
$saleintlquery60 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as saleintl60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultsaleintl60 = $db->query($saleintlquery60);
        $sale_intl60 = $resultsaleintl60->fetch();
echo $sale_intl60['saleintl60'];

//Refund Initiated
$numrefundquery60 = "SELECT count(`increment_id`) as numrefund60 FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status IN (12,18,19,23)";

$resultnumrefund60 = $db->query($numrefundquery60);
        $numrefund_60 = $resultnumrefund60->fetch();

//Prepaid Delivery Time
$dispatchquery60 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatch60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method != 'cashondelivery'";

$resultdispatch60 = $db->query($dispatchquery60);
        $dispatch_60 = $resultdispatch60->fetch();

//COD Delivery Time
$dispatchcodquery60 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchcod60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultdispatchcod60 = $db->query($dispatchcodquery60);
        $dispatch_cod60 = $resultdispatchcod60->fetch();

//International Shipping Time
$dispatchintlquery60 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchintl60 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 60 day) AND DATE_SUB(NOW(), INTERVAL 31 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.udropship_status = 1";

$resultdispatchintl60 = $db->query($dispatchintlquery60);
        $dispatch_intl60 = $resultdispatchintl60->fetch();


//61-90 Days

$today3 = date("Y-m-d H:i:s",strtotime("+61 days"));
	$today4 = date("Y-m-d H:i:s",strtotime("+90 days"));
$avgorder1 = "SELECT round(AVG(`base_grand_total`),2) as avg90 FROM `sales_flat_order` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND DATE_SUB(NOW(), INTERVAL 61 DAY)";

	$resultavg = $db->query($avgorder1);
	$_num_avg1 = $resultavg->fetch();
	
	
$avgshipment2 = "SELECT round(AVG(`base_total_value`),2) as avgship90 FROM `sales_flat_shipment` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND DATE_SUB(NOW(), INTERVAL 61 DAY)";

  $resultquery2 = $db->query($avgshipment2);
	$_num_avgship = $resultquery2->fetch();

$revenue2 = "SELECT round(SUM( `base_total_value` ),2) as totrev90 FROM `sales_flat_shipment` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND DATE_SUB(NOW(), INTERVAL 61 DAY)";
$revenuere2 = $db->query($revenue2);
	$revenueres90 = $revenuere2->fetch();

$totshipment2 = "SELECT count(`increment_id`) as totship90 FROM `sales_flat_shipment` WHERE `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND DATE_SUB(NOW(), INTERVAL 61 DAY)";

$resulttot1 = $db->query($totshipment2);
	$_num_rows90 = $resulttot1->fetch();

$totorder2 = "SELECT count(`increment_id`) as totord90 FROM `sales_flat_order` WHERE `status` IN ('complete', 'closed', 'processing') AND `created_at` BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND DATE_SUB(NOW(), INTERVAL 61 DAY)";

$resultord1 = $db->query($totorder2);
	$_num_ord90 = $resultord1->fetch();

//cash on delivery
$numcodquery90 = "SELECT count(`increment_id`) as numcod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumcod90 = $db->query($numcodquery90);
        $num_cod90 = $resultnumcod90->fetch();
echo $num_cod90['numcod90']; 

//cash on delivery - delivered
$numdelcodquery90 = "SELECT count(`increment_id`) as numdelcod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumdelcod90 = $db->query($numdelcodquery90);
        $numdel_cod90 = $resultnumdelcod90->fetch();
echo $numdel_cod90['numdelcod90'];

//cash on delivery - accepted
$numacccodquery90 = "SELECT count(`increment_id`) as numacccod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 24 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumacccod90 = $db->query($numacccodquery90);
        $numacc_cod90 = $resultnumacccod90->fetch();
echo $numacc_cod90['numacccod90'];


//cash on delivery - shipped
$numshipcodquery90 = "SELECT count(`increment_id`) as numshipcod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumshipcod90 = $db->query($numshipcodquery90);
        $numship_cod90 = $resultnumshipcod90->fetch();
echo $numship_cod90['numshipcod90'];

//cash on delivery - processing
$numproccodquery90 = "SELECT count(`increment_id`) as numproccod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 11 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumproccod90 = $db->query($numproccodquery90);
        $numproc_cod90 = $resultnumproccod90->fetch();

//cash on delivery - returned
$numretcodquery90 = "SELECT count(`increment_id`) as numretcod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status IN (25,26) AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultnumretcod90 = $db->query($numretcodquery90);
        $numret_cod90 = $resultnumretcod90->fetch();

$salecodquery90 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as salecod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(NOW(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status != 6 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultsalecod90 = $db->query($salecodquery90);
        $sale_cod90 = $resultsalecod90->fetch();
echo $sale_cod90['salecod90'];

//International Shipments
$numintlquery90 = "SELECT count(`increment_id`) as numintl90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultnumintl90 = $db->query($numintlquery90);
        $num_intl90 = $resultnumintl90->fetch();
echo $num_intl90['numintl90'];

//International Shipments - Sales
$saleintlquery90 = "SELECT round(SUM( `sales_flat_shipment`.base_total_value ),2) as saleintl90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN'";

$resultsaleintl90 = $db->query($saleintlquery90);
        $sale_intl90 = $resultsaleintl90->fetch();
echo $sale_intl90['saleintl90'];

//Refund Initiated
$numrefundquery90 = "SELECT count(`increment_id`) as numrefund90 FROM `sales_flat_shipment` WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status IN (12,18,19,23)";

$resultnumrefund90 = $db->query($numrefundquery90);
        $numrefund_90 = $resultnumrefund90->fetch();

//Prepaid Delivery Time
$dispatchquery90 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatch90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 1 AND `sales_flat_order_payment`.method != 'cashondelivery'";

$resultdispatch90 = $db->query($dispatchquery90);
        $dispatch_90 = $resultdispatch90->fetch();

//COD Delivery Time
$dispatchcodquery90 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchcod90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_payment` ON `sales_flat_shipment`.order_id = `sales_flat_order_payment`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_shipment`.udropship_status = 7 AND `sales_flat_order_payment`.method = 'cashondelivery'";

$resultdispatchcod90 = $db->query($dispatchcodquery90);
        $dispatch_cod90 = $resultdispatchcod90->fetch();

//International Shipping Time
$dispatchintlquery90 = "SELECT AVG(DATEDIFF( `sales_flat_shipment`.updated_at, `sales_flat_shipment`.created_at )) as dispatchintl90 FROM `sales_flat_shipment` LEFT JOIN `sales_flat_order_address` ON `sales_flat_shipment`.order_id = `sales_flat_order_address`.parent_id WHERE `sales_flat_shipment`.created_at BETWEEN DATE_SUB(now(), interval 90 day) AND DATE_SUB(NOW(), INTERVAL 61 DAY) AND `sales_flat_order_address`.address_type = 'shipping' AND `sales_flat_order_address`.country_id != 'IN' AND `sales_flat_shipment`.udropship_status = 1";

$resultdispatchintl90 = $db->query($dispatchintlquery90);
        $dispatch_intl90 = $resultdispatchintl90->fetch();
	
$body ='<center><b><h2>MIS Dashboard</h2></b></center>
<br><br><b><h3><u>Summary:-</u></h3></b>
<table border=1>
<th><font color="magenta">Summary</font></th>
<th><font color="magenta">Last 30 Days</font></th>
<th><font color="magenta">31-60 Days</font></th>
<th><font color="magenta">61-90 Days</font></th>
<tr>
<td><b>Average Order Value</b></td>
<td>'.$num_rows['grand30'].'</td>
<td>'.$_num_avg['avg60'].'</td>
<td>'.$_num_avg1['avg90'].'</td>
</tr>
<tr>
<td><b>Average Shipment Value</b></td>
<td>'.$num_rows1['avgship30'].'</td>
<td>'.$_num_avgship60['avgship60'].'</td>
<td>'.$_num_avgship['avgship90'].'</td>
</tr>
<tr>
<td><b>Total Revenue</b></td>
<td>'.round(($revenueres['totrev']/100000),2).' Lakhs</td>
<td>'.round(($revenueres1['totrev60']/100000),2).' Lakhs</td>
<td>'.round(($revenueres90['totrev90']/100000),2).' Lakhs</td>
</tr>
<tr>
<td><b>Revenue Growth</b></td>
<td>'.round((($revenueres['totrev']-$revenueres1['totrev60'])/$revenueres1['totrev60'])*100).'%</td>
<td>'.round((($revenueres1['totrev60']-$revenueres90['totrev90'])/$revenueres90['totrev90'])*100).'%</td>
<td>NA</td>
</tr>
<tr>
<td><b>Total Number Of Shipments</b></td>
<td>'.$num_rows2['totship30'].'</td>
<td>'.$_num_ship60['totship60'].'</td>
<td>'.$_num_rows90['totship90'].'</td>
</tr>
<tr>
<td><b>Total Number Of Orders</b></td>
<td>'.$num_ord30['totord'].'</td>
<td>'.$_num_rows3['totord60'].'</td>
<td>'.$_num_ord90['totord90'].'</td>
</tr>
<tr>
<td><b>Total Number Of COD Shipments</b></td>
<td>'.$num_cod30['numcod30'].'</td>
<td>'.$num_cod60['numcod60'].'</td>
<td>'.$num_cod90['numcod90'].'</td>
</tr>
<tr>
<td><b>Sales Value Of COD Shipments</b></td>
<td>'.round(($sale_cod30['salecod30']/100000),2).' Lakhs</td>
<td>'.round(($sale_cod60['salecod60']/100000),2).' Lakhs</td>
<td>'.round(($sale_cod90['salecod90']/100000),2).' Lakhs</td>
</tr>
<tr>
<td><b>Sales % Value Of COD </b></td>
<td>'.round(($sale_cod30['salecod30']/$revenueres['totrev'])*100).'%</td>
<td>'.round(($sale_cod60['salecod60']/$revenueres1['totrev60'])*100).'%</td>
<td>'.round(($sale_cod90['salecod90']/$revenueres90['totrev90'])*100).'%</td>
</tr>
<tr>
<td><b>Average COD Shipment Value </b></td>
<td>'.round(($sale_cod30['salecod30']/$num_cod30['numcod30'])).'</td>
<td>'.round(($sale_cod60['salecod60']/$num_cod60['numcod60'])).'</td>
<td>'.round(($sale_cod90['salecod90']/$num_cod90['numcod90'])).'</td>
</tr>
<tr>
<td><b>Total Delivered COD Shipments</b></td>
<td>'.$numdel_cod30['numdelcod30'].'</td>
<td>'.$numdel_cod60['numdelcod60'].'</td>
<td>'.$numdel_cod90['numdelcod90'].'</td>
</tr>
<tr>
<td><b>Total Accepted COD Shipments</b></td>
<td>'.$numacc_cod30['numacccod30'].'</td>
<td>'.$numacc_cod60['numacccod60'].'</td>
<td>'.$numacc_cod90['numacccod90'].'</td>
</tr>
<tr>
<td><b>Total Shipped COD Shipments</b></td>
<td>'.$numship_cod30['numshipcod30'].'</td>
<td>'.$numship_cod60['numshipcod60'].'</td>
<td>'.$numship_cod90['numshipcod90'].'</td>
</tr>
<tr>
<td><b>Total Processing COD Shipments</b></td>
<td>'.$numproc_cod30['numproccod30'].'</td>
<td>'.$numproc_cod60['numproccod60'].'</td>
<td>'.$numproc_cod90['numproccod90'].'</td>
</tr>
<tr>
<td><b>Total Returned COD Shipments</b></td>
<td>'.$numret_cod30['numretcod30'].'</td>
<td>'.$numret_cod60['numretcod60'].'</td>
<td>'.$numret_cod90['numretcod90'].'</td>
</tr>
<tr>
<td><b>Return % COD Shipments</b></td>
<td>'.round(($numret_cod30['numretcod30']/$numdel_cod30['numdelcod30'])*100).'%</td>
<td>'.round(($numret_cod60['numretcod60']/$numdel_cod60['numdelcod60'])*100).'%</td>
<td>'.round(($numret_cod90['numretcod90']/$numdel_cod90['numdelcod90'])*100).'%</td>
</tr>
<tr>
<td><b>Total International Shipments</b></td>
<td>'.$num_intl30['numintl30'].'</td>
<td>'.$num_intl60['numintl60'].'</td>
<td>'.$num_intl90['numintl90'].'</td>
</tr>
<tr>
<td><b>Sale International Shipments</b></td>
<td>'.round(($sale_intl30['saleintl30']/100000),2).' Lakhs</td>
<td>'.round(($sale_intl60['saleintl60']/100000),2).' Lakhs</td>
<td>'.round(($sale_intl90['saleintl90']/100000),2).' Lakhs</td>
</tr>
<tr>
<td><b>International Sale % Of Total</b></td>
<td>'.round(($sale_intl30['saleintl30']/$revenueres['totrev'])*100).'%</td>
<td>'.round(($sale_intl60['saleintl60']/$revenueres1['totrev60'])*100).'%</td>
<td>'.round(($sale_intl90['saleintl90']/$revenueres90['totrev90'])*100).'%</td>
</tr>
<tr>
<td><b>Avg International Value</b></td>
<td>'.round(($sale_intl30['saleintl30']/$num_intl30['numintl30'])).'</td>
<td>'.round(($sale_intl60['saleintl60']/$num_intl60['numintl60'])).'</td>
<td>'.round(($sale_intl90['saleintl90']/$num_intl90['numintl90'])).'</td>
</tr>
<tr>
<td><b>Refunded Shipments</b></td>
<td>'.$numrefund_30['numrefund30'].'</td>
<td>'.$numrefund_60['numrefund60'].'</td>
<td>'.$numrefund_90['numrefund90'].'</td>
</tr>
<tr>
<td><b>Refund %</b></td>
<td>'.round(($numrefund_30['numrefund30']/$num_rows2['totship30'])*100).'%</td>
<td>'.round(($numrefund_60['numrefund60']/$_num_ship60['totship60'])*100).'%</td>
<td>'.round(($numrefund_90['numrefund90']/$_num_rows90['totship90'])*100).'%</td>
</tr>
<tr>
<td><b>COD Average Delivery Time</b></td>
<td>'.round($dispatch_cod30['dispatchcod30'],1).' Days</td>
<td>'.round($dispatch_cod60['dispatchcod60'],1).' Days</td>
<td>'.round($dispatch_cod90['dispatchcod90'],1).' Days</td>
</tr>
<tr>
<td><b>Prepaid Average Shipping Time</b></td>
<td>'.round($dispatch_30['dispatch30'],1).' Days</td>
<td>'.round($dispatch_60['dispatch60'],1).' Days</td>
<td>'.round($dispatch_90['dispatch90'],1).' Days</td>
</tr>
<tr>
<td><b>Average International Shipping Time</b></td>
<td>'.round($dispatch_intl30['dispatchintl30'],1).' Days</td>
<td>'.round($dispatch_intl60['dispatchintl60'],1).' Days</td>
<td>'.round($dispatch_intl90['dispatchintl90'],1).' Days</td>
</tr>
</table><br><br>';

//Top 10 Sellers

//$top10 = "SELECT * FROM `udropship_vendor` WHERE `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY ) LIMIT 0 , 10" ;
$top10 = "SELECT sfu.`vendor_id`,sfu.`vendor_name`, SUM(`base_total_value`) as Grandtotal FROM `udropship_vendor` as sfu, `sales_flat_shipment` as sfs where sfu.`vendor_id` = sfs.`udropship_vendor` and sfs.`created_at` > DATE_SUB(now(), INTERVAL 30 DAY) group by `vendor_name` order by Grandtotal desc limit 0,10";
$topvendors = $db->query($top10)->fetchAll();
$body .='<b><h3><u>Top 10 Sellers:-</u></h3></b><table border=1><th><font color="blue">Sellername</font></th><th><font color="blue">Total Revenue</font></th>';
		//while($_row = ->fetch($topvendors))
		 foreach($topvendors as $_row)
			{
			
			$sellerrev = "select SUM(`base_total_value`) as Grandtotal from `sales_flat_shipment` where `udropship_vendor` = '".$_row['vendor_id']."'";
			$grandTotal = number_format($_row['Grandtotal'],2);
			$body .='<tr><td><b>'.$_row['vendor_name'].'</b></td><td>'.$grandTotal.'</td></tr>';
			}
$body .='</table><br><br>';




//Top 10 Customers

$customerevenueQuery = "select sfoa.`firstname` as Firtname,sfoa.`lastname` as Lastname,round(max(sfo.`base_grand_total`),2) as GrandTotal,sfo.`customer_email` from `sales_flat_order` as sfo,`sales_flat_order_address` as sfoa where sfo.`entity_id` = sfoa.`parent_id` and sfo.`created_at` > DATE_SUB(now(), INTERVAL 30 DAY) group by `customer_email` order by max(sfo.`base_grand_total`) desc LIMIT 0 , 10";
$resultcustomerevenue = $db->query($customerevenueQuery)->fetchAll();
$body .='<b><h3><u>Top 10 Customers:-</u></h3></b><table border=1><th><font color="dark red">Customer FirstName</font></th><th><font color="dark red">Customer LastName</font></th><th><font color="dark red">Email-ID</font></th><th><font color="dark red">Total Revenue</font></th>';
//while($_resultcustomerevenue = ->fetch($resultcustomerevenue)){
 foreach($resultcustomerevenue as $_resultcustomerevenue){
//echo '<pre>'.$_resultcustomerevenue['GrandTotal'].'-'.$_resultcustomerevenue['customer_email'].'-'.$_resultcustomerevenue['fn'].'-'.$_resultcustomerevenue['ln'];
$custRevtotal = "select round(max(sum(`base_grand_total`)),2) as Grandtotal from  `sales_flat_order` where `customer_email` ='".$_resultcustomerevenue['customer_email']."'";
$body .='<tr><td>'.$_resultcustomerevenue['Firtname'].'</td><td>'.$_resultcustomerevenue['Lastname'].'</td><td>'.$_resultcustomerevenue['customer_email'].'</td><td>'.$_resultcustomerevenue['GrandTotal'].'</td></tr>';
}
$body .='</table><br><br>';


//Categorywise Revenue
$categoryrev = "select round(sum(`base_price`*`qty_ordered`),2) as Jewelleryrevenue from `sales_flat_order_item` join `catalog_category_product` where `sales_flat_order_item`.`product_id` = `catalog_category_product`.`product_id` AND `category_id` = '6' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$categoryrevj =  $db->query($categoryrev);
$categoryrevje = $categoryrevj->fetch();

$categoryrev1 = "select round(sum(`base_price`*`qty_ordered`),2) as sareerevenue from `sales_flat_order_item` join `catalog_category_product` where `sales_flat_order_item`.`product_id` = `catalog_category_product`.`product_id` AND `category_id` = '74' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$categoryrevs =  $db->query($categoryrev1);
$categoryrevsa = $categoryrevs->fetch();

$categoryrev2 = "select round(sum(`base_price`*`qty_ordered`),2) as homedecorrevenue from `sales_flat_order_item` join `catalog_category_product` where `sales_flat_order_item`.`product_id` = `catalog_category_product`.`product_id` AND `category_id` = '5' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$categoryrevh =  $db->query($categoryrev2);
$categoryrevjho = $categoryrevh->fetch();

$categoryrev3 = "select round(sum(`base_price`*`qty_ordered`),2) as clothingrevenue from `sales_flat_order_item` join `catalog_category_product` where `sales_flat_order_item`.`product_id` = `catalog_category_product`.`product_id` AND `category_id` = '4' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$categoryrevc =  $db->query($categoryrev3);
$categoryrevjcl = $categoryrevc->fetch();

$body .= '<b><h3><u>Categorywise Revenue:-</u></h3></b><table border=1><th><font color="green">Category</font></th><th><font color="green">Total Revenues</font></th><tr><td><font color="red">Jewellery</font></td><td>'.$categoryrevje['Jewelleryrevenue'].'</td></tr><tr><td><font color="red">Saree</font></td><td>'.$categoryrevsa['sareerevenue'].'</td></tr><tr><td><font color="red">Home Decor</font></td><td>'.$categoryrevjho['homedecorrevenue'].'</td></tr><tr><td><font color="red">Clothings</font></td><td>'.$categoryrevjcl['clothingrevenue'].'</td></tr></table><br><br>';

//shipementunnpaid 

$shipmentunpaidpro = "select count(`shipment_id`) as unpaidpro from shipmentpayout, sales_flat_shipment where shipmentpayout.shipment_id = sales_flat_shipment.increment_id AND `shipmentpayout_status` = '0' AND udropship_status = '11'";

$shipmentunpaidproc = $db->query($shipmentunpaidpro);
$shipmentunpaidproce = $shipmentunpaidproc->fetch();

$shipmentunpaidshi = "select count(`shipment_id`) as unpaidship from shipmentpayout, sales_flat_shipment where shipmentpayout.shipment_id = sales_flat_shipment.increment_id AND `shipmentpayout_status` = '0' AND udropship_status = '1'";

$shipmentunpaidship = $db->query($shipmentunpaidshi);
$shipmentunpaidshipp = $shipmentunpaidship->fetch();

$shipmentunpaidre = "select count(`shipment_id`) as unpaidrec from shipmentpayout, sales_flat_shipment where shipmentpayout.shipment_id = sales_flat_shipment.increment_id AND `shipmentpayout_status` = '0' AND udropship_status = '17'";

$shipmentunpaidrec = $db->query($shipmentunpaidre);
$shipmentunpaidrecv = $shipmentunpaidrec->fetch();

$shipmentunpaidpa = "select count(`shipment_id`) as unpaidpar from shipmentpayout, sales_flat_shipment where shipmentpayout.shipment_id = sales_flat_shipment.increment_id AND `shipmentpayout_status` = '0' AND udropship_status = '2'";

$shipmentunpaidpar = $db->query($shipmentunpaidpa);
$shipmentunpaidpart = $shipmentunpaidpar->fetch();

$body .='<b><h3><u>Finance Unpaid Report:-</u></h3></b><table border=1><th><font color="orchid">Status</font></th><th><font color="orchid">Total Shipment Unpaid</font></th><tr><td><font color="teal">Processing</font></td><td>'.$shipmentunpaidproce['unpaidpro'].'</td></tr><tr><td><font color="teal">Shipped to Customer</font></td><td>'.$shipmentunpaidshipp['unpaidship'].'</td></tr><tr><td><font color="teal">Received in Craftsvilla</font></td><td>'.$shipmentunpaidrecv['unpaidrec'].'</td></tr><tr><td><font color="teal">Partially Shipped</font></td><td>'.$shipmentunpaidpart['unpaidpar'].'</td></tr></table><br><br>';

//Dispatch


$dispatchpro = "SELECT count(*) as pro30 FROM `sales_flat_shipment` where `udropship_status` = '11' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
 $dispatchproc =  $db->query($dispatchpro);
$dispatchproces = $dispatchproc->fetch();


$dispatchshcr = "SELECT count(*) as craf30 FROM `sales_flat_shipment` where `udropship_status` = '15' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchshcra =  $db->query($dispatchshcr);
	$dispatchshcraf = $dispatchshcra->fetch();


$dispatchqc = "SELECT count(*) as qcrj30 FROM `sales_flat_shipment` where `udropship_status` = '16' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchqcr =  $db->query($dispatchqc);
	$dispatchqcrj = $dispatchqcr->fetch();

$dispatchrec = "SELECT count(*) as rec30 FROM `sales_flat_shipment` where `udropship_status` = '17' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchrece =  $db->query($dispatchrec);
	$dispatchrecv = $dispatchrece->fetch();


$dispatchpen = "SELECT count(*) as pen30 FROM `sales_flat_shipment` where `udropship_status` = '0' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";

$dispatchpend =  $db->query($dispatchpen);
	$dispatchpeng = $dispatchpend->fetch();

$dispatchpar = "SELECT count(*) as par30 FROM `sales_flat_shipment` where `udropship_status` = '2' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchpart =  $db->query($dispatchpar);
	$dispatchparti = $dispatchpart->fetch();


$dispatchonh = "SELECT count(*) as onhol30 FROM `sales_flat_shipment` where `udropship_status` = '4' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchonho =  $db->query($dispatchonh);
	$dispatchonhol = $dispatchonho->fetch();

$dispatchcan = "SELECT count(*) as can30 FROM `sales_flat_shipment` where `udropship_status` = '6' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchcanc =  $db->query($dispatchcan);
	$dispatchcance = $dispatchcanc->fetch();

$dispatchdel = "SELECT count(*) as del30 FROM `sales_flat_shipment` where `udropship_status` = '7' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchdeli =  $db->query($dispatchdel);
	$dispatchdeliv = $dispatchdeli->fetch();

$dispatchref = "SELECT count(*) as ref30 FROM `sales_flat_shipment` where `udropship_status` = '12' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchrefu =  $db->query($dispatchref);
	$dispatchrefun = $dispatchrefu->fetch();

$dispatchnotd = "SELECT count(*) as notd30 FROM `sales_flat_shipment` where `udropship_status` = '13' AND `created_at` > DATE_SUB( now( ) , INTERVAL 30 DAY )";
$dispatchnotde =  $db->query($dispatchnotd);
	$dispatchnotdel = $dispatchnotde->fetch();

$body .= '<b><h3><u>Dispatch Report:-</u></h3></b><table border="1"><th><font color="brown">STATUS</font></th><th><font color="brown">No. Of Shipments</font></th><tr><td><b>Processing</b></td><td>'.$dispatchproces['pro30'].'</td></tr><tr><td><b>Received in Craftsvilla</b></td><td>'.$dispatchrecv['rec30'].'</td></tr><tr><td><b>QC_Rejected</b></td><td>'.$dispatchqcrj['qcrj30'].'</td></tr><tr><td><b>Refund Initiated</b></td><td>'.$dispatchrefun['ref30'].'</td></tr><tr><td><b>Shipped to Craftsvilla</b></td><td>'.$dispatchshcraf['craf30'].'</td></tr><tr><td><b>Partially Shipped</b></td><td>'.$dispatchparti['par30'].'</td></tr><tr><td><b>Pending</b></td><td>'.$dispatchpeng['pen30'].'</td></tr><tr><td><b>Onhold</b></td><td>'.$dispatchonhol['onhol30'].'</td></tr><tr><td><b>Cancelled</b></td><td>'.$dispatchcance['can30'].'</td></tr><tr><td><b>Delivered</b></td><td>'.$dispatchdeliv['del30'].'</td></tr><tr><td><b>Not Delivered</b></td><td>'.$dispatchnotdel['notd30'].'</td></tr></table><br><br>';




//echo $body;
$message = "Summary Report";

	//$body .= '<a href="http://www.craftsvilla.com/media/misreport/firstshipment.csv">click here to download the Excel</a>';
	$mail = Mage::getModel('core/email');
	$mail->setToName('Craftsvilla Places');
	$mail->setToEmail('manoj@craftsvilla.com');
	$mail->setBody($body);
	$mail->setSubject($message);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Tech");
	$mail->setType('html');
	
	if($mail->send())
	{echo "Email sent to your emailid sucessfully";}
	else {echo "Failed to send Email";}

	$mail->setToEmail('monica@craftsvilla.com');
 if($mail->send())
        {echo "Email sent to your emailid sucessfully";}
        else {echo "Failed to send Email";}
	
?>
