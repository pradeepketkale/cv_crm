<?php
class Craftsvilla_Financereport_FinancereportController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();     
		$this->renderLayout();
	}

	public function downloadcodAction(){

		$ustatus = array(	'pending' =>0,
			'shipped to customer'=>1,
			'partial'=>2,
			'pendingpickup'=>8,
			'ack'=>9,
			'exported'=>10,
			'ready'=>3,
			'onhold'=>4,
			'backorder'=>5,
			'cancelled'=>6,
			'delivered'=>7,
			'processing'=>11,
			'refundintiated'=>12,
			'not delivered'=>13,
			'charge_back'=>14,
			'shipped craftsvilla'=>15,
			'qc_rejected'=>16,
			'received'=>17,
			'out of stock'=>18,
			'partial refund initiated'=>19,
			'dispute raised'=>20,
			'shipment delayed'=>21,
			'partially shipped'=>22,
			'refund to do'=>23,
			'Accepted'=>24,
			'Returned By Customer'=>25,
			'Returned To Seller'=>26,
			'Mainfest Shared'=>27,
			'COD SHIPMENT PICKED UP'=>28,
			'Packing slip printed'=>30,
			'Handed to courier'=>31,
			'Returned Recieved from customer'=>32,
			'partially recieved'=>33,
			'Damage/Lost in Transit'=>36);
		$ustatusCond = ($_GET['ustatus'] != 'all' ? "AND sfs.udropship_status=".$ustatus[$_GET['ustatus']] : '');
		$paymentCond = ($_GET['paymentstatus'] != 'all' ? "AND sp.shipmentpayout_status =".$_GET['paymentstatus'] : '');
		$CourierCond = ($_GET['couriername'] != 'all' ? "AND sfst.courier_name='".$_GET['couriername']."' " : '');

		$sWhere = "sfop.`method` = 'cashondelivery' ". $ustatusCond .' ' .$paymentCond . ' ' . $CourierCond. ' ';
		if ( isset( $_GET['startdate'] ) && isset( $_GET['enddate'] ) )
		{
			$sWhere .= "and sfo.created_at >= '".$_GET['startdate'] . "' and sfo.created_at <= '".$_GET['enddate'] . "'";
		}

		$sQuery = "SELECT sfo.`increment_id` AS order_id, sfo.`created_at` AS order_date, sfs.`increment_id` AS shipment_id,
		case sfs.`udropship_status` 
		when 0 then 'pending'
		when 1 then 'shipped to customer'
		when 2 then 'partial'
		when 8 then 'pendingpickup'
		when 9 then 'ack'
		when 10 then 'exported'
		when 3 then 'ready'
		when 4 then 'onhold'
		when 5 then 'backorder'
		when 6 then 'cancelled'
		when 7 then 'delivered'
		when 11 then 'processing'
		when 12 then 'refundintiated'
		when 13 then 'not delivered'
		when 14 then 'charge_back'
		when 15 then 'shipped craftsvilla'
		when 16 then 'qc_rejected'
		when 17 then 'received'
		when 18 then 'out of stock'
		when 19 then 'partial refund initiated'
		when 20 then 'dispute raised'
		when 21 then 'shipment delayed'
		when 22 then 'partially shipped'
		when 23 then 'refund to do'
		when 24 then 'Accepted'
		when 25 then 'Returned By Customer'
		when 26 then 'Returned To Seller'
		when 27 then 'Mainfest Shared'
		when 28 then 'COD SHIPMENT PICKED UP'
		when 30 then 'Packing slip printed'
		when 31 then 'Handed to courier'
		when 32 then 'Returned Recieved from customer'
		when 33 then 'partially recieved'
		when 36 then 'Damage/Lost in Transit'
		end as ustatus,
		case sp.`shipmentpayout_status` when 0 then 'Unpaid'when 1 then 'Paid' when 2 then 'Refunded' end as payoutstatus,sfs.`created_at` AS shipment_datec, sfst.`number` AS awb_number, sfs.`updated_at` AS shipment_update, `sp`.`citibank_utr`, sp.`shipmentpayout_update_time` AS payment_updated_date, `sfs`.`udropship_vendor` AS vendor_name, sfs.`base_total_value` as SubTotal, sp.`payment_amount` AS payment_amount, sp.`commission_amount` AS comission_amount,sfst.`courier_name`
		FROM `sales_flat_shipment` as sfs
		LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
		LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
		LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`		
		LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfs`.`order_id` = `sfop`.`parent_id`
		WHERE ". $sWhere;
//LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
		//echo $sQuery;exit;
			$head = array( 'Order Id', 'Order Date', 'Shipment Id', 'Udropship Status', 'Payout Status', 'Shipment Date', 'Awb Number', 'Shipment Update', 'UTR Number', 'Payment Updated Date', 'Vendor Name', 'SubTotal', 'Payment Amount', 'Comission Amount','Courier Name' );
			$filename = "Finance_report_". $_GET['startdate'] ."_to_" . $_GET['enddate'];
			Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, true);

		}


	public function reportcodAction(){

		$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');	
		$aColumns = array( 'sfo.increment_id', 'sfo.created_at', 'sfs.increment_id', 'sfs.udropship_status', 'sp.shipmentpayout_status', 'sfs.created_at ', 'sfst.number ', 'sfs.updated_at ', 'sp.shipmentpayout_created_time ', 'sp.shipmentpayout_update_time ', 'uv.vendor_name ', 'sfs.base_total_value', 'sp.payment_amount', 'sp.commission_amount','sfst.courier_name' );
		$payout_status = array("Unpaid"=> 0, "Paid" => 1 , "Refunded" => 2);
		$ustatus = array(	'pending' =>0,
			'shipped to customer'=>1,
			'partial'=>2,
			'pendingpickup'=>8,
			'ack'=>9,
			'exported'=>10,
			'ready'=>3,
			'onhold'=>4,
			'backorder'=>5,
			'cancelled'=>6,
			'delivered'=>7,
			'processing'=>11,
			'refundintiated'=>12,
			'not delivered'=>13,
			'charge_back'=>14,
			'shipped craftsvilla'=>15,
			'qc_rejected'=>16,
			'received'=>17,
			'out of stock'=>18,
			'partial refund initiated'=>19,
			'dispute raised'=>20,
			'shipment delayed'=>21,
			'partially shipped'=>22,
			'refund to do'=>23,
			'Accepted'=>24,
			'Returned By Customer'=>25,
			'Returned To Seller'=>26,
			'Mainfest Shared'=>27,
			'COD SHIPMENT PICKED UP'=>28,
			'Packing slip printed'=>30,
			'Handed to courier'=>31,
			'Returned Recieved from customer'=>32,
			'partially recieved'=>33,
			'Damage/Lost in Transit'=>36);

	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
	{
		$sLimit = "LIMIT ".( $_GET['start'] ).", ".
		( $_GET['length'] );
	}


	/*
	 * Ordering
	 */
	if ( isset( $_GET['order'][0]['column'] ) )
	{
		$sOrder = "ORDER BY ". (intval($_GET['order'][0]['column'])+1). " " . $_GET['order'][0]['dir'];
		
	}


	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$ustatusCond = ($_GET['ustatus'] != 'all' ? "AND sfs.udropship_status=".$ustatus[$_GET['ustatus']] : '');
	$paymentCond = ($_GET['paymentstatus'] != 'all' ? "AND sp.shipmentpayout_status =".$_GET['paymentstatus'] : '');
	$CourierCond = ($_GET['couriername'] != 'all' ? "AND sfst.courier_name='".$_GET['couriername']."' " : '');

	$sWhere = "sfop.`method` = 'cashondelivery' ". $ustatusCond .' ' .$paymentCond . ' ' . $CourierCond. ' ';
	if ( isset( $_GET['startdate'] ) && isset( $_GET['enddate'] ) )
	{
		$sWhere .= "and sfo.created_at >= '".$_GET['startdate'] . "' and sfo.created_at <= '".$_GET['enddate'] . "'";
	}

	/* Total data set length */
	$sQuery = "
	SELECT count( sfo.`increment_id` ) AS order_id
	FROM `sales_flat_shipment` AS sfs
	LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
	WHERE ". $sWhere;
	$aResultTotal = $readQuery->query($sQuery)->fetchAll();
	$iTotal = (int) $aResultTotal[0][order_id];

	if ( !empty($_GET['search']['value']) )
	{
		$sWhere .= " and (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{	if($i == 3){
					$temp = $_GET['search']['value'];
					if(!array_key_exists($temp, $ustatus)){
						continue;
					}
					$sWhere .= $aColumns[$i]." = " . $ustatus[$temp]." OR ";
				}else if($i == 4){
					$temp = $_GET['search']['value'];
					if(!array_key_exists($temp, $payout_status)){
						continue;
					}
					$sWhere .= $aColumns[$i]." = " . $payout_status[$temp]." OR ";
				} else {			
					$sWhere .= $aColumns[$i]." LIKE '%".$_GET['search']['value']."%' OR ";
				}
						
					}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($aColumns) ; $i++ )
{	
	if ( $_GET['columns'][$i]['search']['regex'] == "true" && $_GET['columns'][$i]['search']['value'] != '' )
	{
		if ( $sWhere == "" )
		{
			$sWhere = "WHERE ";
		}
		else
		{
			$sWhere .= " AND ";
		}
		if($i == 3){
			$temp = str_replace(array("^", "$"), "", ($_GET['columns'][$i]['search']['value']));
			$sWhere .= $aColumns[$i]." = " . $ustatus[$temp];
		}else if($i == 4){
			$temp = str_replace(array("^", "$"), "", ($_GET['columns'][$i]['search']['value']));
			$sWhere .= $aColumns[$i]." = " . $payout_status[$temp];
		} else {			
			$sWhere .= $aColumns[$i]." ='". str_replace(array("^", "$"), "", ($_GET['columns'][$i]['search']['value']))."' ";
		}
	}
}

$is_csv = $_GET['exportcsv'];
if($is_csv == "true"){

	$sLimit = "";
}
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "SELECT sfo.`increment_id` AS order_id, sfo.`created_at` AS order_date, sfs.`increment_id` AS shipment_id,
	case sfs.`udropship_status` 
	when 0 then 'pending'
	when 1 then 'shipped to customer'
	when 2 then 'partial'
	when 8 then 'pendingpickup'
	when 9 then 'ack'
	when 10 then 'exported'
	when 3 then 'ready'
	when 4 then 'onhold'
	when 5 then 'backorder'
	when 6 then 'cancelled'
	when 7 then 'delivered'
	when 11 then 'processing'
	when 12 then 'refundintiated'
	when 13 then 'not delivered'
	when 14 then 'charge_back'
	when 15 then 'shipped craftsvilla'
	when 16 then 'qc_rejected'
	when 17 then 'received'
	when 18 then 'out of stock'
	when 19 then 'partial refund initiated'
	when 20 then 'dispute raised'
	when 21 then 'shipment delayed'
	when 22 then 'partially shipped'
	when 23 then 'refund to do'
	when 24 then 'Accepted'
	when 25 then 'Returned By Customer'
	when 26 then 'Returned To Seller'
	when 27 then 'Mainfest Shared'
	when 28 then 'COD SHIPMENT PICKED UP'
	when 30 then 'Packing slip printed'
	when 31 then 'Handed to courier'
	when 32 then 'Returned Recieved from customer'
	when 33 then 'partially recieved'
	when 36 then 'Damage/Lost in Transit'
	end as ustatus,
	case sp.`shipmentpayout_status` when 0 then 'Unpaid'when 1 then 'Paid' when 2 then 'Refunded' end as payoutstatus,sfs.`created_at` AS shipment_datec, sfst.`number` AS awb_number, sfs.`updated_at` AS shipment_update, `utr`.`utrno`, sp.`shipmentpayout_update_time` AS payment_updated_date, uv.`vendor_name` AS vendor_name, sfs.`base_total_value` as SubTotal, sp.`payment_amount` AS payment_amount, sp.`commission_amount` AS comission_amount,sfst.`courier_name`
	FROM `sales_flat_shipment` as sfs
	LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
	LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
	WHERE ". $sWhere ." " . $sOrder . " " . $sLimit;

	//echo ($sQuery);exit;

	//var_dump($rResult);exit;
	if($is_csv == "true"){
		$head = array( 'Order Id', 'Order Date', 'Shipment Id', 'Udropship Status', 'Payout Status', 'Shipment Date', 'Awb Number', 'Shipment Update', 'UTR Number', 'Payment Updated Date', 'Vendor Name', 'SubTotal', 'Payment Amount', 'Comission Amount','Courier Name' );
			// header( 'Content-Type: text/csv' );
	//          header( 'Content-Disposition: attachment;filename='.$filename);
	//          $fp = fopen('php://output', 'w');
	//          fputcsv($fp, $head); 
	//          foreach ($rResult as $line) { 
			// 	fputcsv($fp, array_values($line)); 
	  // 	    }
	  // 	    fclose($fp);
	  // 	    exit;
		$readQuery-> closeConnection();
		$filename = "Finance_report_". $_GET['startdate'] ."_to_" . $_GET['enddate'];
		Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, true);
		exit;
	/*	$file = fopen('demosaved.csv', 'w');
		fputcsv($file, $head); 
		foreach ($rResult as $row)
		{
			fputcsv($file, array_values($row));
		}

			// Close the file
		fclose($file);

		$csvFile    = "demosaved.csv";

	//location for php to create zip file.
		$zipPath    = "csvFile.zip";

		$zip = new ZipArchive();
		if ($zip->open($zipPath,ZipArchive::CREATE)) {
			$zip->addFile($csvFile, $csvFile);
			$zip->close();
			    //Make sure the zip file created and output it.
			if(is_file($zipPath)){
				header('Content-type: application/octet-stream; charset=utf-8');
				header('Content-Disposition: attachment; filename="'.$csvFile.'.zip"');
				header('Content-Length: ' . filesize($zipPath));
				readfile($zipPath,false);
			}   
		}*/
	}

	$rResult = $readQuery->query($sQuery)->fetchAll();

	for ($temploop = 0; $temploop < count($rResult); $temploop++){
		$realcommision  = Mage::getModel('udropship/vendor_statement')->getCommission($rResult[$temploop] ["shipment_id"]);
		$rResult[$temploop]["comission_amount"] = $realcommision;
	}
	/* Data set length after filtering */
	$sQuery = "
	SELECT count( sfo.`increment_id` ) AS order_id
	FROM `sales_flat_shipment` AS sfs
	LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
	WHERE ". $sWhere;

	$rResultFilterTotal = $readQuery->query($sQuery)->fetchAll();
	$iFilteredTotal = (int) $rResultFilterTotal[0]['order_id'];// count($rResult);


	//Close Connection
	$readQuery-> closeConnection();

	/*
	 * Output
	 */
	$output = array(
		"draw" => intval($_GET['draw']),
		"recordsTotal" => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data" => array()
		);

	$row = array();
	for ($i = 0 ; $i < count($rResult) ; $i++){
		$row[$i] = array_values($rResult[$i]);
	}
	$output['data'] = $row;
	$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true)->appendBody(json_encode($output));;
}

public function downloadCSV($filename, $sqlQuery, $headerArray, $reportCod = false){

	$lower_limit = 0;
	$upper_limit = 5000;
	$flag = true;
	
	$filePathOfCsv = Mage::getBaseDir('media').DS.$filename.'.csv';
	unlink ( $filePathOfCsv );
	$fp=fopen($filePathOfCsv,'a');
	fputcsv($fp, $headerArray);
	fclose($fp);
	while ($flag){
		
		$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
		$sqlLimit = " LIMIT ". $lower_limit . ",". $upper_limit;
		$sqlFinalQuery = $sqlQuery .$sqlLimit;
		//var_dump($sqlFinalQuery);
		$rResult = $readQuery->query($sqlFinalQuery)->fetchAll();
		if(!$rResult){
			break;	
		};
		if($reportCod){
			$inCondition = '';
			foreach ($rResult as $key => $value) {
				$inCondition .= ($value['vendor_name'].',');
			}
			$inCondition = rtrim($inCondition, ",");
			$sqlVendorName = "select `vendor_id`,`vendor_name` from `udropship_vendor` where `vendor_id` in (".$inCondition.")";
			//echo $sqlVendorName;exit;
			$rVendor = $readQuery->query($sqlVendorName)->fetchAll();

			foreach ($rResult as $key1 => $value1) {
				foreach ($rVendor as $key => $value) {
					if($value['vendor_id'] == $value1['vendor_name'] ){
							$rResult[$key1]['vendor_name'] = $value['vendor_name'];
					}
				}
				$realcommision  = Mage::getModel('udropship/vendor_statement')->getCommission($rResult[$key1] ["shipment_id"]);
				$rResult[$key1]["comission_amount"] = $realcommision;
			}
		}

		$fp=fopen($filePathOfCsv,'a');
		foreach ($rResult as $line) { 
			fputcsv($fp, array_values($line)); 
		}
		fclose($fp);
		$lower_limit = $upper_limit +1 ;
		$upper_limit = $upper_limit + 5000;
		$readQuery-> closeConnection();
	}
	
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment;filename='.$filename.'.csv');
	fclose($fp);
	readfile($filePathOfCsv,false);
	unlink ( $filePathOfCsv );
	exit ;
}

public function recoForPaymentAction(){

	$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');	
	$aColumns = array( 'sfs.`increment_id`', 'sfst.`number`', 'uv.`vendor_name`', 'uv.`vendor_id`', '`sfs`.`base_total_value`', '`utr`.`payin_date`', '`utr`.`utrno`', '`sp`.`shipmentpayout_update_time`');

	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
	{
		$sLimit = "LIMIT ".( $_GET['start'] ).", ".
		( $_GET['length'] );
	}


	/*
	 * Ordering
	 */
	if ( isset( $_GET['order'][0]['column'] ) )
	{
		$sOrder = "ORDER BY ". (intval($_GET['order'][0]['column'])+1). " " . $_GET['order'][0]['dir'];
		
	}


	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset( $_GET['startdate'] ) && isset( $_GET['enddate'] ) )
	{
		$sWhere .= "`utr`.`payin_date` >= '".$_GET['startdate'] . "' and `utr`.`payin_date` <= '".$_GET['enddate'] . "'";
	}

	/* Total data set length */
	$sQuery = "
	SELECT count(sfs.`entity_id`) AS shipment_id 
	FROM `sales_flat_shipment` as sfs
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
	WHERE ". $sWhere; 
	$aResultTotal = $readQuery->query($sQuery)->fetchAll();

	$iTotal = intval($aResultTotal[0][shipment_id]);
	//print_r($iTotal) ; exit;

	if ( !empty($_GET['search']['value']) )
	{
		$sWhere .= " and (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$_GET['search']['value']."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($aColumns) ; $i++ )
{
	if ( $_GET['columns'][$i]['search']['regex'] == "true" && $_GET['columns'][$i]['search']['value'] != '' )
	{
		if ( $sWhere == "" )
		{
			$sWhere = "WHERE ";
		}
		else
		{
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns[$i]." ='". str_replace(array("^", "$"), "", ($_GET['columns'][$i]['search']['value']))."' ";
	}
}

$is_csv = $_GET['exportcsv'];
if($is_csv == "true"){

	$sLimit = "";
}
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "SELECT sfs.`increment_id` AS shipment_id , sfst.`number` AS awb_number, uv.`vendor_name` AS merchant_name, uv.`vendor_id` AS merchand_id, `sfs`.`base_total_value` AS amount,  `utr`.`payin_date` AS pay_in_date, `utr`.`utrno` AS utr_number, `sp`.`shipmentpayout_update_time`
	FROM `sales_flat_shipment` as sfs
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
	WHERE ". $sWhere ." " . $sOrder . " " . $sLimit;

	//var_dump($rResult);exit;
	if($is_csv == "true"){
		$head = array( 'Shipment Id', 'AWB Number', 'Vendor Name', 'Merchant Name', 'Merchant Id', 'Amount', 'Pay In Date', 'UTR Number', 'Shipment Payout Update Time' );
		// header( 'Content-Type: text/csv' );
		// header( 'Content-Disposition: attachment;filename='.$filename);
		// $fp = fopen('php://output', 'w');
		// fputcsv($fp, $head); 
		// foreach ($rResult as $line) { 
		// 	fputcsv($fp, array_values($line)); 
		// }
		// fclose($fp);
		// exit;
		$readQuery-> closeConnection();
		$filename = "PaymentRecord_". $_GET['startdate'] ."_to_" . $_GET['enddate'];
		Craftsvilla_Financereport_FinancereportController::downloadCSV( $filename, $sQuery, $head);
		exit;
	}

	$rResult = $readQuery->query($sQuery)->fetchAll();
	/* Data set length after filtering */
	$sQuery = "
	SELECT count(sfs.`entity_id`) AS shipment_id
	FROM `sales_flat_shipment` as sfs
	LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
	LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
	LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
	LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
	WHERE ". $sWhere;

	$rResultFilterTotal = $readQuery->query($sQuery)->fetchAll();
	$iFilteredTotal = intval($rResultFilterTotal[0]['shipment_id']);// count($rResult);


	//Close Connection
	$readQuery-> closeConnection();

	/*
	 * Output
	 */
	$output = array(
		"draw" => intval($_GET['draw']),
		"recordsTotal" => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data" => array()
		);

	$row = array();
	for ($i = 0 ; $i < count($rResult) ; $i++){
		$row[$i] = array_values($rResult[$i]);
	}
	$output['data'] = $row;
	$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true)->appendBody(json_encode($output));;
}


public function paymentInvoiceAction(){
	
	$startdate = "";
	$enddate = "";	
	if ( isset( $_GET['startdate'] ) && isset( $_GET['enddate'] )){
		$startdate = ($_GET['startdate']);
		$enddate = ($_GET['enddate']);;
		//echo $enddate = ($_GET['startdate']);exit;
	}
	$dateFrom = "'".$startdate." 00:00:01'";

	$neworderFrom = strtotime($dateFrom)-7*24*60*60;
	$neworderFrom1 =  date('Y-m-d', $neworderFrom);
	$dateTo = "'".$enddate." 23:59:59'";
	$neworderTo = strtotime($dateTo)-7*24*60*60;
	$neworderTo1 =  date('Y-m-d', $neworderTo);    
	$selectedMonth=date("m",strtotime($dateTo));
	$year=date("Y",strtotime($dateTo));
	$statementQuery =  Mage::getSingleton('core/resource')->getConnection('custom_db');
	try {
		$selectedMonthData =$statementQuery->fetchAll("SELECT sfs.`order_id`,sfs.`statement_id` as statement_id ,sfs.`increment_id` as increment_id,sfs.`created_at` as created_at,sfs.`updated_at` as updated_at,sfs.`udropship_vendor` as udropship_vendor,sfs.`base_total_value` as base_total_value,uv.`vendor_name` as uname,sfs.`base_shipping_amount` AS `shipping_amount` FROM `sales_flat_shipment` as sfs
			LEFT JOIN `sales_flat_order_payment` as sfop
			ON sfs.`order_id` = sfop.`parent_id`
			LEFT JOIN `udropship_vendor` as uv ON sfs.`udropship_vendor` = uv.`vendor_id`
			where sfs.updated_at>=".$dateFrom." AND sfs.updated_at <=".$dateTo." AND
			((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7') OR ( sfop.`method` != 'cashondelivery' AND sfs.`udropship_status` = '1')) ");
	} catch(Exception $e){
		echo "Error";
	}
	//echo 'Total Shipments to check: '.count($selectedMonthData);
//$statementQuery->closeConnection();

    /*echo '<pre>';
    print_r($selectedMonthData);exit;*/

    //For CSV
    $filename = "Payment_Invoice_". $startdate ."_to_" . $enddate;
    $output = "";
    $fieldlist1 = array("ParentKey","LineNum","ItemDescription","ShipDate","AccountCode","TaxCode","UnitPrice","LocationCode","vendorid","vendorname","GrossVal","DeliveryDate","method");
    $fieldlist = array("DocNum","LineNum","Dscription","ShipDate","AcctCode","TaxCode","PriceBefDi","LocCode","vendorid","vendorname","GrossVal","DeliveryDate","method");


    $numfields = sizeof($fieldlist1);
    for($k =0; $k < $numfields;  $k++) {
    	$output .= $fieldlist1[$k];
    	if ($k < ($numfields-1)) $output .= ",";
    }
    $output .= "\n";

    $numfields = sizeof($fieldlist);
    for($k =0; $k < $numfields;  $k++) {
    	$output .= $fieldlist[$k];
    	if ($k < ($numfields-1)) $output .= ",";
    }
    $output .= "\n";
    $lineNum = 0;
    $lastStatementId = 0;
    //$lastday = date("Ymd",mktime(0, 0, 0,$_montharray[$selectedMonth]+1,0,$_yeararray[$selectedMonth]));
    $lastday = str_replace('-','',$neworderTo1);
    foreach($selectedMonthData as $_selectedMonthData)
    {
    	$statementId =  $_selectedMonthData['statement_id'];
    	$shipmenttId  =  $_selectedMonthData['increment_id'];
    	$updatedDate  =  $_selectedMonthData['created_at'];
    	$orderShipid  =  $_selectedMonthData['order_id'];
    	$date = str_replace('-','',substr($updatedDate,0,10));
                            //$commissionCsv = str_replace(',','',$this->getCommission($shipmenttId));
    	$commissionCsv = str_replace(',','',Mage::getModel('udropship/vendor_statement')->getCommission($shipmenttId));
                            //$commissionCsv = 100;
    	//echo "\n";
    	$vendorId = $_selectedMonthData['udropship_vendor'];
    	$vendorName = $_selectedMonthData['uname'];
    	$deliveryDate = $_selectedMonthData['updated_at'];
    	$subtotal = $_selectedMonthData['base_total_value'];
    	$itemised_total_shippingcost = $_selectedMonthData['shipping_amount'];
    	$grossValue =   $subtotal+$itemised_total_shippingcost;

    	$getMethodquery = "SELECT `method` FROM `sales_flat_order_payment` WHERE `parent_id` = '".$orderShipid."'";
    	$resultMethod = $statementQuery->query($getMethodquery)->fetch();
    	$paymentMethod = $resultMethod['method'];

    	if($lastStatementId == $statementId)
    	{
    		$lineNum++;
    	}
    	else{
    		$lineNum = 0;
    	}
    	$lastStatementId = $statementId;

    	for($m =0; $m < sizeof($fieldlist); $m++)
    	{
    		$fieldvalue = $fieldlist[$m];
    		if($fieldvalue == "DocNum")
    		{
    			$output .= $statementId;
    		}

    		if($fieldvalue == "LineNum")
    		{
    			$output .= $lineNum;
    		}
    		if($fieldvalue == "Dscription")
    		{
    			$output .= $shipmenttId;
    		}

    		if($fieldvalue == "ShipDate")
    		{
    			$output .= $lastday;
    		}

    		if($fieldvalue == "AcctCode")
    		{
    			$output .= '40101003';
    		}

    		if($fieldvalue == "TaxCode")
    		{
    			$output .= 'Service';
    		}

    		if($fieldvalue == "PriceBefDi")
    		{
    			$output .= $commissionCsv;
    		}

    		if($fieldvalue == "LocCode")
    		{
    			$output .= '2';
    		}
    		if($fieldvalue == "vendorid")
    		{
    			$output .= $vendorId;
    		}
    		if($fieldvalue == "vendorname")
    		{
    			$output .= $vendorName;
    		}
    		if($fieldvalue == "GrossVal")
    		{
    			$output .= $grossValue;
    		}
    		if($fieldvalue == "DeliveryDate")
    		{
    			$output .= $deliveryDate;
    		}
    		if($fieldvalue == "method")
    		{
    			$output .= $paymentMethod;
    		}
    		if ($m < ($numfields-1))
    		{
    			$output .= ",";
    		}


    	}
    	$output .= "\n";


    }
    // Send the CSV file to the browser for download

            // header("Content-type: text/x-csv");
            // header("Content-Disposition: attachment; filename=$filename.csv");
    $statementQuery->closeConnection();
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename='.$filename.'.csv');
    $filePathOfCsv = Mage::getBaseDir('media').DS.$filename.'.csv';
    $fp=fopen($filePathOfCsv,'w');
    fputs($fp, $output);
    fclose($fp);
    echo $output;
    unlink ( $filePathOfCsv );
    exit;

}

public function reportDamageLostAction(){
	//echo "asasd";exit();
	if(isset($_GET['status'])){
		$status = $_GET['status'];
	}
	if($status == 'unpaid'){
		$status = 0;
	}else if($status == 'paid') {
		$status = 1;
	}

	$selected_date_val = date("Y-m-d");
		//$dateOpen = date('Ymd',strtotime($selected_date_val));
 
    	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
    	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.shipmentpayout_status= '.$status.' AND a.udropship_status IN (36) AND main_table.citibank_utr != "" ') ;	
      	/*$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.shipmentpayout_status=0 AND a.udropship_status IN (7) AND `sales_flat_order_payment`.method = "cashondelivery" AND main_table.citibank_utr != "" ') ;      	*/
  //     	echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		// exit();
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
      	//Check if we got any data
      	if(count($shipmentpayout_report1_arr) == 0){
      		echo '<h2 style="text-align: center;">Error : No data Found</h2>';
      		exit;	
      	}

    	$filename = "Report_DamageLost"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("Debit Account Number","Value Date","Customer Reference No","Beneficiary Name","Payment Type","Bene Account Number","Bank Code","Account type","Amount","Payment Details 1","Payment Details 2","Payment Details 3","Payment Details 4","Payable Location Code *","Payable Location Name *","Print Location Code *","Print Location Name *","Beneficiary Address 1","Beneficiary Address 2","Beneficiary Address 3","Beneficiary Address 4","Delivery Method","Cheque Number","Bene E-mail ID","Instrument Detail 1","Instrument Detail 2","Craftsvilla Commission");
    	
		$numfields = sizeof($fieldlist);
		$i = 1;
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
		/*echo "<pre>";
		print_r($shipmentpayout_report1_arr);
		exit();*/
		
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
			$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
			//if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($vendors->getMerchantIdCity() != ''))
			if($shipmentpayout_report1_val['udropship_vendor'] != '')
    		{
		    	unset($total_amount);
		    	unset($commission_amount);
		    	unset($vendor_amount);
		    	unset($kribha_amount);
		    	unset($gen_random_number);
		    	unset($itemised_total_shippingcost);
		    
				$total_amount1 = $shipmentpayout_report1_val['subtotal'];
				$total_amount = $shipmentpayout_report1_val['subtotal'];
				$logisticamount = $shipmentpayout_report1_val['intshipingcost'];
				$_liveDate = "2012-08-21 00:00:00";
		    	$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
				
				// Below Two lines added By Dileswar for Adding Discount coupon on dated 25-07-2013
				$disCouponcode = '';
				$discountAmountCoupon = 0;
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	//$commission_amount = $shipmentpayout_report1_val['commission_percent'];
				$commission_amount = 20;
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
		    	
				// Added By Dileswar On dated 25-07-2013 For get the Value of coupon id & vendorid
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
				
				//$gen_random_number = "K".$this->gen_rand();
                       $vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1236)));
						
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						
			    	
					$vendor_amount = $vendor_amount - $logisticamount;
				
		    	$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)) - $vendor_amount);
				
				//Below lines for to update the value in shipmentpayout table ...
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
					$queryUpdateDiscount = "update shipmentpayout set `discount` ='".$discountAmountCoupon."',`couponcode` = '".$disCouponcode."' WHERE `shipment_id` = '".$shipmentpayout_report1_val['shipment_id']."'";
					$write->query($queryUpdateDiscount);
				
					$utr = $shipmentpayout_report1_val['citibank_utr'];
					$neft = 'EFT';
						if(($vendor_amount+$closingbalance) <= 0)
							{
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){$vendor_amount = 0;}
								
								else{	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									//$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									//$queryUpdate = "update shipmentpayout set `adjustment` ='".$adjustmentAmount."',`type` = '".$neft."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`payment_amount`= '".$adjustmentAmount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	
								
								}
							}	
							
				for($m =0; $m < sizeof($fieldlist); $m++) {
					$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "Debit Account Number")
		    		{
		    			//$output .= '710607028';
		    			$output .= '712097019';
		    		}
		    		
		    		if($fieldvalue == "Value Date")
		    		{
		    			$output .= $dateOpen;
		    		}
		    		
		    		if($fieldvalue == "Customer Reference No")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Beneficiary Name")
		    		{
		    			$output .= $vendors->getCheckPayTo();
		    		}
		    			
		    		if($fieldvalue == "Payment Type")
		    		{
		    			$output .= $neft;
		    		}
		    			
		    		if($fieldvalue == "Bene Account Number")
		    		{
		    			$output .= "'".$vendors->getBankAcNumber();
		    		}
		    			
		    		if($fieldvalue == "Bank Code")
		    		{
		    			$output .= strtoupper($vendors->getBankIfscCode()); 
		    		}
		    			
		    		if($fieldvalue == "Account type")
		    		{
		    			$output .= '2';
		    		}
		    			
		    		if($fieldvalue == "Amount")
		    		{
		    			$output .= str_replace(',','',number_format($vendor_amount,2));
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',substr(strtoupper($vendors->getVendorName()),0,30)));		    		}
		    		
		    		if($fieldvalue == "Payment Details 3")
		    		{
		    			$output .= "";
		    		}
		    			
		    		if($fieldvalue == "Payment Details 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 3")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Delivery Method")
		    		{
		    				$output .= "";
		    		}
					if($fieldvalue == "Cheque Number")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Bene E-mail ID")
		    		{
		    			$output .= $vendors->getEmail();
		    		}
					if($fieldvalue == "Instrument Detail 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Instrument Detail 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Craftsvilla Commission")
		    		{
		    			$output .= $kribha_amount;
		    		}
					
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    	
				}
		    	$output .= "\n";
				
    		}
	    }
		
    	//Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    $i++;
}
}
