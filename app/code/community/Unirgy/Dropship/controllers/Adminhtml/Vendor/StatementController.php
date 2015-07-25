<?php

class Unirgy_Dropship_Adminhtml_Vendor_StatementController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $hlp = Mage::helper('udropship');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb($hlp->__('Vendor Statements'), $hlp->__('Vendor Statements'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();

        $hlp = Mage::helper('udropship');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb($hlp->__('Vendor Statements'), $hlp->__('Vendor Statements'));
        $this->_addBreadcrumb($hlp->__('Generate'), $hlp->__('Generate'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_new'));

        $this->renderLayout();
    }

    public function newPostAction()
    {
		
		if (!$this->getRequest()->isPost()) {
            $this->_redirect('new');
            return;
        }
        $hlp = Mage::helper('udropship');

        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $dateFrom = Mage::app()->getLocale()->date($dateFrom, $dateFormat, null, false)->toString('yyyy-MM-dd');
        $dateTo = Mage::app()->getLocale()->date($dateTo, $dateFormat, null, false)->addDay(1)->toString('yyyy-MM-dd');
		
        if ($this->getRequest()->getParam('all_vendors')) {
            $vendors = Mage::getModel('udropship/vendor')->getCollection()
                ->addFieldToFilter('status', 'A')
                ->getAllIds();
        } else {
			$vendors = $this->getRequest()->getParam('vendor_ids');
        }
        $period = $this->getRequest()->getParam('statement_period');
		if (!$period) {
             
			$period = date('ym', strtotime($dateFrom));
			/*echo '$period'.$period;
			$statementData = Mage::getModel('udropship/vendor_statement')->getCollection();
			//echo $statementId = $statementData[0]['statement_id'];exit;
			echo '<pre>';
			print_r($statementData->getData());exit;
			echo 'st id'.$statementData->addAttributeToSelect('statement_id')
			                           ->addAttributeToSort('statement_id','asc');exit;*/
			$read =  Mage::getSingleton('core/resource')->getConnection('core_read');
			$lastStatement = $read->fetchAll("SELECT `statement_id` FROM `udropship_vendor_statement` ORDER BY `statement_id` DESC LIMIT 0,1");
			$lastStatementId = $lastStatement[0]['statement_id'];
			if($lastStatementId == '')
			{
				$lastStatementId = '13137'; 
				//$lastStatementId = '159'; 
				
			} 				
				
        }

        $n = sizeof($vendors);
        $i = 0;
        ob_implicit_flush();
        echo "<html><body>Generating {$n} vendor statements<hr/>";

        $generator1 = Mage::getModel('udropship/pdf_statement');
		$statementId = $lastStatementId+1;
        foreach ($vendors as $vId) {
			
            echo "Vendor ID {$vId} (".(++$i)."/{$n}): ";
            try {
                $statement = Mage::getModel('udropship/vendor_statement');
				
                if ($statement->load($statementId, 'statement_id')->getId()) {
                    echo "<span style='color:#888'>ALREADY EXISTS</span>.<br/>";
                    continue;
                }
                $statement->addData(array(
                    'vendor_id' => $vId,
                    'order_date_from' => $dateFrom,
                    'order_date_to' => $dateTo,
                    //'statement_id' => "{$vId}-{$period}",
					'statement_id' => $statementId,
                    'statement_date' => now(),
                    'statement_period' => $period,
                    'statement_filename' => "invoice-{$vId}-{$statementId}.pdf",
                    'created_at' => now(),
                ));
			   
                if(is_null($statement->fetchOrders()))
					{
						echo "<span style='color:#0F0'>NOT CREATED</span>.<br/>";
					}
				else
				{
				$statement->save();
				 $generator = Mage::getModel('udropship/pdf_statement')->before();
            	 $statement = Mage::getModel('udropship/vendor_statement');
            
                 $statement = Mage::getModel('udropship/vendor_statement')->load($statement->load($statementId, 'statement_id')->getId());
                if (!$statement->getId()) {
                    continue;
                }
                //$generator->addStatement($statement);
                $generator->addStatementCraftsvilla($statement);
			
			
			$pdf = $generator->getPdf();
			
			if (empty($pdf->pages)) {
                Mage::throwException(Mage::helper('udropship')->__('No statements found to print'));
            }
		//Commented by Dileswar on dated 08-03-2013 for stopping statement order total report

		    //$generator->insertTotalsPage()->after();
			
			
			// To sotore the pdf files in folder
			$statement->getVendorId();
			$pdf = $generator->getPdf();
			$dateMonth = date('M',strtotime($statement->getOrderDateFrom()));
			$dateYear = date('Y',strtotime($statement->getOrderDateFrom()));
			$io = new Varien_Io_File();
        	$io->setAllowCreateFolders(true);
       		$io->open(array('path' => Mage::getBaseDir('media') . DS . 'statementreport/'.$statement->getVendorId()));
			
			//$io->streamOpen('invoice-'.$statement->getVendorId().'-'.$dateMonth.'-'.$dateYear.'.pdf');
			$io->streamOpen('invoice-'.$vId.'-'.$statementId.'.pdf');
			$io->streamWrite($pdf->render());
			$io->streamClose();
			$statementId++;	
            echo "<span style='color:#0F0'>DONE</span>.<br/>";
			}

		 }
			catch (Exception $e) {
                echo "<span style='color:#F00'>ERROR</span>: ".$e->getMessage()."<br/>";
                continue;
            }
            
        }

        $redirectUrl = Mage::helper('adminhtml')->getUrl('udropshipadmin/adminhtml_vendor_statement');
        echo "<hr>".$hlp->__('All done, <a href="%s">click here</a> to be redirected to statements grid.', $redirectUrl);
        exit;
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Statements'), Mage::helper('udropship')->__('Statements'));

        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit'))
            ->_addLeft($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tabs'));

        $this->renderLayout();
    }
    
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = Mage::helper('udropship');
            try {
                if (($id = $this->getRequest()->getParam('id')) > 0
                    && ($statement = Mage::getModel('udropship/vendor_statement')->load($id)) 
                    && $statement->getId()
                ) {
                    $statement->setNotes($this->getRequest()->getParam('notes'));
                    if (($adjArr = $this->getRequest()->getParam('adjustment'))
                        && is_array($adjArr) && is_array($adjArr['amount'])
                    ) {
                        foreach ($adjArr['amount'] as $k => $adjAmount) {
                            if (is_numeric($adjAmount)) {
                                $createdAdj = $statement->createAdjustment($adjAmount)
                                    ->setComment(isset($adjArr['comment'][$k]) ? $adjArr['comment'][$k] : '')
                                    ->setPoType(isset($adjArr['po_type'][$k]) ? $adjArr['po_type'][$k] : null)
                                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                                    ->setPoId(isset($adjArr['po_id'][$k]) ? $adjArr['po_id'][$k] : null);
                                $statement->addAdjustment($createdAdj);
                            }
                        }
                        $statement->finishStatement();
                    }
                     
                    $statement->save();
                    if ($this->getRequest()->getParam('refresh_flag')) {
                        $statement->fetchOrders()->save();
                        Mage::getSingleton('adminhtml/session')->addSuccess($hlp->__('Statement was successfully refreshed'));
                    }
                    if ($this->getRequest()->getParam('pay_flag')) {
                        $this->_redirect('udpayoutadmin/payout/edit', array('id'=>$statement->createPayout()->save()->getId()));
                        return;
                    }
                } else {
                    Mage::throwException($hlp->__("Statement '%s' no longer exists", $id));
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($hlp->__('Statement was successfully saved'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if (($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                $model = Mage::getModel('udropship/vendor_statement');
                $model->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Statement was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
   public function statementRdr1Action()
	{
	/*$year = Mage::app()->getLocale()->date()->get(Zend_date::YEAR);	
	$selectedMonth = $this->getRequest()->getParam('selected_month');
	$statementQuery =  Mage::getSingleton('core/resource')->getConnection('core_read');	
	$selectedMonthData =$statementQuery->fetchAll("SELECT `statement_id`,`increment_id`,`created_at` FROM `sales_flat_shipment` where MONTH(`created_at`) = ".$selectedMonth." AND YEAR(`created_at`) = ".$year." AND `statement_id` IS NOT NULL ORDER BY `statement_id` DESC");
*/	/*echo '<pre>';
	print_r($selectedMonthData);exit;*/
	$selectedMonth = $this->getRequest()->getParam('selected_month');
	$year = Mage::app()->getLocale()->date()->get(Zend_date::YEAR);
		$monthNum = Mage::app()->getLocale()->date()->get(Zend_date::MONTH);
        for ($i = 0; $i < 12; $i++) {
			$_monthNum = ($monthNum+12 -$i) % 12;
			if ($_monthNum == 0) $_monthNum = 12;
			if ($monthNum <= $i)
			{
				$_year = $year - 1;
			}
			else{
				$_year = $year;
			}
          	$_montharray[$i] = $_monthNum;
			$_yeararray[$i] = $_year.'-';
		}

	$statementQuery =  Mage::getSingleton('core/resource')->getConnection('core_read');	
	$selectedMonthData =$statementQuery->fetchAll("SELECT `statement_id`,`increment_id`,`created_at`,`udropship_vendor` FROM `sales_flat_shipment` where MONTH(`created_at`) = '".$_montharray[$selectedMonth]."' AND YEAR(`created_at`) = '".$_yeararray[$selectedMonth]."' AND `statement_id` IS NOT NULL ORDER BY `statement_id` DESC");
	/*echo '<pre>';
	print_r($selectedMonthData);exit;*/

			
				
	//For CSV
	$filename = "RDR1"."-".$selectedMonth."-".$year;
	$output = "";
	$fieldlist1 = array("ParentKey","LineNum","ItemDescription","ShipDate","AccountCode","TaxCode","UnitPrice","LocationCode");
	$fieldlist = array("DocNum","LineNum","Dscription","ShipDate","AcctCode","TaxCode","PriceBefDi","LocCode");
	
	
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
	$lastday = date("Ymd",mktime(0, 0, 0,$_montharray[$selectedMonth]+1,0,$_yeararray[$selectedMonth]));
			foreach($selectedMonthData as $_selectedMonthData)
			{
				$statementId =  $_selectedMonthData['statement_id'];
				$shipmenttId  =  $_selectedMonthData['increment_id'];
				$createdDate  =  $_selectedMonthData['created_at'];
				$date = str_replace('-','',substr($createdDate,0,10));
				//$commissionCsv = str_replace(',','',$this->getCommission($shipmenttId));
				$commissionCsv = str_replace(',','',Mage::getModel('udropship/vendor_statement')->getCommission($shipmenttId));
				
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
							
						if ($m < ($numfields-1))
						{
							$output .= ",";
						}
						
					}
		    	$output .= "\n";
				
				
			}
    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
				
	}
	
	public function getCommission($shipmentId)
	{
		
		$hlp = Mage::helper('udropship');
		
		$po = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
	
		$order = array(
            'id' => $hlp->getPoOrderIncrementId($po),
            //'com_percent' => $po->getCommissionPercent(),
			'com_percent' => 20,//set as static to 20 percentage by dileswar on dated 25-02-2014 for get invoice correct
            'order_id' => $po->getOrderId(),
            'po_id' => $po->getId(),
            'order_created_at' => $hlp->getPoOrderCreatedAt($po),
            'order_increment_id' => $hlp->getPoOrderIncrementId($po),
            'po_increment_id' => $shipmentId,
            'itemised_total_shippingcost' => $po->getItemisedTotalShippingcost(),
			'base_shipping_amount' => $po->getBaseShippingAmount(),
            'udropship_vendor' => $po->getUdropshipVendor(),
			'cod_fee' => $po->getCodFee(),
        	'subtotal' =>  $po->getBaseTotalValue(),
        );
      //print_r($order);exit;
	   $base_shipping_amount = $order['base_shipping_amount'];
	  	$total_amount1 = $order['subtotal'];
			$total_amount = $order['subtotal'];
			$_liveDate = "2012-08-21 00:00:00";
			$_order = Mage::getModel('sales/order')->loadByIncrementId($order['id']);
		   	$orderBaseShippingamount = $_order->getBaseShippingAmount();
		   $orderid = $_order->getEntityId();
		   $discountAmountCoupon = 0;
		   $disCouponcode = '';
			$_orderCurrencyCode = $_order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($order['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $order['subtotal']/1.5;
               $vendorId = $order['udropship_vendor'];
			   $readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
			$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$orderid."' AND `address_type` = 'shipping'")->fetch();
				$getCountryResult = $getCountryOf['country_id'];
				$lastFinalbaseshipamt = $this->baseShippngAmountByOrder($orderid,$orderBaseShippingamount);
		    	$commission_amount = $order['com_percent'];
		    	$itemised_total_shippingcost = $order['itemised_total_shippingcost'];
				
		    	$vendors = Mage::helper('udropship')->getVendor($order['udropship_vendor']);
		    	$couponCodeId = Mage::getModel('salesrule/coupon')->load($_order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $_order->getBaseDiscountAmount();
					$disCouponcode = $_order->getCouponCode();
				}
		    		
		    	//$gen_random_number = "K".$this->gen_rand();

    			if($order['order_created_at']<='2012-07-02 23:59:59')
		    	{
		    		if($vendors->getManageShipping() == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
						$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$order['cod_fee'];
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
						$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
			    	}
		    	}
		    	else {
		    		if($vendors->getManageShipping() == "imanage")
			    	{
						
			    		$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$order['cod_fee'])*1.00 - $vendor_amount;
			    	}
			    	else {
						
						$vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
				    	// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						if($getCountryResult == 'IN')
						{	
							$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)*1.00) - $vendor_amount);
						}
						else
						{
							$kribha_amount = ((($total_amount1+$lastFinalbaseshipamt+$discountAmountCoupon)*1.00) - $vendor_amount);
						}
						//$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////
				 		//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost+$order['cod_fee'])*1.00) - $vendor_amount);
						//$kribha_amount = ((($total_amount1+$base_shipping_amount+$order['cod_fee'])*1.00) - $vendor_amount);
			    		
					}
		    	}
					if (is_null($order['com_percent'])) {
						$order['com_percent'] = $this->getVendor()->getCommissionPercent();
					}
					
					$order['com_percent'] *= 1;

					
					$order['com_amount'] = $kribha_amount/1.1236;
					
					// $order['amounts']['total_payout'] = $vendor_amount;
					
					return number_format($order['com_amount'],2);
		
	} 
	
	public function baseShippngAmountByOrder($orderId,$orderBaseShippingamount)
		{
		$readOrder = Mage::getSingleton('core/resource')->getConnection('core_read');
		//echo $query = "select count(*) from `sales_flat_shipment`  where  sfs.`order_id` = '".$orderId."'";exit;
		$adjustquery = $readOrder->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
		$shipcnt = $adjustquery['cntshipments'];
		$lastFinalshipamt = $orderBaseShippingamount/$shipcnt;
		return $lastFinalshipamt;
		}
	
	public function statementOrdrAction()
	{
		
		$selectedMonth = $this->getRequest()->getParam('selected_month');
	
		$year = Mage::app()->getLocale()->date()->get(Zend_date::YEAR);
		$monthNum = Mage::app()->getLocale()->date()->get(Zend_date::MONTH);
        for ($i = 0; $i < 12; $i++) {
			$_monthNum = ($monthNum+12 -$i) % 12;
			if ($_monthNum == 0) $_monthNum = 12;
			if ($monthNum <= $i)
			{
				$_year = $year - 1;
			}
			else{
				$_year = $year;
			}
          	$_montharray[$i] = $_monthNum;
			$_yeararray[$i] = $_year.'-';
		}
		
		
	$statementQuery =  Mage::getSingleton('core/resource')->getConnection('core_read');	
	$selectedMonthData =$statementQuery->fetchAll("SELECT `statement_id`,`increment_id`,`created_at`,`udropship_vendor` FROM `sales_flat_shipment` where MONTH(`created_at`) = '".$_montharray[$selectedMonth]."' AND YEAR(`created_at`) = '".$_yeararray[$selectedMonth]."' AND `statement_id` IS NOT NULL ORDER BY `statement_id` DESC");
	/*echo '<pre>';
	print_r($selectedMonthData);exit;*/
	
			
				
	//For CSV
	$filename = "ORDR"."-".$selectedMonth."-".$year;
	$output = "";
	$fieldlist1 = array("DocNum","HandWritten","DocType","DocDate","DocDueDate","CardCode","TaxDate");
	$fieldlist = array("DocNum","HandWrtten","DocType","DocDate","DocDueDate","CardCode","TaxDate");
	
	
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
	
	$lastday = date("Ymd",mktime(0, 0, 0,$_montharray[$selectedMonth]+1,0,$_yeararray[$selectedMonth]));
	
			
			foreach($selectedMonthData as $_selectedMonthData)
			{
					
				$statementId =  $_selectedMonthData['statement_id'];
				$shipmenttId  =  $_selectedMonthData['increment_id'];
				$createdDate  =  $_selectedMonthData['created_at'];
				$vendorId	= 	$_selectedMonthData['udropship_vendor'];
				$date = str_replace('-','',substr($createdDate,0,10));
				
				if($lastStatementId != $statementId) {
		
				for($m =0; $m < sizeof($fieldlist); $m++) 
				{
						$fieldvalue = $fieldlist[$m];
						if($fieldvalue == "DocNum")
						{
							$output .= $statementId;
						}
						
						if($fieldvalue == "HandWritten")
						{
							$output .= 'tYES';
						}
						
						if($fieldvalue == "DocType")
						{
							$output .= 'S';
						}
						
						if($fieldvalue == "DocDate")
						{
							$output .= $lastday;
						}
							
						if($fieldvalue == "DocDueDate")
						{
							$output .= $lastday;
						}
							
						if($fieldvalue == "CardCode")
						{
							$output .= 'KCS'.$vendorId;
						}
							
						if($fieldvalue == "TaxDate")
						{
							$output .= $lastday;
						}
							
						if ($m < ($numfields-1))
						{
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
	
    public function payoutGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_payouts', 'admin.statement.payouts')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
    
    public function rowGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_rows', 'admin.statement.rows')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
    
    public function adjustmentGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_adjustments', 'admin.statement.adjustments')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')->toHtml()
        );
    }

    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'vendors.csv';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'vendors.xml';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError($this->__('Please select statement(s)'));
        }
        else {
            try {
                $obj = Mage::getSingleton('udropship/vendor_statement');
                foreach ($objIds as $objId) {
                    Mage::getModel('udropship/vendor_statement')->load($objId)->setId($objId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($objIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massRefreshAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError($this->__('Please select statement(s)'));
        }
        else {
            try {
                foreach ($objIds as $objId) {
                    $st = Mage::getModel('udropship/vendor_statement')->load($objId);
                    if ($st->getId()) {
                        $st->fetchOrders()->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully refreshed', count($objIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDownloadAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
       	if (!is_array($objIds)) {
            $this->_getSession()->addError($this->__('Please select statement(s)'));
        }
        try {
            $generator = Mage::getModel('udropship/pdf_statement')->before();
            $statement = Mage::getModel('udropship/vendor_statement');
            foreach ($objIds as $id) {
                $statement = Mage::getModel('udropship/vendor_statement')->load($id);
                if (!$statement->getId()) {
                    continue;
                }
                //$generator->addStatement($statement);
                $generator->addStatementCraftsvilla($statement);
            }
			
			$pdf = $generator->getPdf();
			
			if (empty($pdf->pages)) {
                Mage::throwException(Mage::helper('udropship')->__('No statements found to print'));
            }
            
			//Commented by Dileswar on dated 08-03-2013 for stopping statement order total report
			
			//$generator->insertTotalsPage()->after();
			Mage::helper('udropship')->sendDownload('statements.pdf', $pdf->render(), 'application/x-pdf');
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while download vendor statement(s): %s', $e->getMessage()));
        }

        $this->_redirect('*/*/');
    }

    public function massEmailAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError($this->__('Please select statement(s)'));
        }
        try {
            $statement = Mage::getModel('udropship/vendor_statement');
            foreach ($objIds as $id) {
                $statement = Mage::getModel('udropship/vendor_statement')->load($id);
				$statement->send();
            }
            Mage::helper('udropship')->processQueue();
            $this->_getSession()->addSuccess(
                $this->__('Total of %d statement(s) have been sent', count($objIds))
            );

        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while downloading vendor statement(s): %s', $e->getMessage()));
        }

        $this->_redirect('*/*/');
    }
}
