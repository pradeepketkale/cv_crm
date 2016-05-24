<?php
class Craftsvilla_Financereport_FinancereportController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function downloadcodAction()
    {

        $ustatus     = array(
            'pending' => 0,
            'shipped to customer' => 1,
            'partial' => 2,
            'pendingpickup' => 8,
            'ack' => 9,
            'exported' => 10,
            'ready' => 3,
            'onhold' => 4,
            'backorder' => 5,
            'cancelled' => 6,
            'delivered' => 7,
            'processing' => 11,
            'refundintiated' => 12,
            'not delivered' => 13,
            'charge_back' => 14,
            'shipped craftsvilla' => 15,
            'qc_rejected' => 16,
            'received' => 17,
            'out of stock' => 18,
            'partial refund initiated' => 19,
            'dispute raised' => 20,
            'shipment delayed' => 21,
            'partially shipped' => 22,
            'refund to do' => 23,
            'Accepted' => 24,
            'COD RTO' => 25,
            'Returned By Customer' => 26,
            'Mainfest Shared' => 27,
            'COD SHIPMENT PICKED UP' => 28,
            'Packing slip printed' => 30,
            'Handed to courier' => 31,
            'Returned Recieved from customer' => 32,
            'partially recieved' => 33,
            'Damage/Lost in Transit' => 36
        );
        $ustatusCond = ($_GET['ustatus'] != 'all' ? "AND sfs.udropship_status=" . $ustatus[$_GET['ustatus']] : '');
        $paymentCond = ($_GET['paymentstatus'] != 'all' ? "AND sp.shipmentpayout_status =" . $_GET['paymentstatus'] : '');
        $CourierCond = ($_GET['couriername'] != 'all' ? "AND upper(sfst.courier_name)='" . strtoupper($_GET['couriername']) . "' " : '');

        $sWhere = "sfop.`method` = 'cashondelivery' AND sfst.number!='' " . $ustatusCond . ' ' . $paymentCond . ' ' . $CourierCond . ' ';
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $sWhere .= "and sfo.created_at >= '" . $_GET['startdate'] . "' and sfo.created_at <= '" . $_GET['enddate'] . "'";
        }

        $sQuery   = "SELECT sfo.`increment_id` AS order_id, sfo.`created_at` AS order_date, sfs.`increment_id` AS shipment_id,
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
        when 25 then 'COD RTO'
        when 26 then 'Returned By Customer'
        when 27 then 'Mainfest Shared'
        when 28 then 'COD SHIPMENT PICKED UP'
        when 30 then 'Packing slip printed'
        when 31 then 'Handed to courier'
        when 32 then 'Returned Recieved from customer'
        when 33 then 'partially recieved'
        when 36 then 'Damage/Lost in Transit'
        end as ustatus,
        case sp.`shipmentpayout_status` when 0 then 'Unpaid'when 1 then 'Paid' when 2 then 'Refunded' end as payoutstatus,sfs.`created_at` AS shipment_datec, sfst.`number` AS awb_number, sfs.`updated_at` AS shipment_update, `sp`.`citibank_utr`, sp.`shipmentpayout_update_time` AS payment_updated_date, `sfs`.`udropship_vendor` AS vendor_name, sfs.`base_total_value` as SubTotal,sfs.`base_shipping_amount` as Shipping, sp.`payment_amount` AS payment_amount, sp.`commission_amount` AS comission_amount,sfst.`courier_name`
        FROM `sales_flat_shipment` as sfs
        LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
        LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
        LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
        LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfs`.`order_id` = `sfop`.`parent_id`
        WHERE " . $sWhere;
        //LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
        //echo $sQuery;exit;
        $head     = array(
            'Order Id',
            'Order Date',
            'Shipment Id',
            'Udropship Status',
            'Payout Status',
            'Shipment Date',
            'Awb Number',
            'Shipment Update',
            'UTR Number',
            'Payment Updated Date',
            'Vendor Name',
            'SubTotal',
            'Shipping Amount',
            'Payment Amount',
            'Comission Amount',
            'Courier Name'
        );
        $filename = "Finance_report_" . $_GET['startdate'] . "_to_" . $_GET['enddate'];
        Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, true);
    }


    public function reportcodAction()
    {

        $readQuery     = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $aColumns      = array(
            'sfo.increment_id',
            'sfo.created_at',
            'sfs.increment_id',
            'sfs.udropship_status',
            'sp.shipmentpayout_status',
            'sfs.created_at ',
            'sfst.number ',
            'sfs.updated_at ',
            'sp.shipmentpayout_created_time ',
            'sp.shipmentpayout_update_time ',
            'uv.vendor_name ',
            'sfs.base_total_value',
            'sp.payment_amount',
            'sp.commission_amount',
            'sfst.courier_name'
        );
        $payout_status = array(
            "Unpaid" => 0,
            "Paid" => 1,
            "Refunded" => 2
        );
        $ustatus       = array(
            'pending' => 0,
            'shipped to customer' => 1,
            'partial' => 2,
            'pendingpickup' => 8,
            'ack' => 9,
            'exported' => 10,
            'ready' => 3,
            'onhold' => 4,
            'backorder' => 5,
            'cancelled' => 6,
            'delivered' => 7,
            'processing' => 11,
            'refundintiated' => 12,
            'not delivered' => 13,
            'charge_back' => 14,
            'shipped craftsvilla' => 15,
            'qc_rejected' => 16,
            'received' => 17,
            'out of stock' => 18,
            'partial refund initiated' => 19,
            'dispute raised' => 20,
            'shipment delayed' => 21,
            'partially shipped' => 22,
            'refund to do' => 23,
            'Accepted' => 24,
            'COD RTO' => 25,
            'Returned By Customer' => 26,
            'Mainfest Shared' => 27,
            'COD SHIPMENT PICKED UP' => 28,
            'Packing slip printed' => 30,
            'Handed to courier' => 31,
            'Returned Recieved from customer' => 32,
            'partially recieved' => 33,
            'Damage/Lost in Transit' => 36
        );

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['start']) && $_GET['length'] != '-1') {
            $sLimit = "LIMIT " . ($_GET['start']) . ", " . ($_GET['length']);
        }


        /*
         * Ordering
         */
        if (isset($_GET['order'][0]['column'])) {
            $sOrder = "ORDER BY " . (intval($_GET['order'][0]['column']) + 1) . " " . $_GET['order'][0]['dir'];

        }
        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $ustatusCond = ($_GET['ustatus'] != 'all' ? "AND sfs.udropship_status=" . $ustatus[$_GET['ustatus']] : '');
        $paymentCond = ($_GET['paymentstatus'] != 'all' ? "AND sp.shipmentpayout_status =" . $_GET['paymentstatus'] : '');
        $CourierCond = ($_GET['couriername'] != 'all' ? "AND sfst.courier_name='" . $_GET['couriername'] . "' " : '');

        $sWhere = "sfop.`method` = 'cashondelivery' " . $ustatusCond . ' ' . $paymentCond . ' ' . $CourierCond . ' ';
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $sWhere .= "and sfo.created_at >= '" . $_GET['startdate'] . "' and sfo.created_at <= '" . $_GET['enddate'] . "'";
        }

        /* Total data set length */
        $sQuery       = "
        SELECT count( sfo.`increment_id` ) AS order_id
        FROM `sales_flat_shipment` AS sfs
        LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
        LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
        LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
        LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
        LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
        WHERE " . $sWhere;
        $aResultTotal = $readQuery->query($sQuery)->fetchAll();
        $iTotal       = (int) $aResultTotal[0][order_id];

        if (!empty($_GET['search']['value'])) {
            $sWhere .= " and (";
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($i == 3) {
                    $temp = $_GET['search']['value'];
                    if (!array_key_exists($temp, $ustatus)) {
                        continue;
                    }
                    $sWhere .= $aColumns[$i] . " = " . $ustatus[$temp] . " OR ";
                } else if ($i == 4) {
                    $temp = $_GET['search']['value'];
                    if (!array_key_exists($temp, $payout_status)) {
                        continue;
                    }
                    $sWhere .= $aColumns[$i] . " = " . $payout_status[$temp] . " OR ";
                } else {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . $_GET['search']['value'] . "%' OR ";
                }

            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_GET['columns'][$i]['search']['regex'] == "true" && $_GET['columns'][$i]['search']['value'] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                if ($i == 3) {
                    $temp = str_replace(array(
                        "^",
                        "$"
                    ), "", ($_GET['columns'][$i]['search']['value']));
                    $sWhere .= $aColumns[$i] . " = " . $ustatus[$temp];
                } else if ($i == 4) {
                    $temp = str_replace(array(
                        "^",
                        "$"
                    ), "", ($_GET['columns'][$i]['search']['value']));
                    $sWhere .= $aColumns[$i] . " = " . $payout_status[$temp];
                } else {
                    $sWhere .= $aColumns[$i] . " ='" . str_replace(array(
                        "^",
                        "$"
                    ), "", ($_GET['columns'][$i]['search']['value'])) . "' ";
                }
            }
        }

        $is_csv = $_GET['exportcsv'];
        if ($is_csv == "true") {

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
        when 25 then 'COD RTO'
        when 26 then 'Returned By Customer'
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
        WHERE " . $sWhere . " " . $sOrder . " " . $sLimit;

        //echo ($sQuery);exit;

        //var_dump($rResult);exit;
        if ($is_csv == "true") {
            $head = array(
                'Order Id',
                'Order Date',
                'Shipment Id',
                'Udropship Status',
                'Payout Status',
                'Shipment Date',
                'Awb Number',
                'Shipment Update',
                'UTR Number',
                'Payment Updated Date',
                'Vendor Name',
                'SubTotal',
                'Payment Amount',
                'Comission Amount',
                'Courier Name'
            );
            // header( 'Content-Type: text/csv' );
            //          header( 'Content-Disposition: attachment;filename='.$filename);
            //          $fp = fopen('php://output', 'w');
            //          fputcsv($fp, $head);
            //          foreach ($rResult as $line) {
            //  fputcsv($fp, array_values($line));
            //      }
            //      fclose($fp);
            //      exit;
            $readQuery->closeConnection();
            $filename = "Finance_report_" . $_GET['startdate'] . "_to_" . $_GET['enddate'];
            Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, true);
            exit;
            /*  $file = fopen('demosaved.csv', 'w');
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

        for ($temploop = 0; $temploop < count($rResult); $temploop++) {
            $realcommision                          = Mage::getModel('udropship/vendor_statement')->getCommission($rResult[$temploop]["shipment_id"]);
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
    WHERE " . $sWhere;

        $rResultFilterTotal = $readQuery->query($sQuery)->fetchAll();
        $iFilteredTotal     = (int) $rResultFilterTotal[0]['order_id']; // count($rResult);


        //Close Connection
        $readQuery->closeConnection();

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
        for ($i = 0; $i < count($rResult); $i++) {
            $row[$i] = array_values($rResult[$i]);
        }
        $output['data'] = $row;
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true)->appendBody(json_encode($output));
        ;
    }

    public function downloadCSV($filename, $sqlQuery, $headerArray, $reportCod = false, $statewise = false)
    {

        $lower_limit = 0;
        $upper_limit = 5000;
        $flag        = true;

        $filePathOfCsv = Mage::getBaseDir('media') . DS . $filename . '.csv';
        unlink($filePathOfCsv);
        $fp = fopen($filePathOfCsv, 'w');
        fputcsv($fp, $headerArray);
        fclose($fp);
        while ($flag) {
            unset($rResult);
            unset($sqlLimit);
            $readQuery     = Mage::getSingleton('core/resource')->getConnection('custom_db');
            $sqlLimit      = " LIMIT " . $lower_limit . "," . $upper_limit;
            $sqlFinalQuery = $sqlQuery . $sqlLimit;
            //var_dump($sqlFinalQuery);
            $rResult       = $readQuery->query($sqlFinalQuery)->fetchAll();
            if (!$rResult) {
                break;
            }
            ;
            if ($reportCod) {
                $inCondition = '';
                foreach ($rResult as $key => $value) {
                    $inCondition .= ($value['vendor_name'] . ',');
                }
                $inCondition   = rtrim($inCondition, ",");
                $sqlVendorName = "select `vendor_id`,`vendor_name` from `udropship_vendor` where `vendor_id` in (" . $inCondition . ")";
                //echo $sqlVendorName;exit;
                $rVendor       = $readQuery->query($sqlVendorName)->fetchAll();
                $readQuery->closeConnection();
                foreach ($rResult as $key1 => $value1) {
                    foreach ($rVendor as $key => $value) {
                        if ($value['vendor_id'] == $value1['vendor_name']) {
                            $rResult[$key1]['vendor_name'] = $value['vendor_name'];
                        }
                    }
                    $realcommision                      = Mage::getModel('udropship/vendor_statement')->getCommission($rResult[$key1]["shipment_id"]);
                    $rResult[$key1]["comission_amount"] = $realcommision;
                }
            }
            if ($statewise) {
                foreach ($rResult as $key => $value) {
                    $combined_date                     = json_decode($value['pan']);
                    $rResult[$key]['tin']              = $combined_date->vat_tin_no;
                    $rResult[$key]['pan']              = $combined_date->pan_number;
                    $rResult[$key]['customer_address'] = preg_replace('/\s+/', ' ', trim($value['customer_address']));
                    $rResult[$key]['seller_address']   = preg_replace('/\s+/', ' ', trim($value['seller_address']));
                    $hlp                               = Mage::helper('udropship');
                    $discountAmount                    = $hlp->getDiscountamt($rResult[$key]['shipment_id']);
                    $rResult[$key]['GMV']              = $rResult[$key]['GMV'] - $discountAmount;
                }
            }

            $fp = fopen($filePathOfCsv, 'a');
            foreach ($rResult as $line) {
                fputcsv($fp, array_values($line));
            }
            fclose($fp);
            $lower_limit = $upper_limit + 1;
            $upper_limit = $upper_limit + 5000;

        }
        fclose($fp);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename . '.csv');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePathOfCsv));
        readfile($filePathOfCsv, false);
        unlink($filePathOfCsv);
        exit;
    }

    public function recoForPaymentAction()
    {

        $readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $aColumns  = array(
            'sfs.`increment_id`',
            'sfst.`number`',
            'uv.`vendor_name`',
            'uv.`vendor_id`',
            '`sfs`.`base_total_value`',
            '`utr`.`payin_date`',
            '`utr`.`utrno`',
            '`sp`.`shipmentpayout_update_time`'
        );

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['start']) && $_GET['length'] != '-1') {
            $sLimit = "LIMIT " . ($_GET['start']) . ", " . ($_GET['length']);
        }


        /*
         * Ordering
         */
        if (isset($_GET['order'][0]['column'])) {
            $sOrder = "ORDER BY " . (intval($_GET['order'][0]['column']) + 1) . " " . $_GET['order'][0]['dir'];

        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $sWhere .= "`utr`.`payin_date` >= '" . $_GET['startdate'] . "' and `utr`.`payin_date` <= '" . $_GET['enddate'] . "'";
        }

        /* Total data set length */
        $sQuery       = "
        SELECT count(sfs.`entity_id`) AS shipment_id
        FROM `sales_flat_shipment` as sfs
        LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
        LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
        LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
        LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
        WHERE " . $sWhere;
        $aResultTotal = $readQuery->query($sQuery)->fetchAll();

        $iTotal = intval($aResultTotal[0][shipment_id]);
        //print_r($iTotal) ; exit;

        if (!empty($_GET['search']['value'])) {
            $sWhere .= " and (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . $_GET['search']['value'] . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_GET['columns'][$i]['search']['regex'] == "true" && $_GET['columns'][$i]['search']['value'] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " ='" . str_replace(array(
                    "^",
                    "$"
                ), "", ($_GET['columns'][$i]['search']['value'])) . "' ";
            }
        }

        $is_csv = $_GET['exportcsv'];
        if ($is_csv == "true") {

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
        WHERE " . $sWhere . " " . $sOrder . " " . $sLimit;

        //var_dump($rResult);exit;
        if ($is_csv == "true") {
            $head = array(
                'Shipment Id',
                'AWB Number',
                'Vendor Name',
                'Merchant Name',
                'Merchant Id',
                'Amount',
                'Pay In Date',
                'UTR Number',
                'Shipment Payout Update Time'
            );
            // header( 'Content-Type: text/csv' );
            // header( 'Content-Disposition: attachment;filename='.$filename);
            // $fp = fopen('php://output', 'w');
            // fputcsv($fp, $head);
            // foreach ($rResult as $line) {
            //  fputcsv($fp, array_values($line));
            // }
            // fclose($fp);
            // exit;
            $readQuery->closeConnection();
            $filename = "PaymentRecord_" . $_GET['startdate'] . "_to_" . $_GET['enddate'];
            Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head);
            exit;
        }

        $rResult = $readQuery->query($sQuery)->fetchAll();
        /* Data set length after filtering */
        $sQuery  = "
        SELECT count(sfs.`entity_id`) AS shipment_id
        FROM `sales_flat_shipment` as sfs
        LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
        LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
        LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
        LEFT JOIN `utrreport` AS utr ON `sp`.`citibank_utr` = `utr`.`utrno`
        WHERE " . $sWhere;

        $rResultFilterTotal = $readQuery->query($sQuery)->fetchAll();
        $iFilteredTotal     = intval($rResultFilterTotal[0]['shipment_id']); // count($rResult);


        //Close Connection
        $readQuery->closeConnection();

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
        for ($i = 0; $i < count($rResult); $i++) {
            $row[$i] = array_values($rResult[$i]);
        }
        $output['data'] = $row;
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true)->appendBody(json_encode($output));
        ;
    }


    public function paymentInvoiceAction()
    {

        $startdate = "";
        $enddate   = "";
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $startdate = ($_GET['startdate']);
            $enddate   = ($_GET['enddate']);
            ;
            //echo $enddate = ($_GET['startdate']);exit;
        }
        $dateFrom = "'" . $startdate . " 00:00:01'";

        $neworderFrom   = strtotime($dateFrom) - 7 * 24 * 60 * 60;
        $neworderFrom1  = date('Y-m-d', $neworderFrom);
        $dateTo         = "'" . $enddate . " 23:59:59'";
        $neworderTo     = strtotime($dateTo) - 7 * 24 * 60 * 60;
        $neworderTo1    = date('Y-m-d', $neworderTo);
        $selectedMonth  = date("m", strtotime($dateTo));
        $year           = date("Y", strtotime($dateTo));
        $statementQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
        try {
            $selectedMonthData = $statementQuery->fetchAll("SELECT sfs.`order_id`,sfs.`statement_id` as statement_id ,sfs.`increment_id` as increment_id,sfs.`created_at` as created_at,sfs.`updated_at` as updated_at,sfs.`udropship_vendor` as udropship_vendor,sfs.`base_total_value` as base_total_value,uv.`vendor_name` as uname,sfs.`base_shipping_amount` AS `shipping_amount` FROM `sales_flat_shipment` as sfs
             LEFT JOIN `sales_flat_order_payment` as sfop
             ON sfs.`order_id` = sfop.`parent_id`
             LEFT JOIN `udropship_vendor` as uv ON sfs.`udropship_vendor` = uv.`vendor_id`
             where sfs.updated_at>=" . $dateFrom . " AND sfs.updated_at <=" . $dateTo . " AND
             ((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7') OR ( sfop.`method` != 'cashondelivery' AND sfs.`udropship_status` = '1')) ");
        }
        catch (Exception $e) {
            echo "Error";
        }
        //echo 'Total Shipments to check: '.count($selectedMonthData);
        //$statementQuery->closeConnection();

        /*echo '<pre>';
        print_r($selectedMonthData);exit;*/

        //For CSV
        $filename   = "Payment_Invoice_" . $startdate . "_to_" . $enddate;
        $output     = "";
        $fieldlist1 = array(
            "ParentKey",
            "LineNum",
            "ItemDescription",
            "ShipDate",
            "AccountCode",
            "TaxCode",
            "UnitPrice",
            "LocationCode",
            "vendorid",
            "vendorname",
            "GrossVal",
            "DeliveryDate",
            "method"
        );
        $fieldlist  = array(
            "DocNum",
            "LineNum",
            "Dscription",
            "ShipDate",
            "AcctCode",
            "TaxCode",
            "PriceBefDi",
            "LocCode",
            "vendorid",
            "vendorname",
            "GrossVal",
            "DeliveryDate",
            "method"
        );


        $numfields = sizeof($fieldlist1);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist1[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";

        $numfields = sizeof($fieldlist);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";
        $lineNum         = 0;
        $lastStatementId = 0;
        //$lastday = date("Ymd",mktime(0, 0, 0,$_montharray[$selectedMonth]+1,0,$_yeararray[$selectedMonth]));
        $lastday         = str_replace('-', '', $neworderTo1);
        foreach ($selectedMonthData as $_selectedMonthData) {
            $statementId                 = $_selectedMonthData['statement_id'];
            $shipmenttId                 = $_selectedMonthData['increment_id'];
            $updatedDate                 = $_selectedMonthData['created_at'];
            $orderShipid                 = $_selectedMonthData['order_id'];
            $date                        = str_replace('-', '', substr($updatedDate, 0, 10));
            //$commissionCsv = str_replace(',','',$this->getCommission($shipmenttId));
            $commissionCsv               = str_replace(',', '', Mage::getModel('udropship/vendor_statement')->getCommission($shipmenttId));
            //$commissionCsv = 100;
            //echo "\n";
            $vendorId                    = $_selectedMonthData['udropship_vendor'];
            $vendorName                  = $_selectedMonthData['uname'];
            $deliveryDate                = $_selectedMonthData['updated_at'];
            $subtotal                    = $_selectedMonthData['base_total_value'];
            $itemised_total_shippingcost = $_selectedMonthData['shipping_amount'];
            $grossValue                  = $subtotal + $itemised_total_shippingcost;

            $getMethodquery = "SELECT `method` FROM `sales_flat_order_payment` WHERE `parent_id` = '" . $orderShipid . "'";
            $resultMethod   = $statementQuery->query($getMethodquery)->fetch();
            $paymentMethod  = $resultMethod['method'];

            if ($lastStatementId == $statementId) {
                $lineNum++;
            } else {
                $lineNum = 0;
            }
            $lastStatementId = $statementId;

            for ($m = 0; $m < sizeof($fieldlist); $m++) {
                $fieldvalue = $fieldlist[$m];
                if ($fieldvalue == "DocNum") {
                    $output .= $statementId;
                }

                if ($fieldvalue == "LineNum") {
                    $output .= $lineNum;
                }
                if ($fieldvalue == "Dscription") {
                    $output .= $shipmenttId;
                }

                if ($fieldvalue == "ShipDate") {
                    $output .= $lastday;
                }

                if ($fieldvalue == "AcctCode") {
                    $output .= '40101003';
                }

                if ($fieldvalue == "TaxCode") {
                    $output .= 'Service';
                }

                if ($fieldvalue == "PriceBefDi") {
                    $output .= $commissionCsv;
                }

                if ($fieldvalue == "LocCode") {
                    $output .= '2';
                }
                if ($fieldvalue == "vendorid") {
                    $output .= $vendorId;
                }
                if ($fieldvalue == "vendorname") {
                    $output .= $vendorName;
                }
                if ($fieldvalue == "GrossVal") {
                    $output .= $grossValue;
                }
                if ($fieldvalue == "DeliveryDate") {
                    $output .= $deliveryDate;
                }
                if ($fieldvalue == "method") {
                    $output .= $paymentMethod;
                }
                if ($m < ($numfields - 1)) {
                    $output .= ",";
                }


            }
            $output .= "\n";


        }
        // Send the CSV file to the browser for download

        // header("Content-type: text/x-csv");
        // header("Content-Disposition: attachment; filename=$filename.csv");
        $statementQuery->closeConnection();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename . '.csv');
        $filePathOfCsv = Mage::getBaseDir('media') . DS . $filename . '.csv';
        $fp            = fopen($filePathOfCsv, 'w');
        fputs($fp, $output);
        fclose($fp);
        echo $output;
        unlink($filePathOfCsv);
        exit;

    }

    public function reportDamageLostAction()
    {
        //echo "asasd";exit();
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
        }
        if ($status == 'unpaid') {
            $status = 0;
        } else if ($status == 'paid') {
            $status = 1;
        }

        $selected_date_val = date("Y-m-d");
        //$dateOpen = date('Ymd',strtotime($selected_date_val));

        $shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
        $shipmentpayout_report1->getSelect()->join(array(
            'a' => 'sales_flat_shipment'
        ), 'a.increment_id=main_table.shipment_id', array(
            'udropship_vendor',
            'subtotal' => 'base_total_value',
            'commission_percent' => 'commission_percent',
            'itemised_total_shippingcost' => 'itemised_total_shippingcost',
            'cod_fee' => 'cod_fee',
            'base_shipping_amount' => 'base_shipping_amount'
        ))->join(array(
            'b' => 'sales_flat_shipment_grid'
        ), 'b.increment_id=main_table.shipment_id', array(
            'order_created_at'
        ))->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id', 'method')->joinLeft('sales_flat_shipment_track', 'a.entity_id = sales_flat_shipment_track.parent_id', array(
            'courier_name',
            'number'
        ))->where('main_table.shipmentpayout_status= ' . $status . ' AND a.udropship_status IN (36) AND `sales_flat_order_payment`.method = "cashondelivery"');
        /*$shipmentpayout_report1->getSelect()
        ->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
        ->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
        ->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
        ->where('main_table.shipmentpayout_status=0 AND a.udropship_status IN (7) AND `sales_flat_order_payment`.method = "cashondelivery" AND main_table.citibank_utr != "" ') ;       */
        /*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
        exit();*/

        $shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
        //Check if we got any data
        if (count($shipmentpayout_report1_arr) == 0) {
            echo '<h2 style="text-align: center;">Error : No data Found</h2>';
            exit;
        }

        $filename = "Report_DamageLost" . "_" . $selected_date_val;
        $output   = "";

        $fieldlist = array(
            "Debit Account Number",
            "Value Date",
            "Customer Reference No",
            "Beneficiary Name",
            "Payment Type",
            "Bene Account Number",
            "Bank Code",
            "Account type",
            "Amount",
            "Payment Details 1",
            "Payment Details 2",
            "Payment Details 3",
            "Payment Details 4",
            "Payable Location Code *",
            "Payable Location Name *",
            "Print Location Code *",
            "Print Location Name *",
            "Beneficiary Address 1",
            "Beneficiary Address 2",
            "Beneficiary Address 3",
            "Beneficiary Address 4",
            "Delivery Method",
            "Cheque Number",
            "Bene E-mail ID",
            "Instrument Detail 1",
            "Instrument Detail 2",
            "Craftsvilla Commission",
            "Courier Name",
            "AWB Number"
        );

        $numfields = sizeof($fieldlist);
        $i         = 1;

        // *********************   NOW START BUILDING THE CSV

        // Create the column headers

        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist[$k];
            if ($k < ($numfields - 1))
                $output .= ", ";
        }
        $output .= "\n";

        /*echo "<pre>";
        print_r($shipmentpayout_report1_arr);
        exit();*/

        foreach ($shipmentpayout_report1_arr as $shipmentpayout_report1_val) {
            $vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
            //if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($vendors->getMerchantIdCity() != ''))
            if ($shipmentpayout_report1_val['udropship_vendor'] != '') {
                unset($total_amount);
                unset($commission_amount);
                unset($vendor_amount);
                unset($kribha_amount);
                unset($gen_random_number);
                unset($itemised_total_shippingcost);
                $hlp               = Mage::helper('udropship');
                $vendorId          = $shipmentpayout_report1_val['udropship_vendor'];
                $commission_amount = $hlp->getVendorCommission($vendorId, $shipmentpayout_report1_val['shipment_id']);
                $service_tax       = $hlp->getServicetaxCv($shipmentpayout_report1_val['shipment_id']);
                $total_amount      = $shipmentpayout_report1_val['subtotal'];
                $logisticamount    = (125 + (125 * $service_tax));
                $_liveDate         = "2012-08-21 00:00:00";
                $order             = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);

                // Below Two lines added By Dileswar for Adding Discount coupon on dated 25-07-2013
                $disCouponcode        = '';
                $discountAmountCoupon = 0;
                $_orderCurrencyCode   = $order->getOrderCurrencyCode();
                if (($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
                    $total_amount = $shipmentpayout_report1_val['subtotal'] / 1.5;

                $itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
                $base_shipping_amount        = $shipmentpayout_report1_val['base_shipping_amount'];

                $adjustmentAmount     = $shipmentpayout_report1_val['adjustment'];
                $shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
                //Below line is for get closingBalance
                $collectionVendor     = Mage::getModel('udropship/vendor')->load($vendorId);
                $closingbalance       = $collectionVendor->getClosingBalance();

                // Added By Dileswar On dated 25-07-2013 For get the Value of coupon id & vendorid
                $couponCodeId   = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
                $_resultCoupon  = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
                $couponVendorId = $_resultCoupon->getVendorid();
                if ($couponVendorId == $vendorId) {
                    $discountAmountCoupon = $order->getBaseDiscountAmount();
                    $disCouponcode        = $order->getCouponCode();
                }
                $total_amount = $shipmentpayout_report1_val['subtotal'] + $shipmentpayout_report1_val['base_shipping_amount'] + $discountAmountCoupon;
                if ($shipmentpayout_report1_val['order_created_at'] <= '2012-07-02 23:59:59') {
                    if ($vendors->getManageShipping() == "imanage") {
                        $vendor_amount = ($total_amount * (1 - $commission_amount / 100));
                        $kribha_amount = ($shipmentpayout_report1_val['subtotal'] - $vendor_amount) + $itemised_total_shippingcost + $shipmentpayout_report1_val['cod_fee'];
                    } else {
                        $vendor_amount = (($total_amount + $itemised_total_shippingcost) * (1 - $commission_amount / 100));
                        $kribha_amount = (($shipmentpayout_report1_val['subtotal'] + $itemised_total_shippingcost) - $vendor_amount);
                    }
                } else {
                    if ($vendors->getManageShipping() == "imanage") {
                        $vendor_amount = ($total_amount * (1 - ($commission_amount / 100) * (1 + 0.1236)));
                        $kribha_amount = ($total_amount + $itemised_total_shippingcost + $shipmentpayout_report1_val['cod_fee']) * 1.00 - $vendor_amount;
                    } else {
                        //$vendor_amount = (($total_amount+$base_shipping_amount+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1450)));
                        $vendor_amount = (($total_amount) * (1 - ($commission_amount / 100) * (1 + $service_tax)));
                        $kribha_amount = ((($total_amount) * 1.00) - $vendor_amount);
                    }

                }


                $vendor_amount = $vendor_amount - $logisticamount;

                //Below lines for to update the value in shipmentpayout table ...
                $write               = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
                $queryUpdateDiscount = "update shipmentpayout set `discount` ='" . $discountAmountCoupon . "',`couponcode` = '" . $disCouponcode . "' WHERE `shipment_id` = '" . $shipmentpayout_report1_val['shipment_id'] . "'";
                $write->query($queryUpdateDiscount);

                $utr  = $shipmentpayout_report1_val['citibank_utr'];
                $neft = 'EFT';
                /*if(($vendor_amount+$closingbalance) <= 0)
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
                }   */

                for ($m = 0; $m < sizeof($fieldlist); $m++) {
                    $fieldvalue = $fieldlist[$m];
                    if ($fieldvalue == "Debit Account Number") {
                        //$output .= '710607028';
                        $output .= '712097019';
                    }

                    if ($fieldvalue == "Value Date") {
                        $output .= $dateOpen;
                    }

                    if ($fieldvalue == "Customer Reference No") {
                        $output .= $shipmentpayout_report1_val['shipment_id'];
                    }

                    if ($fieldvalue == "Beneficiary Name") {
                        $output .= $vendors->getCheckPayTo();
                    }

                    if ($fieldvalue == "Payment Type") {
                        $output .= $neft;
                    }

                    if ($fieldvalue == "Bene Account Number") {
                        $output .= "'" . $vendors->getBankAcNumber();
                    }

                    if ($fieldvalue == "Bank Code") {
                        $output .= strtoupper($vendors->getBankIfscCode());
                    }

                    if ($fieldvalue == "Account type") {
                        $output .= '2';
                    }

                    if ($fieldvalue == "Amount") {
                        $output .= str_replace(',', '', number_format($vendor_amount, 2));
                    }

                    if ($fieldvalue == "Payment Details 1") {
                        $output .= $shipmentpayout_report1_val['shipment_id'];
                    }

                    if ($fieldvalue == "Payment Details 2") {
                        $output .= preg_replace('/[^a-zA-Z0-9]/s', '', str_replace(' ', '', substr(strtoupper($vendors->getVendorName()), 0, 30)));
                    }

                    if ($fieldvalue == "Payment Details 3") {
                        $output .= "";
                    }

                    if ($fieldvalue == "Payment Details 4") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Payable Location Code *") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Payable Location Name *") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Print Location Code *") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Print Location Name *") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Beneficiary Address 1") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Beneficiary Address 2") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Beneficiary Address 3") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Beneficiary Address 4") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Delivery Method") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Cheque Number") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Bene E-mail ID") {
                        $output .= $vendors->getEmail();
                    }
                    if ($fieldvalue == "Instrument Detail 1") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Instrument Detail 2") {
                        $output .= "";
                    }
                    if ($fieldvalue == "Craftsvilla Commission") {
                        $output .= $kribha_amount;
                    }
                    if ($fieldvalue == "Courier Name") {
                        $output .= $shipmentpayout_report1_val['courier_name'];
                    }
                    if ($fieldvalue == "AWB Number") {
                        $output .= $shipmentpayout_report1_val['number'];
                    }


                    if ($m < ($numfields - 1)) {
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

    public function setCommissionPercentAction()
    {
        if (isset($_GET['vendorid']) && isset($_GET['vendorcommission']) && isset($_GET['startdate']) && isset($_GET['login_id'])) {
            $vendorid           = $_GET['vendorid'];
            $commission_percent = $_GET['vendorcommission'];
            $startdate          = $_GET['startdate'];
            $login_id           = $_GET['login_id'];
            $readQuery          = Mage::getSingleton('core/resource')->getConnection('custom_db');
            $write              = Mage::getSingleton('core/resource')->getConnection('core_write');

            $getLastCreatedDateSql = "select max(date_created) as last_created_date from finance_vendor_commission where vendor_id = " . $vendorid;
            //echo $getLastCreatedDateSql;exit;
            $result                = $readQuery->query($getLastCreatedDateSql)->fetchAll();
            $getLastCreatedDate    = $result[0]['last_created_date'];

            $sqlUpdate = "UPDATE `finance_vendor_commission` SET `end_date`='" . date('Y-m-d', (strtotime('-1 day', strtotime($startdate)))) . "' WHERE `vendor_id`=" . $vendorid . " and `date_created` = '" . $getLastCreatedDate . "'";

            $result    = $write->query($sqlUpdate);
            $datetime  = date('Y-m-d H:i:s');
            $sqlInsert = "INSERT INTO `finance_vendor_commission`(`s_no`, `vendor_id`, `commission_percent`, `start_date`,`changed_by`, `date_created`) VALUES ('DEFAULT'," . $vendorid . ",'" . $commission_percent . "','" . $startdate . "','" . $login_id . "','" . $datetime . "')";
            $result    = $write->query($sqlInsert);

            echo "<center>Successful </br> <a href='/financereportcv/dashboard.php'>Dashboard </a></center>";
        } else {
            echo "<center>Unsuccessful Please check your Inputs </br> <a href='/financereportcv/dashboard.php'>Dashboard </a></center>";
        }
    }

    public function getCommissionPercentAction()
    {

        $readQuery             = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $write                 = Mage::getSingleton('core/resource')->getConnection('core_write');
        $finalArray            = array();
        $vendorString          = '';
        $getLastCreatedDateSql = "SELECT fvc.vendor_id, uv.vendor_name, fvc.commission_percent FROM finance_vendor_commission as fvc, udropship_vendor as uv WHERE fvc.end_date = '0000-00-00' and fvc.vendor_id = uv.vendor_id order by 1 ";
        $result                = $readQuery->query($getLastCreatedDateSql)->fetchAll();
        foreach ($result as $key => $value) {
            $vendorString .= $value['vendor_id'] . ',';
        }
        $vendorString     = rtrim($vendorString, ',');
        $sqlGetVendors    = "SELECT `vendor_id`,`vendor_name`, '20' as commission_percent  FROM `udropship_vendor` where vendor_id not in (" . $vendorString . ") order by 1 ";
        $resultAllVendors = $readQuery->query($sqlGetVendors)->fetchAll();
        $nowDate          = date("Y-m-d");
        $filename         = "VendorCommission_" . $nowDate . '.csv';
        $filePathOfCsv    = Mage::getBaseDir('media') . DS . $filename . '.csv';
        $head             = array(
            'Vendor ID',
            'Vendor Name',
            'Vendor Commission'
        );
        unlink($filePathOfCsv);
        $fp = fopen($filePathOfCsv, 'w');
        fputcsv($fp, $head);
        foreach ($result as $key => $value) {
            fputcsv($fp, array_values($value));
        }
        foreach ($resultAllVendors as $key => $value) {
            fputcsv($fp, array_values($value));
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);
        fclose($fp);
        readfile($filePathOfCsv, false);
        unlink($filePathOfCsv);
        exit;
    }

    public function downloadprepaidAction()
    {

        $ustatus     = array(
            'pending' => 0,
            'shipped to customer' => 1,
            'partial' => 2,
            'pendingpickup' => 8,
            'ack' => 9,
            'exported' => 10,
            'ready' => 3,
            'onhold' => 4,
            'backorder' => 5,
            'cancelled' => 6,
            'delivered' => 7,
            'processing' => 11,
            'refundintiated' => 12,
            'not delivered' => 13,
            'charge_back' => 14,
            'shipped craftsvilla' => 15,
            'qc_rejected' => 16,
            'received' => 17,
            'out of stock' => 18,
            'partial refund initiated' => 19,
            'dispute raised' => 20,
            'shipment delayed' => 21,
            'partially shipped' => 22,
            'refund to do' => 23,
            'Accepted' => 24,
            'COD RTO' => 25,
            'Returned By Customer' => 26,
            'Mainfest Shared' => 27,
            'COD SHIPMENT PICKED UP' => 28,
            'Packing slip printed' => 30,
            'Handed to courier' => 31,
            'Returned Recieved from customer' => 32,
            'partially recieved' => 33,
            'Damage/Lost in Transit' => 36
        );
        $ustatusCond = ($_GET['ustatus'] != 'all' ? "AND sfs.udropship_status=" . $ustatus[$_GET['ustatus']] : '');
        $paymentCond = ($_GET['paymentstatus'] != 'all' ? "AND sp.shipmentpayout_status =" . $_GET['paymentstatus'] : '');
        $CourierCond = ($_GET['couriername'] != 'all' ? "AND sfst.courier_name='" . $_GET['couriername'] . "' " : '');

        $sWhere = "sfop.`method` != 'cashondelivery' " . $ustatusCond . ' ' . $paymentCond . ' ' . $CourierCond . ' ';
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $sWhere .= "and sfo.created_at >= '" . $_GET['startdate'] . "' and sfo.created_at <= '" . $_GET['enddate'] . "'";
        }

        $sQuery   = "SELECT sfo.`increment_id` AS order_id, sfo.`created_at` AS order_date, sfs.`increment_id` AS shipment_id,
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
    when 25 then 'COD RTO'
    when 26 then 'Returned By Customer'
    when 27 then 'Mainfest Shared'
    when 28 then 'COD SHIPMENT PICKED UP'
    when 30 then 'Packing slip printed'
    when 31 then 'Handed to courier'
    when 32 then 'Returned Recieved from customer'
    when 33 then 'partially recieved'
    when 36 then 'Damage/Lost in Transit'
    end as ustatus,
    case sp.`shipmentpayout_status` when 0 then 'Unpaid'when 1 then 'Paid' when 2 then 'Refunded' end as payoutstatus,sfs.`created_at` AS shipment_datec, sfst.`number` AS awb_number, sfs.`updated_at` AS shipment_update, `sp`.`citibank_utr`, sp.`shipmentpayout_update_time` AS payment_updated_date, `sfs`.`udropship_vendor` AS vendor_name, sfs.`base_total_value` as SubTotal,sfs.`base_shipping_amount` as Shipping, sp.`payment_amount` AS payment_amount, sp.`commission_amount` AS comission_amount,sfst.`courier_name`
    FROM `sales_flat_shipment` as sfs
    LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
    LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
    LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
    LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfs`.`order_id` = `sfop`.`parent_id`
    WHERE " . $sWhere;
        //LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
        //echo $sQuery;exit;
        $head     = array(
            'Order Id',
            'Order Date',
            'Shipment Id',
            'Udropship Status',
            'Payout Status',
            'Shipment Date',
            'Awb Number',
            'Shipment Update',
            'UTR Number',
            'Payment Updated Date',
            'Vendor Name',
            'SubTotal',
            'Shipping Amount',
            'Payment Amount',
            'Comission Amount',
            'Courier Name'
        );
        $filename = "Prepaid_report_" . $_GET['startdate'] . "_to_" . $_GET['enddate'];
        Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, true);
    }

    public function statewiseAction()
    {
        $vendorCond   = ($_GET['vstate'] != 'all' ? "AND uv.`region_id` =" . $_GET['vstate'] : '');
        $customerCond = ($_GET['cstate'] != 'all' ? "AND sfoa.`region_id` =" . $_GET['cstate'] : '');

        $sWhere = "sfoa.`address_type` = 'billing' " . $vendorCond . ' ' . $customerCond . ' ';
        if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
            $sWhere .= "and date(sfo.`created_at`) >= '" . $_GET['startdate'] . "' and date(sfo.`created_at`) <= '" . $_GET['enddate'] . "'";
        }

        $sQuery   = "SELECT sfo.`increment_id` AS order_id, date(sfo.`created_at`) AS order_date,sfs.`increment_id` AS shipment_id,date(sfs.`created_at`) AS shipment_date,
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
    when 25 then 'COD RTO'
    when 26 then 'Returned By Customer'
    when 27 then 'Mainfest Shared'
    when 28 then 'COD SHIPMENT PICKED UP'
    when 30 then 'Packing slip printed'
    when 31 then 'Handed to courier'
    when 32 then 'Returned Recieved from customer'
    when 33 then 'partially recieved'
    when 36 then 'Damage/Lost in Transit'
    end as ustatus, uv.`vendor_name` AS vendor_name,uv.`street` AS seller_address, uv.`custom_vars_combined` AS pan,'' as tin,dcr.`default_name` AS vendor_state,(sfs.`base_total_value`+sfs.`base_shipping_amount`) as GMV, sfop.`method` as payment_method, concat(sfoa.`firstname` ,' ',sfoa.`lastname`) as customer_name,sfoa.`region` as customer_region, concat(sfoa.`street` ,', ',sfoa.`city`) as customer_address from `sales_flat_shipment` as sfs
    LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
    LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
    LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
    LEFT JOIN `sales_flat_order_address` as sfoa ON sfoa.`parent_id` = sfo.`entity_id`
    LEFT JOIN `directory_country_region` as dcr ON uv.`region_id` = dcr.`region_id`
    where " . $sWhere;
        //echo $sQuery;exit;
        $head     = array(
            'Order Id',
            'Order Date',
            'Shipment Id',
            'Shipment Date',
            'Status',
            'Seller Name',
            'Seller Address',
            'Seller Pan',
            'Seller TIN',
            'Seller State',
            'Total Amount',
            'Payment Method',
            'Customer Name',
            'State',
            'Customer Address'
        );
        $filename = "SateWise_" . $_GET['startdate'] . "_to_" . $_GET['enddate'];
        //echo ($sQuery);exit;
        Craftsvilla_Financereport_FinancereportController::downloadCSV($filename, $sQuery, $head, false, true);
    }

    public function handlingRdrAction()
    {
        $month        = $_GET['month'];
        $year         = $_GET['year'];
        $currentMonth = date('Y-m-d', mktime(0, 0, 0, $month, date("t") - 7, $year));
        if ($month == '1') {
            $year--;
        }
        $previousMonth     = date('Y-m-d', mktime(0, 0, 0, $month - 1, date("t") - 6, $year));
        $statementQuery    = Mage::getSingleton('core/resource')->getConnection('custom_db');
        /*echo "SELECT sfs.`increment_id`,sfs.`created_at`,sfs.`udropship_vendor`,sfs.`updated_at` FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` where sfs.updated_at>='".$previousMonth." 00:00:01' AND sfs.updated_at <='".$currentMonth." 23:59:59' AND ((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7'))";exit;*/
        $selectedMonthData = $statementQuery->fetchAll("SELECT sfs.`increment_id`,sfs.`created_at`,sfs.`udropship_vendor`,sfs.`updated_at` FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` where sfs.updated_at>='" . $previousMonth . " 00:00:01' AND sfs.updated_at <='" . $currentMonth . " 23:59:59' AND ((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7'))");
        $statementQuery->closeConnection();

        /*echo '<pre>';
        print_r($selectedMonthData);*/

        //For CSV
        $filename   = "RDR1_shiphandling_" . $month . "-" . $year;
        $output     = "";
        $fieldlist1 = array(
            "ParentKey",
            "LineNum",
            "ItemDescription",
            "ShipDate",
            "AccountCode",
            "TaxCode",
            "UnitPrice",
            "LocationCode"
        );
        $fieldlist  = array(
            "DocNum",
            "LineNum",
            "Dscription",
            "ShipDate",
            "AcctCode",
            "TaxCode",
            "PriceBefDi",
            "LocCode"
        );


        $numfields = sizeof($fieldlist1);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist1[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";

        $numfields = sizeof($fieldlist);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";

        $lineNum         = 0;
        $lastStatementId = 0;
        //$lastday = date("Ymd",mktime(0, 0, 0,$_montharray[$selectedMonth]+1,0,$_yeararray[$selectedMonth]));
        $lastday         = str_replace('-', '', $neworderTo1);
        foreach ($selectedMonthData as $_selectedMonthData) {
            $statementId   = $_selectedMonthData['statement_id'];
            $shipmenttId   = $_selectedMonthData['increment_id'];
            //$updatedDate  =  $_selectedMonthData['created_at'];
            $updatedDate   = $_selectedMonthData['updated_at'];
            $date          = str_replace('-', '', substr($updatedDate, 0, 10));
            //$commissionCsv = str_replace(',','',$this->getCommission($shipmenttId));
            $commissionCsv = str_replace(',', '', Mage::getModel('udropship/vendor_statement')->getCommissionLogistic($shipmenttId));
            if ($lastStatementId == $statementId) {
                $lineNum++;
            } else {
                $lineNum = 0;
            }
            $lastStatementId = $statementId;

            for ($m = 0; $m < sizeof($fieldlist); $m++) {
                $fieldvalue = $fieldlist[$m];
                if ($fieldvalue == "DocNum") {
                    $output .= $statementId;
                }

                if ($fieldvalue == "LineNum") {
                    $output .= $lineNum;
                }

                if ($fieldvalue == "Dscription") {
                    $output .= $shipmenttId;
                }

                if ($fieldvalue == "ShipDate") {
                    //$output .= $lastday;
                    $output .= $updatedDate;
                }

                if ($fieldvalue == "AcctCode") {
                    $output .= '40101003';
                }

                if ($fieldvalue == "TaxCode") {
                    $output .= 'Service';
                }

                if ($fieldvalue == "PriceBefDi") {
                    $output .= $commissionCsv;
                }

                if ($fieldvalue == "LocCode") {
                    $output .= '2';
                }

                if ($m < ($numfields - 1)) {
                    $output .= ",";
                }

            }
            $output .= "\n";


        }
        //echo $output;exit;
        // Send the CSV file to the browser for download

        header("Content-type: text/x-csv");
        header("Content-Disposition: attachment; filename=$filename.csv");
        $filePathOfCsv = Mage::getBaseDir('media') . DS . 'misreport' . DS . $filename . '.csv';
        $fp            = fopen($filePathOfCsv, 'w');
        fputs($fp, $output);
        fclose($fp);
        echo $output;
        //exit;
    }

    public function handlingORdrAction()
    {
        $month        = $_GET['month'];
        $year         = $_GET['year'];
        $currentMonth = date('Y-m-d', mktime(0, 0, 0, $month, date("t") - 7, $year));
        if ($month == '1') {
            $year--;
        }
        $previousMonth  = date('Y-m-d', mktime(0, 0, 0, $month - 1, date("t") - 6, $year));
        $statementQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
        /*echo "SELECT `statement_id`,`increment_id`,`created_at`,`udropship_vendor` FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` where sfs.updated_at>='" . $previousMonth . " 00:00:01' AND sfs.updated_at <='" . $currentMonth . " 23:59:59' AND ((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7')) AND sfs.`statement_id` IS NOT NULL ORDER BY `statement_id` DESC ";
        exit;*/
        $selectedMonthData = $statementQuery->fetchAll("SELECT `statement_id`,`increment_id`,`created_at`,`udropship_vendor` FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` where sfs.updated_at>='" . $previousMonth . " 00:00:01' AND sfs.updated_at <='" . $currentMonth . " 23:59:59' AND ((sfop.`method` =  'cashondelivery'  AND sfs.`udropship_status` = '7')) AND sfs.`statement_id` IS NOT NULL ORDER BY `statement_id` DESC ");
        $statementQuery->closeConnection();

        /*echo '<pre>';
        print_r($selectedMonthData);*/

        //For CSV
        $filename   = "ORDR" . "-" . $selectedMonth . "-" . $year;
        $output     = "";
        $fieldlist1 = array(
            "DocNum",
            "HandWritten",
            "DocType",
            "DocDate",
            "DocDueDate",
            "CardCode",
            "TaxDate"
        );
        $fieldlist  = array(
            "DocNum",
            "HandWritten",
            "DocType",
            "DocDate",
            "DocDueDate",
            "CardCode",
            "TaxDate"
        );


        $numfields = sizeof($fieldlist1);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist1[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";

        $numfields = sizeof($fieldlist);
        for ($k = 0; $k < $numfields; $k++) {
            $output .= $fieldlist[$k];
            if ($k < ($numfields - 1))
                $output .= ",";
        }
        $output .= "\n";

        $lineNum         = 0;
        $lastStatementId = 0;

        $lastday = date("Ymd", mktime(0, 0, 0, $month + 1, 0, $year));


        foreach ($selectedMonthData as $_selectedMonthData) {

            $statementId = $_selectedMonthData['statement_id'];
            $shipmenttId = $_selectedMonthData['increment_id'];
            $createdDate = $_selectedMonthData['created_at'];
            $vendorId    = $_selectedMonthData['udropship_vendor'];
            $date        = str_replace('-', '', substr($createdDate, 0, 10));

            if ($lastStatementId != $statementId) {

                for ($m = 0; $m < sizeof($fieldlist); $m++) {
                    $fieldvalue = $fieldlist[$m];
                    if ($fieldvalue == "DocNum") {
                        $output .= $statementId;
                    }

                    if ($fieldvalue == "HandWritten") {
                        $output .= 'tYES';
                    }

                    if ($fieldvalue == "DocType") {
                        $output .= 'S';
                    }

                    if ($fieldvalue == "DocDate") {
                        $output .= $lastday;
                    }

                    if ($fieldvalue == "DocDueDate") {
                        $output .= $lastday;
                    }

                    if ($fieldvalue == "CardCode") {
                        $output .= 'KCS' . $vendorId;
                    }

                    if ($fieldvalue == "TaxDate") {
                        $output .= $lastday;
                    }

                    if ($m < ($numfields - 1)) {
                        $output .= ",";
                    }

                }
                $output .= "\n";

            }
            $lastStatementId = $statementId;
        }
        // Send the CSV file to the browser for download

        header("Content-type: text/x-csv");
        header("Content-Disposition: attachment; filename=$filename.csv");
        echo $output;
        exit;
    }

    //API For GMV-NMV
    public function getGmvMnvAction()
    {   //var_dump($_GET);exit;
        $startDate = $_GET['startdate'];
        $endDate = $_GET['enddate'];
        $result = array('gmv'=>'', 'nmv' => '', 'nmvcod' => '', 'nmvothers' => '');
        $readQuery     = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $sqlnmv = "select
count(DISTINCT sfs.order_id) as TotalShippedOrder,
sum(sfs.base_shipping_amount+sfs.base_total_value) as shippedGMV,
sum(case when (sfs.udropship_status = 1 and sfop.method not in ('cashondelivery','free')) then (sfs.base_shipping_amount+sfs.base_total_value) else 0 end) as MnvPrepaid,
sum(case when (sfs.udropship_status = 7 and sfop.method = 'cashondelivery') then (sfs.base_shipping_amount+sfs.base_total_value) else 0 end) as MnvCOD,
sum(case when (sfs.udropship_status = 6) then (sfs.base_shipping_amount+sfs.base_total_value) else 0 end) as cancelledShipmentGmv,
sum(case when (sfs.udropship_status IN (25,41)) then (sfs.base_shipping_amount+sfs.base_total_value) else 0 end) as rtoGmv,
sum(case when (sfs.udropship_status = 12) then (sfs.base_shipping_amount+sfs.base_total_value) else 0 end) as refuntInitiatedGmv
from sales_flat_shipment sfs
left join sales_flat_order_payment sfop
on sfs.order_id = sfop.parent_id where sfs.created_at BETWEEN '".$startDate." 00:00:01' AND '".$endDate." 23:59:59' AND (sfs.base_shipping_amount+sfs.base_total_value) < 250000";
        $resultNmv       = $readQuery->query($sqlnmv)->fetch();
        $result['totalShippedOrder'] = round(intval($resultNmv['TotalShippedOrder']));
        $result['shippedGMV'] = round(intval($resultNmv['shippedGMV']));
        $result['cancelledShipmentGmv'] = round(intval($resultNmv['cancelledShipmentGmv']));
        $result['rtoGmv'] = round(intval($resultNmv['rtoGmv']));
        $result['refuntInitiatedGmv'] = round(intval($resultNmv['refuntInitiatedGmv']));
        $result['nmvcod'] = round(intval($resultNmv['MnvCOD']));
        $result['nmvothers'] = round(intval($resultNmv['MnvPrepaid']));
        $result['nmv'] = round($resultNmv['MnvCOD'] + $resultNmv['MnvPrepaid']);
        $sqlgmv = "SELECT  sum(base_grand_total) as gmv,count(*) as TotalOrder from sales_flat_order where base_grand_total < 250000 and created_at BETWEEN '".$startDate." 00:00:01' AND '".$endDate." 23:59:59'";
        $resultgmv       = $readQuery->query($sqlgmv)->fetch();
        $result['gmv'] = round(intval($resultgmv['gmv']));
        $result['droppedGmv'] = round(intval($resultgmv['gmv']) - $result['shippedGMV']);
        $result['totalOrder'] = round(intval($resultgmv['TotalOrder']));
        $readQuery->closeConnection();
        echo (json_encode($result));
    }

    //For AWB to Shipment Mapping CSV
    public function awbToShipmentAction()
    {   //var_dump($_POST);exit;
        $data = explode(",", $_POST['param']) ;
        $filename = "awb_shipment";
        $filePathOfCsv = Mage::getBaseDir('media') . DS . $filename . '.csv';
        $fp = fopen($filePathOfCsv, 'w');
        fputcsv($fp, array("AWB Number","Shipment Number"));
        foreach ($data as $key => $value) {
            $readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
            $sql = "select sales_flat_shipment_track.number, sales_flat_shipment.increment_id from sales_flat_shipment left join sales_flat_shipment_track on sales_flat_shipment.entity_id = sales_flat_shipment_track.parent_id where sales_flat_shipment_track.number = '".$value."' ";
            $result = $readQuery->query($sql)->fetch();
            $readQuery->closeConnection();
            if($result){
                //var_dump($result);exit;
                fputcsv($fp, $result);
            }
        }
        fclose($fp);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename . '.csv');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePathOfCsv));
        readfile($filePathOfCsv, false);
        unlink($filePathOfCsv);
    }
}
