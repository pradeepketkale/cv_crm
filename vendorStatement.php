<?php
require_once 'app/Mage.php';
Mage::app();
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$vendorQuery = "SELECT `vendor_id`,`vendor_name` FROM `vendor_info_craftsvilla`";
$vendors = $readcon->query($vendorQuery)->fetchAll();
//echo '<pre>'; print_R($vendors); exit;
$totalVendors = sizeof($vendors);
$vendorCount = 0;
$dateFrom = '2015-06-01 00:00:01';   
$dateTo = '2015-06-30 23:59:59';



foreach($vendors as $_vendors) {

$vendorId = $_vendors['vendor_id'];
$vendorName = $_vendors['vendor_name'];

$lastStatement = $readcon->fetchAll("SELECT `statement_id` FROM `udropship_vendor_statement` ORDER BY `statement_id` DESC LIMIT 0,1"); 
			
			$lastStatementId = $lastStatement[0]['statement_id']; 
			if($lastStatementId == '')
			{
				$lastStatementId = '13137'; 
				
			}
			
			$generator1 = Mage::getModel('udropship/pdf_statement');
		    $statementId = $lastStatementId+1;
		    $period = date('ym', strtotime($dateTo)); 
			
			    try {
			    
			    $duplicateVendor = $readcon->fetchAll("SELECT * FROM `udropship_vendor_statement` WHERE `vendor_id` = '".$vendorId."' AND `statement_period` = '".$period."'"); 

				if(!$duplicateVendor) {
				
				$statement = Mage::getModel('udropship/vendor_statement');
				
                if(!($statement->load($statementId, 'statement_id')->getId())) {
                
                $statement->addData(array(
                    'vendor_id' => $vendorId,
                    'order_date_from' => $dateFrom,
                    'order_date_to' => $dateTo,
                    'statement_id' => $statementId,
                    'statement_date' => $dateTo,
                    'statement_period' => $period,
                    'statement_filename' => "invoice-{$vendorId}-{$statementId}.pdf",
                    //'invoice-'.date('M',strtotime($dateFrom)).'-'.date('Y',strtotime($dateFrom)).'-'.$vendorId.'-'.$statementId.'.pdf',
                    
                    'created_at' => now(),
                ));
			   
			     $statement->save();
				 
				 $generator = Mage::getModel('udropship/pdf_statement')->before();
            	 $statement = Mage::getModel('udropship/vendor_statement')->load($statement->load($statementId, 'statement_id')->getId());
                 
                if (!$statement->getId()) {
                    continue;
                }
                 
                 $stmtId = $statement->getStatementId(); 
                 
                 $orderFrom = $statement->getOrderDateFrom(); 
        		 $orderTo = $statement->getOrderDateTo(); 
        		 $orderVendor = $statement->getVendorId(); 
        		 
                $statementQuery = "SELECT `t`.entity_id, `t`.increment_id FROM `sales_flat_shipment_grid` AS `main_table` INNER JOIN `sales_flat_shipment` AS `t` ON t.entity_id=main_table.entity_id INNER JOIN `sales_flat_order_payment` AS `b` ON b.parent_id=main_table.order_id WHERE ((t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')) AND (t.udropship_vendor='".$orderVendor."') AND (t.created_at IS NOT NULL) AND (t.created_at!='0000-00-00 00:00:00') AND (t.created_at>='".$orderFrom."') AND (t.created_at<='".$orderTo."') ORDER BY `main_table`.`entity_id` asc";
          $readCon = Mage::getSingleton('core/resource')->getConnection('core_read');
          $writeCon = Mage::getSingleton('core/resource')->getConnection('core_write');
          
          $statementQueryRes = $readCon->query($statementQuery)->fetchAll();      
             
            foreach($statementQueryRes as $_statementQueryRes) {
            $incId = $_statementQueryRes['increment_id']; 
            
            $sfsStatementUpdate = "UPDATE `sales_flat_shipment` SET `statement_id`='".$stmtId."' WHERE `increment_id` = '".$incId."'";
			$writeCon->query($sfsStatementUpdate);
			$sfsgridStatementUpdate = "UPDATE `sales_flat_shipment_grid` SET `statement_id`='".$stmtId."' WHERE `increment_id` = '".$incId."'";
			$writeCon->query($sfsgridStatementUpdate); 
            
            }
            
             $writeCon->closeConnection();
             $readCon->closeConnection();
             
            
			$generator->addStatementCraftsvilla($statement);
			
			$pdf = $generator->getPdf();
			
			if (empty($pdf->pages)) { 
                Mage::throwException(Mage::helper('udropship')->__('No statements found to print'));
            }
		    
			// To sotore the pdf files in folder
			$pdf = $generator->getPdf();
			$dateMonth = date('M',strtotime($statement->getOrderDateFrom()));
			$dateYear = date('Y',strtotime($statement->getOrderDateFrom()));
			$io = new Varien_Io_File();
        	$io->setAllowCreateFolders(true);
       		$io->open(array('path' => Mage::getBaseDir('media') . DS . 'statementreport/'.$statement->getVendorId()));
			
			//$io->streamOpen('invoice-'.date('M',strtotime($dateFrom)).'-'.date('Y',strtotime($dateFrom)).'-'.$vendorId.'-'.$statementId.'.pdf');
			$io->streamOpen("invoice-{$vendorId}-{$statementId}.pdf");
			
			$io->streamWrite($pdf->render());
			$io->streamClose();
			$statementId++;	
            echo "<span style='color:#0F0'>DONE</span>.<br/>";
			
			}
			
			} 
		 }
			catch (Exception $e) {
                echo "<span style='color:#F00'>ERROR</span>: ".$e->getMessage()."<br/>";
                continue;
            }
            echo 'first vendor done'; exit; 
  
}
//echo "<span style='color:#0F0'>DONE.......</span>.<br/>";
echo 'done'; exit;

?>
