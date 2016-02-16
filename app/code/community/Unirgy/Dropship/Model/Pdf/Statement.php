<?php

class Unirgy_Dropship_Model_Pdf_Statement extends Unirgy_Dropship_Model_Pdf_Abstract
{
    protected $_curPageNum;
    protected $_pageFooter = array();
    protected $_globalTotals = array();
    protected $_globalTotalsAmount = array();
    protected $_totalsPageNum = 0;

    public function before()
    {
        Mage::getSingleton('core/translate')->setTranslateInline(false);

        $pdf = new Zend_Pdf();
        $this->setPdf($pdf);

        return $this;
    }

    public function isInPayoutAmount($amountType, $inPayoutOption, $vId=null)
    {
    	if (is_null($vId)) {
    		$vendor = $this->getStatement()->getVendor();
    	} else {
    		$vendor = Mage::helper('udropship')->getVendor($vId);
    	}
    	$inPayoutOption = $inPayoutOption == 'include' ? array('', 'include') : array($inPayoutOption);
    	$hideTax = in_array($vendor->getData('statement_tax_in_payout'), $inPayoutOption);
    	$hideShipping = in_array($vendor->getData('statement_shipping_in_payout'), $inPayoutOption);
    	$hideBoth = $hideTax && $hideShipping;
    	switch ($amountType) {
    		case 'all':
    			return $hideBoth;
    		case 'shipping':
    			return $hideShipping;
    		case 'tax':
    			return $hideTax;
    	}
    }
    
    
    public function addStatementCraftsvilla($statement)
    {
    	$hlp = Mage::helper('udropship');
        
        $this->setStatement($statement); 
        
        $orderFrom = $statement->getOrderDateFrom();
        $orderTo = $statement->getOrderDateTo(); 
        $orderVendor = $statement->getVendorId(); 
        $orderStatementId = $statement->getStatementId(); 
        
        $ordersData = array();
        
       
    $statementQuery = "SELECT `t`.entity_id, `t`.increment_id FROM `sales_flat_shipment_grid` AS `main_table` INNER JOIN `sales_flat_shipment` AS `t` ON t.entity_id=main_table.entity_id INNER JOIN `sales_flat_order_payment` AS `b` ON b.parent_id=main_table.order_id WHERE ((t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')) AND (t.udropship_vendor='".$orderVendor."') AND (t.created_at IS NOT NULL) AND (t.created_at!='0000-00-00 00:00:00') AND (t.created_at>='".$orderFrom."') AND (t.created_at<='".$orderTo."') AND ((main_table.statement_id='".$orderStatementId."' OR main_table.statement_id IS NULL OR main_table.statement_id='')) ORDER BY `main_table`.`entity_id` asc";
    //echo $statementQuery = "SELECT `t`.entity_id, `t`.increment_id FROM `sales_flat_shipment_grid` AS `main_table` INNER JOIN `sales_flat_shipment` AS `t` ON t.entity_id=main_table.entity_id INNER JOIN `sales_flat_order_payment` AS `b` ON b.parent_id=main_table.order_id WHERE ((t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')) AND (t.udropship_vendor='".$vendorId."') AND (t.updated_at IS NOT NULL) AND (t.updated_at!='0000-00-00 00:00:00') AND (t.updated_at>='".$dateFrom."') AND (t.updated_at<='".$dateTo."') ORDER BY `main_table`.`entity_id` asc";

    $readCon = Mage::getSingleton('core/resource')->getConnection('core_read');
    $writeCon = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $statementQueryRes = $readCon->query($statementQuery)->fetchAll();
        
        foreach($statementQueryRes as $_po)
        {
		    
		    $po = Mage::getModel('sales/order_shipment')->load($_po['entity_id']);
		    //echo '<pre>'; print_r($po); exit; 
	       	$actualServiceTax = $this->getServicetaxCv($po->getUpdatedAt()); 
		    $shipmentId = $_po['increment_id']; 
		    //echo '<pre>'; print_r($_po); exit;
		    $order = array(
		        'po_id' => $po->getId(),
		        'date' => $hlp->getPoOrderCreatedAt($po),
		        'id' => $hlp->getPoOrderIncrementId($po),
		        'com_percent' => '20',
				'adjustments' => $po->getAdjustments(),
		        'order_id' => $po->getOrderId(),
		        'po_id' => $po->getId(),
		        'order_created_at' => $hlp->getPoOrderCreatedAt($po),
		        'order_increment_id' => $hlp->getPoOrderIncrementId($po),
		        'po_increment_id' => $shipmentId,
		        'po_created_at' => $po->getCreatedAt(),
                'po_updated_at' => $po->getUpdatedAt(),
		        'base_shipping_amount' => $po->getBaseShippingAmount(),
				'itemised_total_shippingcost' => $po->getItemisedTotalShippingcost(),
		        'udropship_vendor' => $po->getUdropshipVendor (),
		        'po_statement_date' => $po->getStatementDate(),
				'cod_fee' => $codFee,
				'po_type' => $po instanceof Unirgy_DropshipPo_Model_Po ? 'po' : 'shipment'
		    	);
				
				$order['amounts'] = array_merge($this->_getEmptyTotals(), array(
				'subtotal' =>  $po->getBaseTotalValue(),
			    'shipping' => $po->getShippingAmount(),
			    'tax' => $po->getBaseTaxAmount(),
			    'handling' => $po->getBaseHandlingFee(),
				'trans_fee' => $po->getTransactionFee(),
			    'adj_amount' => $po->getAdjustmentAmount(),
        	));
				 
				 $order1 = $this->calculateOrder($order);
				 
      		 $ordersData[]['orders']=$order1;
				 
  			}
  
  		    $this->_curPageNum = 0;
		    $this->addPage()->insertPageHeader(array('first'=>true, 'data'=>$ordersData));
		
			$TotalCommission = 0;
			$TotalServiceTax = 0;
			$TotalSubTotal = 0;
			$totalOrdersCount = 0;
			
		    if (!empty($ordersData)) 
		    { 
		        // iterate through orders
		        foreach ($ordersData as $order) 
		        {
				    
				    $stmtId = $statement->getStatementId(); 
				    $incId = $order['orders']['po_increment_id'];
				    $totalOrdersCount++;
				    $this->insertOrder($order);
				    $TotalSubTotal += number_format($order['orders']['amounts']['subtotal'],2);
				    $TotalCommission += number_format($order['orders']['amounts']['com_amount'],2);
					$TotalServiceTax += number_format(($order['orders']['amounts']['com_amount']*$actualServiceTax),2);
					
				}
		    
		    } else { 
		        $this->text($hlp->__('No orders found for this period.'), 'down')
		            ->moveRel(0, .5);
		    }
		    
		    $order['orders']['amounts']['subtotal'] = $TotalSubTotal;
		    $order['orders']['amounts']['com_amount'] = $TotalCommission;
		    $order['orders']['amounts']['tax'] = $TotalServiceTax;
		    $Totalamount = number_format($TotalCommission+$TotalServiceTax,0);
		    $order['orders']['amounts']['total_payout'] =  $Totalamount;
		    $this->setAlign('left')->font('normal', 10);
		    $this->insertTotals($order); 
		    
		    $statement->setSubtotal($TotalSubTotal); 
		    $statement->setComAmount($TotalCommission); 
		    $statement->settax($TotalServiceTax);
		    $statement->setTotalOrders($totalOrdersCount);
		    $statement->setTotalPayout($Totalamount);
		    $statement->save();
		    $writeCon->closeConnection();
             $readCon->closeConnection();
		    
		    $this->insertAdjustmentsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
		    if ($hlp->isUdpayoutActive()) {
		    	$this->insertPayoutsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
		    }
		    
		    $this->setAlign('left')->font('normal', 10);
		    foreach ($this->_pageFooter as $k=>&$p) 
		    {
		        if (!empty($p['done'])) {
		            continue;
		        }
		        $p['done'] = true;
		        
			
				$str = $hlp->__('%s for %s - Page %s of %s',
		            $statement->getVendor()->getVendorName(),
		            $statement->getStatementPeriod(),
		            $p['page_num'],
		            $this->_curPageNum
		        );
		        $this->setPage($this->getPdf()->pages[$k])->move(.5, 10.6)->text($str);
		    }
		    
			unset($p);
		    return $this;
		
    
    }
    public function getServicetaxCv($updatedDate)
    {
        if($updatedDate >= '2015-11-15 23:59:59')
           { 
            $exServicetax = (14.5/100);
            }
         else{

            $exServicetax = (14/100);
         }  
         return  $exServicetax;
    }
    
    public function addStatement($statement)
    {
        $hlp = Mage::helper('udropship');
        $this->setStatement($statement);

        $ordersData = Zend_Json::decode($statement->getOrdersData());
        
        // first front page header
        $this->_curPageNum = 0;
        $this->addPage()->insertPageHeader(array('first'=>true, 'data'=>$ordersData));

        if (!empty($ordersData['orders'])) {
            // iterate through orders
            foreach ($ordersData['orders'] as $order) {
                $this->insertOrder($order);
            }
        } else {
            $this->text($hlp->__('No orders found for this period.'), 'down')
                ->moveRel(0, .5);
        }

        $this->insertTotals($ordersData['totals']);
        /*$this->rectangle(40,60,400,40);
        
        $this->y=45;
        $this->line(25,$this->y+10,570,$this->y+10);
			$this->line(180,84,180,45);
			$this->line(570,140,570,45);
                $this->font('bold');
			$this->text(Mage::helper('sales')->__('VAT Reg.No. 27250622017V'), 185, 77, 'UTF-8');
			$this->text(Mage::helper('sales')->__('C.S.T. Reg.No. 27250622017C'), 185, 67, 'UTF-8');
			$this->line(180,65,380,65);
			$this->font('bold');
			$this->text(Mage::helper('sales')->__('Created By'), 190, 47, 'UTF-8');
            $this->text(Mage::helper('sales')->__('Checked By'), 335, 47, 'UTF-8');
            $this->line(380,84,380,45);
            $this->text(Mage::helper('sales')->__('For FINGERPRINTS FASHIONS PVT.LTD'), 385, 77, 'UTF-8');
            $this->text(Mage::helper('sales')->__('Authorised Signatory'), 385, 47, 'UTF-8');
        */
        $this->insertAdjustmentsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
        if ($hlp->isUdpayoutActive()) {
        	$this->insertPayoutsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
        }

        $this->setAlign('left')->font('normal', 10);
        foreach ($this->_pageFooter as $k=>&$p) {
            if (!empty($p['done'])) {
                continue;
            }
            $p['done'] = true;
            
			
			$str = $hlp->__('%s for %s - Page %s of %s',
                $statement->getVendor()->getVendorName(),
                $statement->getStatementPeriod(),
                $p['page_num'],
                $this->_curPageNum
            );
            $this->setPage($this->getPdf()->pages[$k])->move(.5, 10.6)->text($str);
        }
        
		unset($p);
        #$this->font('normal', 10)->setAlign('right')->addPageNumbers(8.25, .25);

        if (($vId = $statement->getVendor()->getId())) {
            $totals = $ordersData['totals'];
            $totals['vendor_name'] = $statement->getVendor()->getVendorName();
            $this->_globalTotals[$vId] = $totals;
            $this->_globalTotalsAmount[$vId] = isset($ordersData['totals_amount']) ? $ordersData['totals_amount'] : $ordersData['totals'];
        }
        
        return $this;
    }

    public function after()
    {
        Mage::getSingleton('core/translate')->setTranslateInline(true);
        return $this;
    }
    
    protected function _insertPageHeader($params=array())
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        // letterhead info
        $this->move(4.25, 0.5)
            ->font('bold', 16)->setAlign('center')
			//->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));
			//->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));
			
			//$this->move(4.25, 0.35)
			//->font('normal', 20)->setAlign('center')


            ->text('Craftsvilla Handicrafts Pvt Ltd');
            
			
			$this->move(4.25, 0.75)
            ->font('normal', 10)->setAlign('center')	
			->text('1502 G Wing, 15th Floor, Lotus Corporate Park, Goregaon (East), Mumbai - 400063, Maharashtra');	
			
			$this->move(1,1)->line(6);
			
			$this->move(4.25, 1.1)
            ->font('bold', 12)->setAlign('center')
			->text('Service Invoice');		
		

		//$this->rectangle(0.75,0.95,0.75,0.95);
        // only for first page
        
		if (!empty($params['first'])) {
			$this->move(1.1, 1.6)->font('bold',10)->text('To');            
			$statement = $this->getStatement();
			
			$vendor = $statement->getVendor(); 
			$vendor1 = Zend_Json::decode($statement->getVendor()->getData('custom_vars_combined'));   
			$this->setAlign('left')->move(1.1, 1.8)->font('normal',9)->text($vendor1['check_pay_to']);
			// vendor info
            $this->setAlign('left')->move(1.1,2)->font('normal', 9)
                ->text($vendor->getBillingInfo());
            // statement info
            $stInfoHeight = $this->getTextHeight()*2;
            if ($hlp->isUdpoActive()) {
                $stInfoHeight += $this->getTextHeight();
            }
            $stTotalHeight = $this->getTextHeight()*6;
            if ($hlp->isUdpayoutActive()) {
                $stTotalHeight += $this->getTextHeight()*2;
            }
            $this->setAlign('right')
                ->move(6, 2)
                    ->text($hlp->__("Invoice #"), 'down')
                    ->text($hlp->__("Invoice Date"), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text($hlp->__("PO Type"), 'down');
            }
		
            $this->move(7.9, 2)
                    ->text($statement->getStatementId(), 'down')
                    ->text($core->formatDate(date('Y-m-d',strtotime($statement->getOrderDateTo().'+10 days')), 'medium'), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text(Mage::getSingleton('udropship/source')->setPath('statement_po_type')->getOptionLabel($statement->getPoType()), 'down');
            }
            $stTotalRectMargin = $this->getTextHeight()*.4;
            $stTotalRectPad = $this->getTextHeight()*.3;
            $stTotalRectY = 2+$stInfoHeight+$stTotalRectMargin;
            $stTotalTxtY = 2+$stInfoHeight+$stTotalRectMargin+$stTotalRectPad;
            $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
            // statement total
            /* commented by mandar ******/ /*
            $this->move(4.5, $stTotalRectY)
                ->rectangle(3.5, $stTotalHeightOut, .8, .8)
                ->font('bold')
                ->move(6, $stTotalTxtY)
                    ->text($hlp->__("Total Payment"), 'down');
             */
             
            if ($hlp->isUdpayoutActive()) {
                $this->text($hlp->__("Total Paid"), 'down')
                    ->text($hlp->__("Total Due"), 'down');
            }
            /*$this->move(7.9, $stTotalTxtY)
                    ->text($params['data']['totals']['total_payout'], 'down');*/
            if ($hlp->isUdpayoutActive()) {
                $this->text($params['data']['totals']['total_paid'], 'down')
                    ->text($params['data']['totals']['total_due'], 'down');
            }
			
			$this->move(.5, $stTotalRectY+$stTotalHeightOut+$stTotalRectMargin);
        }
		else
			{
				$this->move(0.5, 1.8);
			}
		
        return $this;
    }

    public function insertPageHeader($params=array())
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertGridHeader()
    {
    	$hideTax = $this->getStatement()->getVendor()->getData('statement_tax_in_payout') == 'exclude_hide';
    	$hideShipping = $this->getStatement()->getVendor()->getData('statement_shipping_in_payout') == 'exclude_hide';
    	$hideBoth = $hideTax && $hideShipping;
        $hlp = Mage::helper('udropship');
        $this->rectangle(7.5, .4, .8, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text($hlp->__("Date"));
        if ($this->isInPayoutAmount('all', 'exclude_hide')) {  
            $this->moveRel(2.2, 0)->text($hlp->__("Shipment#"))
            	//->moveRel(1.6, 0)->text($hlp->__("Product"))
				->moveRel(2.6, 0)->text($hlp->__("Commission"))
                ->moveRel(2.5, 0)->text($hlp->__("Service Tax"))
                ->moveRel(2.6, 0)->text($hlp->__("Total"))
            ->movePop(0, .4);
        } else { 
        	$this->moveRel(1.2, 0)->text($hlp->__("Shipment#"))
            	//->moveRel(1, 0)->text($hlp->__("Product"));
				//->moveRel(1, 0)->text($hlp->__("Commission"));
              ->moveRel(1.6, 0)->text($hlp->__("Commission"));
			//$this->moveRel(1, 0)->text($hlp->__("Service Tax"));
			//if ($this->isInPayoutAmount('tax', 'exclude_hide')) {
              //  $this->moveRel(1, 0)->text($hlp->__("Service Tax"));
            //} //elseif ($this->isInPayoutAmount('shipping', 'exclude_hide')) {
            	//$this->moveRel(1, 0)->text($hlp->__("Tax"));
            //}
			 //else {
                //if(Mage::getSingleton("udropship/session")->getImanage() == 1):
                    $this->moveRel(1.5, 0)->text($hlp->__("Service Tax"));
                //endif;
            //}
            
            $this->moveRel(1.6, 0)->text($hlp->__("Total"))
            ->movePop(0,.4);        	;
        }
        
        return $this;
    }
    
    public function insertAdjustmentsPageHeader($params=array())
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertAdjustmentsGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertAdjustmentsGridHeader()
    {
        $hlp = Mage::helper('udropship');
        $this->movePush()->moveRel(3.5)
            ->font('bold', 16)->setAlign('center')->text('Extra Adjustments')
            ->movePop(0, .5);
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text($hlp->__("Adjustment#"))
                ->moveRel(1.2, 0)->text($hlp->__("PO ID"))
                ->moveRel(.8, 0)->text($hlp->__("PO Type"))
                ->moveRel(.8, 0)->text($hlp->__("Amount"))
                ->moveRel(.8, 0)->text($hlp->__("Username"))
                ->moveRel(.8, 0)->text($hlp->__("Comment"))
                ->moveRel(2.5, 0)->text($hlp->__("Date"))
            ->movePop(0, .4)
        ;

        return $this;
    }
    
    public function insertPayoutsPageHeader($params=array())
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertPayoutsGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertPayoutsGridHeader()
    {
        $hlp = Mage::helper('udropship');
        $this->movePush()->moveRel(3.5)
            ->font('bold', 16)->setAlign('center')->text('Payouts')
            ->movePop(0, .5);
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text($hlp->__("ID"))
                ->moveRel(.8, 0)->text($hlp->__("Type"))
                ->moveRel(.8, 0)->text($hlp->__("Method"))
                ->moveRel(.8, 0)->text($hlp->__("Status"))
                ->moveRel(.6, 0)->text($hlp->__("# of Orders"))
                ->moveRel(1, 0)->text($hlp->__("Payout"))
                ->moveRel(.8, 0)->text($hlp->__("Paid"))
                ->moveRel(.8, 0)->text($hlp->__("Due"))
                ->moveRel(.8, 0)->text($hlp->__("Date"))
            ->movePop(0, .4)
        ;

        return $this;
    }

    public function insertOrder($order)
    {

        //echo '<pre>';print_r($order);exit;
        $core = Mage::helper('core');

        foreach (array('trans_fee','com_percent','com_amount') as $_k) {
            $order[$_k] = strpos($order[$_k], '-') === 0
                ? substr($order[$_k], 1)
                : '-'.$order[$_k];
 
        }
        $actualServiceTax = $this->getServicetaxCv($order['orders']['po_updated_at']); 
        $this->checkPageOverflow()
            ->setMaxHeight(0)
            ->font('normal', 10)
            ->movePush()
                ->setAlign('left')
                    ->text($core->formatDate($core->formatDate($order['orders']['po_created_at']), 'short'));
			
		if ($this->isInPayoutAmount('all', 'exclude_hide')) {
			$this->moveRel(1.2, 0)->text($order['po_increment_id'])
                ->moveRel(1.6, 0)->text($order['subtotal'])
                ->moveRel(1.5, 0)->text("{$order['com_amount']} ({$order['com_percent']}%) / {$order['trans_fee']}")
            	->setAlign('right')
                ->moveRel(3, 0)->text($order['total_payout']); 
		} else {

            $this->moveRel(1.2, 0)->text($order['orders']['po_increment_id']); 
                //->moveRel(1, 0)->text($order['subtotal']);
			$serviceTax = "Rs. ".number_format($order['orders']['amounts']['com_amount']*$actualServiceTax,2); 
			$comAmount = "Rs. ".number_format($order['orders']['amounts']['com_amount'],2);
			$this->moveRel(1.5, 0)->text("{$comAmount}");
            $this->moveRel(1.7, 0)->text("{$serviceTax}");

			$totalAmount =  "Rs. ".number_format($order['orders']['amounts']['com_amount']*(1+$actualServiceTax),2);
			$this->moveRel(1.6, 0)->text("{$totalAmount}");
			
			//$this->moveRel(1, 0)->text($serviceTax)
                
			/*if ($this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->moveRel(1, 0)->text("{$order['shipping']}");
            } elseif ($this->isInPayoutAmount('shipping', 'exclude_hide')) {
            	$this->moveRel(1, 0)->text("{$order['tax']}");
            } else {
                $vendorData = Mage::helper('udropship')->getVendor($order['udropship_vendor']);
                if($vendorData->getManageShipping() != 'imanage'):
                    //Mage::getSingleton("udropship/session")->setImanage(1);
                    $itemisedTotal = "Rs. ".round($order['itemised_total_shippingcost'],0);
                    $this->moveRel(1, 0)->text("{$itemisedTotal}");
                endif;
            }*/
            
		}
		$this->movePop(0, $this->getMaxHeight()+5, 'point')
            ->moveRel(-.1, 0)
            ->line(7.5, 0, .7)
            ->moveRel(.1, .1)
        	;

        if (!empty($order['adjustments'])) {
            foreach ($order['adjustments'] as $adj) {
                $this->checkPageOverflow()
                    ->setMaxHeight(0)
                    ->font('normal', 10)
                    ->movePush()
                        ->setAlign('left')
                            ->text($core->formatDate($order['po_created_at'], 'short'))
                            ->moveRel(.8, 0)->text($order['po_increment_id'])
                            ->moveRel(1, 0)->text($adj['comment'], null, 70)
                        ->setAlign('right')
                            ->moveRel(5.5, 0)->price($adj['amount'])
                    ->movePop(0, $this->getMaxHeight()+5, 'point')
                    ->moveRel(-.1, 0)
                    ->line(7.5, 0, .7)
                    ->moveRel(.1, .1)
                ;
            }
        }

        return $this;
    }

    public function insertTotals($totals)
    {
       /* $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');

        /*foreach (array('trans_fee','com_amount') as $_k) {
            $totals[$_k] = strpos($totals[$_k], '-') === 0
                ? substr($totals[$_k], 1)
                : '-'.$totals[$_k];
        }*/
        /************** Added By Mandar on 18-05-2012*****************/
       /* $data                   = explode('Rs. ',$totals['com_amount']);
        $data2                  = explode('Rs. ',$totals['total_payout']);
        $service_tax            = round(str_replace(",", "", $data[1])*12.36/100,2);
		//$education_tax          = round($service_tax*2/100,2);
        //$highereducation_tax    = round($service_tax*1/100,2);
//        $totals['total_payout'] = round(str_replace(",", "", $data2[1])-round($service_tax+$education_tax+$highereducation_tax,0),2);
		$totals['total_payout'] = number_format(str_replace(",", "", $data[1])+$service_tax,2);
        /************************************************/
       		/*$this->checkPageOverflow(1.5)
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(5.7, .2)
            ->movePush()
                ->setAlign('right')
                ->text($hlp->__("Total Commission"), 'down')
                //->text($hlp->__("Total Transaction Fees"), 'down')
                ->text($hlp->__("Total Adjustments"), 'down')
                ->text($hlp->__("Service Tax"), 'down')
                //->text($hlp->__("Education Tax"), 'down')
                //->text($hlp->__("Secondary & Higher Education Tax"), 'down')
                /* commented by mandar ******/
                /*->font('bold', 12)
                    ->text($hlp->__("Total Product Revenue"), 'down')
                ->font('normal');
                if ($this->isInPayoutAmount('tax', 'include')) {
                    $this->text($hlp->__("Total Tax"), 'down');
                } elseif ($this->isInPayoutAmount('tax', 'exclude_show')) {
                    $this->text($hlp->__("Total Tax (non-payable)"), 'down');
                }
                
                if ($this->isInPayoutAmount('shipping', 'include')) {
                    $this->text($hlp->__("Total Shipping"), 'down');
                } elseif ($this->isInPayoutAmount('shipping', 'exclude_show')) {
                	$this->text($hlp->__("Total Shipping (non-payable)"), 'down');
                }*/
                    //->text($hlp->__("Total Handling"), 'down')
                
            /*->movePop(1.5, 0)
            ->text($totals['com_amount'], 'down')
            //->text($totals['trans_fee'], 'down')
            ->text($totals['adj_amount'], 'down')
            ->text( 'Rs. '.$service_tax, 'down')*/
            //->text($education_tax, 'down')
            //->text($highereducation_tax, 'down')
            /* commented by mandar ******/
            /*->font('bold', 12)
                ->text($totals['subtotal'], 'down')
            ->font('normal');
            if (!$this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->text($totals['tax'], 'down');
            }
            if (!$this->isInPayoutAmount('shipping', 'exclude_hide')) {
                $this->text($totals['shipping'], 'down');
            }*/
                //->text($totals['handling'], 'down')
           /* ->movePush()
                ->moveRel(-3.5, .1);
        $stTotalHeight = $this->getTextHeight();
        if ($hlp->isUdpayoutActive()) {
            $stTotalHeight += $this->getTextHeight()*2;
        }
        $this->font('bold', 14);
        $stTotalRectPad = $this->getTextHeight()*.4;
        $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
        $this->rectangle(3.6, $stTotalHeightOut, .8, .8)
            ->movePop(-1.7, .15)
                ->text($hlp->__("Total"))
            ->moveRel(1.7, 0)->text('Rs. '.$totals['total_payout'], 'down');
		 $this->moveRel(-8,-0.8)
            ->moveRel(1,0)
            ->movePush()
			->font('normal',8)
            ->setAlign('left')
            ->text("Service Tax No:")
            ->movePop(1,0)
            ->text( 'AAFCC8726PSD002','down')
            //
            ->movePush()
            ->moveRel(-3.5,0);  
        // letterhead logo
			
			$this->move(.8,9.1)->font('bold',10)
			->text('For Craftsvilla Handicrafts Pvt Ltd');
			$image = Mage::getStoreConfig('udropship/admin/letterhead_logo', $store);
			if ($image) {
			$image = Mage::getStoreConfig('system/filesystem/media', $store) . '/udropship/' . $image;
			$this->move(.8,9.4)->image($image, 2, 1);
			}
			$this->move(.8,10.3)->font('normal',9)->text('Authorised Signatory');

        if ($hlp->isUdpayoutActive()) {
            $this->moveRel(-1.7)->text($hlp->__("Total Paid"))->moveRel(1.7, 0)->text($totals['total_paid'], 'down');
            $this->moveRel(-1.7)->text($hlp->__("Total Due"))->moveRel(1.7, 0)->text($totals['total_due'], 'down');
        }
		

        return $this;*/
        
        
   
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');

        //$actualServiceTax = $hlp->getServicetaxCv($totals['orders']['po_increment_id']); 
        $totals = $totals['orders']['amounts'];
        $totalPayout = $totals['total_payout'];
   
        $data                   = explode('Rs. ',$totals['com_amount']);
        $data2                  = explode('Rs. ',$totals['total_payout']); 
        $service_tax            = $totals['tax']; 
        
		//$education_tax          = round($service_tax*2/100,2);
        //$highereducation_tax    = round($service_tax*1/100,2);
//        $totals['total_payout'] = round(str_replace(",", "", $data2[1])-round($service_tax+$education_tax+$highereducation_tax,0),2);
		//$totals['total_payout'] = number_format(str_replace(",", "", $data[1])+$service_tax,2);
        $totals['total_payout'] = number_format(str_replace(",", "", $data[1])+$service_tax,2);
        /************************************************/
       		$this->checkPageOverflow(1.5)
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(5.7, .2)
            ->movePush()
                ->setAlign('right')
                ->text($hlp->__("Total Commission"), 'down')
                ->text($hlp->__("Service Tax"), 'down')
                
                
            ->movePop(1.5, 0)
            ->text($totals['com_amount'], 'down')
            
            ->text($totals['adj_amount'], 'down')
            ->text( 'Rs. '.$totals['tax'], 'down')
            
            ->movePush()
                ->moveRel(-3.5, .3);
        $stTotalHeight = $this->getTextHeight();
        if ($hlp->isUdpayoutActive()) {
            $stTotalHeight += $this->getTextHeight()*2;
        }
        $this->font('bold', 14);
        $stTotalRectPad = $this->getTextHeight()*.4;
        $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
        $this->rectangle(3.6, $stTotalHeightOut, .8, .8)
            ->movePop(-1.7, .35)
                ->text($hlp->__("Totaldfdfdf"))
            ->moveRel(1.7, 0)->text('Rs. '.$totalPayout, 'down');
		 $this->moveRel(-8,-0.8)
            ->moveRel(1,0)
            ->movePush()
			->font('normal',8)
            ->setAlign('left')
            ->text("Service Tax No:")
            ->movePop(1,0)
            ->text( 'AAECK2096HSD002','down')
            //
            ->movePush()
            ->moveRel(-3.5,0);  
        // letterhead logo
			
			$this->move(.8,9.1)->font('bold',10)
			->text('For Craftsvilla Handicrafts Pvt Ltd');
			$image = Mage::getStoreConfig('udropship/admin/letterhead_logo', $store);
			if ($image) {
			$image = Mage::getStoreConfig('system/filesystem/media', $store) . '/udropship/' . $image;
			$this->move(.8,9.4)->image($image, 2, 1);
			}
			$this->move(.8,10.3)->font('normal',9)->text('Authorised Signatory');

        if ($hlp->isUdpayoutActive()) {
            $this->moveRel(-1.7)->text($hlp->__("Total Paid"))->moveRel(1.7, 0)->text($totals['total_paid'], 'down');
            $this->moveRel(-1.7)->text($hlp->__("Total Due"))->moveRel(1.7, 0)->text($totals['total_due'], 'down');
        }
		
  return $this;
        
    }

    public function insertTotalsPageHeader()
    {
         $hlp = Mage::helper('udropship');
        $store = null;

        $this
            ->move(.5, .5)
            ->font('normal', 10)
            ->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));

        // letterhead logo
        /*$image = Mage::getStoreConfig('udropship/admin/letterhead_logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/udropship/' . $image;
            $this->move(6, .5)->image($image, 1, 1);
        }*/

        $this->move(.5, 10.6)->text($hlp->__("Page %s", ++$this->_totalsPageNum));

        $hideAll = $hideShipping = $hideTax = true;
        foreach ($this->_globalTotals as $vId=>$line) {
        	$hideAll = $hideAll && $this->isInPayoutAmount('all', 'exclude_hide', $vId);
        	$hideTax = $hideTax && $this->isInPayoutAmount('tax', 'exclude_hide', $vId); 
        	$hideShipping = $hideShipping && $this->isInPayoutAmount('shipping', 'exclude_hide', $vId);
        }
        $showAll = !$hideTax && !$hideShipping;

        $this->move(4.25, 1.5)
            ->font('bold', 16)->setAlign('center')->text('Statement Totals')
            ->move(.5, 2)
            ->rectangle(7.5, .3, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 8)
                ->setAlign('left')
                    ->text($hlp->__("Vendor"))
                ->setAlign('right');
            if ($hlp->isUdpayoutActive()) {
            	if ($hideAll) {
                    $this->moveRel(2.3, 0);
                } else {
                	$this->moveRel(1.7, 0);
              	}
                $this->text($hlp->__("Product"));
                if ($showAll) {
                    $this->moveRel(.6, 0)->text($hlp->__("Tax"));
                    //$this->moveRel(.6, 0)->text($hlp->__("Shipping"));
                } elseif (!$hideAll) {
                   	if ($hideTax) {
                   		//$this->moveRel(.9, 0)->text($hlp->__("Shipping"));
                   	} elseif ($hideShipping) {
                   		$this->moveRel(.9, 0)->text($hlp->__("Tax"));
                	}
                }
                    //->moveRel(.6, 0)->text($hlp->__("Handling"))
                $this->moveRel(.6, 0)->text($hlp->__("Comm"))
                    ->moveRel(.5, 0)->text($hlp->__("Trans"))
                    ->moveRel(.7, 0)->text($hlp->__("Adjustments"))
                    ->moveRel(.95, 0)->text($hlp->__("Payment"))
                    ->moveRel(.9, 0)->text($hlp->__("Paid"))
                    ->moveRel(.8, 0)->text($hlp->__("Due"));
            } else {
            	if ($hideAll) {
                    $this->moveRel(3, 0);
                } else {
                	$this->moveRel(2.3, 0);
              	}
                $this->text($hlp->__("Product"));
                if ($showAll) {
                    $this->moveRel(.7, 0)->text($hlp->__("Tax"));
                    //$this->moveRel(.7, 0)->text($hlp->__("Shipping"));
                } elseif (!$hideAll) {
                   	if ($hideTax) {
                   		//$this->moveRel(1, 0)->text($hlp->__("Shipping"));
                   	} elseif ($hideShipping) {
                   		$this->moveRel(1, 0)->text($hlp->__("Tax"));
                	}
                }
                    //->moveRel(.7, 0)->text($hlp->__("Handling"))
                $this->moveRel(.7, 0)->text($hlp->__("Commission"))
                    ->moveRel(.9, 0)->text($hlp->__("Trans.Fee"))
                    ->moveRel(.9, 0)->text($hlp->__("Adjustments"))
                    ->moveRel(1, 0)->text($hlp->__("Payment"));
            }
        $this->movePop(0, .3);
    }

    protected function _textWithAlphaOverlay($text, $overlay, $alpha)
    {
    	$this->text($text);
    	$tWidth = $this->getTextWidth($text);
    	$curFS = $this->getFontSize();
    	$this->getPage()->setAlpha(.7);
    	$this->fontSize($curFS*.7);
    	$this->movePush();
    	$curAlign = $this->getAlign();
    	if ($this->getAlign()=='left') {
    		$this->moveRel($tWidth*.5, 0, 'point');
    	} elseif ($this->getAlign()=='right') {
    		$this->moveRel(-$tWidth*.5, 0, 'point');
    	}
    	$this->moveRel(0, -$curFS*.7, 'point');
    	$this->setAlign('center');
		$this->text($overlay);
		$this->movePop();
		$this->getPage()->setAlpha(1);
		$this->setAlign($curAlign);
		$this->fontSize($curFS);
        return $this;
    }
    
    public function insertTotalsPage()
    {
        $hlp = Mage::helper('udropship');

        $this->_totalsPageNum = 0;

        $this->addPage()->insertTotalsPageHeader();

        $totals = array('subtotal'=>0, 'tax'=>0, 'shipping'=>0, 'handling'=>0, 'com_amount'=>0, 'trans_fee'=>0, 'adj_amount'=>0, 'total_payout'=>0, 'total_paid'=>0, 'total_due'=>0);

        $hideAll = $hideShipping = $hideTax = true;
        foreach ($this->_globalTotals as $vId=>$line) {
        	$hideAll = $hideAll && $this->isInPayoutAmount('all', 'exclude_hide', $vId);
        	$hideTax = $hideTax && $this->isInPayoutAmount('tax', 'exclude_hide', $vId); 
        	$hideShipping = $hideShipping && $this->isInPayoutAmount('shipping', 'exclude_hide', $vId);
        }
        $showAll = !$hideTax && !$hideShipping;
        foreach ($this->_globalTotals as $vId=>$line) {
        	$line['tax'] = $this->isInPayoutAmount('tax', 'exclude_hide', $vId) ? '' : $line['tax'];
        	$line['shipping'] = $this->isInPayoutAmount('shipping', 'exclude_hide', $vId) ? '' : $line['shipping'];
            $this->checkPageoverflow(.5, 'insertTotalsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                        ->text($line['vendor_name'], null, 30)
                    ->setAlign('right');
                if ($hlp->isUdpayoutActive()) {
                	if ($hideAll) {
                    	$this->moveRel(2.3, 0);
                	} else {
                		$this->moveRel(1.7, 0);
                	}
                   	$this->text($line['subtotal']);
                        
                   	if ($showAll) {
                        $this->moveRel(.6, 0);
                        if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
                        } else {
                        	$this->text($line['tax']);
                        }
                      	/*$this->moveRel(.6, 0);
                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                        	$this->text($line['shipping']);
                        }*/
                   	} elseif (!$hideAll) {
                   		if ($hideTax) {
                   			/*$this->moveRel(.9, 0);
	                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['shipping']);
	                        }*/
                   		} elseif ($hideShipping) {
                   			$this->moveRel(.9, 0);
	                   		if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['tax']);
	                        }
                   		}
                   	}

                    foreach (array('trans_fee','com_amount') as $_k) {
                        $line[$_k] = strpos($line[$_k], '-') === 0
                            ? substr($line[$_k], 1)
                            : '-'.$line[$_k];
                    }
                        //->moveRel(.6, 0)->text($line['handling'])
                    $this->moveRel(.6, 0)->text($line['com_amount'])
                        ->moveRel(.5, 0)->text($line['trans_fee'])
                        ->moveRel(.7, 0)->text($line['adj_amount'])
                        ->moveRel(.95, 0)->text($line['total_payout'])
                        ->moveRel(.9, 0)->text($line['total_paid'])
                        ->moveRel(.8, 0)->text($line['total_due']);
                } else {
                        /************** Added By Mandar on 21-05-2012*****************/
                            $data                   = explode('Rs. ',$line['com_amount']);
                            $data2                  = explode('Rs. ',$line['total_payout']);
                            $service_tax            = round(str_replace(",", "", $data[1])*12/100,2);
                            $education_tax          = round($service_tax*2/100,2);
                            $highereducation_tax    = round($service_tax*1/100,2);
                            $line['tax']          = round($service_tax+$education_tax+$highereducation_tax,0);
                            $line['total_payout'] = round(str_replace(",", "", $data2[1])-$line['tax'],2);
                        /************************************************/
                    
	                if ($hideAll) {
	                    $this->moveRel(3, 0);
	                } else {
	                	$this->moveRel(2.3, 0);
	              	}
                        $this->text($line['subtotal']);
                         
                        foreach (array('trans_fee','com_amount') as $_k) {
                            $line[$_k] = strpos($line[$_k], '-') === 0
                                ? substr($line[$_k], 1)
                                : '-'.$line[$_k];
                        }
	                if ($showAll) {
	                    $this->moveRel(.7, 0);
	                	if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
                        } else {
                        	$this->text('-Rs. '.$line['tax']);
                        }
                        /*$this->moveRel(.7, 0);
	                	if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                        	$this->text($line['shipping']);
                        }*/
	                } elseif (!$hideAll) {
	                   	if ($hideTax) {
	                   		/*$this->moveRel(1, 0);
	                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['shipping']);
	                        }*/
	                   	} elseif ($hideShipping) {
	                   		$this->moveRel(1, 0);
		                   	if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['tax']);
	                        }
	                	}
	                }

                    

                        //->moveRel(.7, 0)->text($line['handling'])
                    $this->moveRel(.7, 0)->text($line['com_amount'])
                        ->moveRel(.9, 0)->text($line['trans_fee'])
                        ->moveRel(.9, 0)->text($line['adj_amount'])
                        ->moveRel(1, 0)->text('Rs. '.$line['total_payout']);
                }
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            foreach ($totals as $k=>&$v) {
        		if ($k == 'shipping' && $this->isInPayoutAmount('shipping', 'include', $vId)
        			|| $k == 'tax' && $this->isInPayoutAmount('tax', 'include', $vId)
        			|| !in_array($k, array('tax','shipping'))
        		) {
                	$v += $this->_globalTotalsAmount[$vId][$k];
        		}
            }
            unset($v);
        }

        $this->checkPageOverflow(.5, 'insertTotalsPageHeader')
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(.1, .1)
            ->font('bold', 9)
                ->setAlign('left')
                    ->text('Grand Totals')
                ->setAlign('right');
            if ($hlp->isUdpayoutActive()) {
            		if ($hideAll) {
                    	$this->moveRel(2.3, 0);
                	} else {
                		$this->moveRel(1.7, 0);
                	}
                   	$this->price($totals['subtotal']);
                        foreach (array('tax','trans_fee','com_amount') as $_k) {
                            $totals[$_k] = strpos($totals[$_k], '-') === 0
                                ? substr($totals[$_k], 1)
                                : '-'.$totals[$_k];
                        }
                   	if ($showAll) {
                        $this->moveRel(.6, 0);
                        	$this->price($totals['tax']);
                      	//$this->moveRel(.6, 0);
                        	//$this->price($totals['shipping']);
                   	} elseif (!$hideAll) {
                   		/*if ($hideTax) {
                   			$this->moveRel(.9, 0);
	                        	$this->price($totals['shipping']);
                   		} elseif ($hideShipping) {
                   			$this->moveRel(.9, 0);
	                        	$this->price($totals['tax']);
                   		}*/
                   	}

                
                    //->moveRel(.6, 0)->price($totals['handling'])
                $this->moveRel(.6, 0)->price($totals['com_amount'])
                    ->moveRel(.5, 0)->price($totals['trans_fee'])
                    ->moveRel(.7, 0)->price($totals['adj_amount'])
                    ->moveRel(.95, 0)->price($totals['total_payout'])
                    ->moveRel(.9, 0)->price($totals['total_paid'])
                    ->moveRel(.8, 0)->price($totals['total_due']);
            } else {
                
                /************** Added By Mandar on 21-05-2012*****************/
                $data                   = $totals['com_amount'];
                $data2                  = $totals['total_payout'];
                $service_tax            = round($data*12/100,2);
                $education_tax          = round($service_tax*2/100,2);
                $highereducation_tax    = round($service_tax*1/100,2);
                $totals['tax']          = round($service_tax+$education_tax+$highereducation_tax,0);
                $totals['total_payout'] = round(str_replace(",", "", $data2)-$totals['tax'],2);
                /************************************************/
            		if ($hideAll) {
	                    $this->moveRel(3, 0);
	                } else {
	                	$this->moveRel(2.3, 0);
	              	}
                        $this->price($totals['subtotal']);
                        foreach (array('tax','trans_fee','com_amount') as $_k) {
                            $totals[$_k] = strpos($totals[$_k], '-') === 0
                                ? substr($totals[$_k], 1)
                                : '-'.$totals[$_k];
                        }
	                if ($showAll) {
	                    $this->moveRel(.7, 0);
                        	$this->price($totals['tax']);
                        //$this->moveRel(.7, 0);
                        	//$this->price($totals['shipping']);
	                } elseif (!$hideAll) {
	                   	if ($hideTax) {
	                   		//$this->moveRel(1, 0);
	                        //$this->price($totals['shipping']);
	                   	} elseif ($hideShipping) {
	                   		$this->moveRel(1, 0);
	                        $this->price($totals['tax']);
	                	}
	                }
                
                    //->moveRel(.7, 0)->price($totals['handling'])
                $this->moveRel(.7, 0)->price($totals['com_amount'])
                    ->moveRel(.9, 0)->price($totals['trans_fee'])
                    ->moveRel(.9, 0)->price($totals['adj_amount'])
                    ->moveRel(1, 0)->price($totals['total_payout']);
                
            }

        return $this;
    }
    
    public function insertAdjustmentsPage($params=array())
    {
        $hlp = Mage::helper('udropship');
        $core = Mage::helper('core');

        $this->_totalsPageNum = 0;
        
        if (!$this->getStatement()->getExtraAdjustments()) return $this;

        $this->addPage()->insertAdjustmentsPageHeader($params);

        foreach ($this->getStatement()->getExtraAdjustments() as $line) {
            foreach (array('adjustment_id','po_id','amount','comment','username','po_type','created_at') as $_k) {
                if (!isset($line[$_k])) $line[$_k] = '';
            }
            $this->checkPageoverflow(.5, 'insertAdjustmentsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                    ->text($line['adjustment_id'])
                    ->moveRel(1.2, 0)->text($line['po_id'])
                    ->moveRel(.7, 0)->text($line['po_type'])
                    ->setAlign('right')
                    ->moveRel(1.6, 0)->price($line['amount'])
                    ->setAlign('left')
                    ->moveRel(0.1, 0)->text($line['username'])
                    ->moveRel(.8, 0)->text($line['comment'], null, 50)
                    ->moveRel(2.5, 0)->text($core->formatDate($line['created_at'], 'short'));
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            unset($v);
        }

        return $this;
    }
    
    public function insertPayoutsPage($params=array())
    {
        $hlp = Mage::helper('udropship');
        $core = Mage::helper('core');

        $this->_totalsPageNum = 0;
        
        if (!$this->getStatement()->getPayouts()) return $this;

        $this->addPage()->insertPayoutsPageHeader($params);

        foreach ($this->getStatement()->getPayouts() as $line) {
            foreach (array('payout_id','payout_type','payout_method','payout_status','total_orders','total_payout','total_paid','total_due') as $_k) {
                if (!isset($line[$_k])) $line[$_k] = '';
            }
            $this->checkPageoverflow(.5, 'insertPayoutsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                    ->text($line['payout_id'])
                    ->moveRel(.8, 0)->text($line['payout_type'])
                    ->moveRel(.8, 0)->text($line['payout_method'])
                    ->moveRel(.8, 0)->text($line['payout_status'])
                    ->moveRel(.6, 0)->text($line['total_orders'])
                    ->setAlign('right')
                    ->moveRel(1.5, 0)->price($line['total_payout'])
                    ->moveRel(.8, 0)->price($line['total_paid'])
                    ->moveRel(.8, 0)->price($line['total_due'])
                    ->moveRel(.8, 0)->text($core->formatDate($line['created_at'], 'short'));
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            unset($v);
        }

        return $this;
    }
    
    public function calculateOrder($order1)
    {  
	
			$order1['amounts']['com_amount'] = Mage::getModel('udropship/vendor_statement')->getCommission($order1['po_increment_id']);
			
	        return $order1;					
		
	}

	
	public function getVendor()
    {
        return Mage::helper('udropship')->getVendor($this->getVendorId());
    }
    
    public function accumulateOrder($order, $totals_amount)
    {
        foreach ($this->_getEmptyTotals() as $k => $v) {
            if (isset($order['amounts'][$k])) $totals_amount[$k] += $order['amounts'][$k];
        }
       
        return $totals_amount;
    }
    
    protected function _getEmptyTotals($format=false)
    {
        return Mage::helper('udropship')->getStatementEmptyTotalsAmount($format);
    }
    
    public function addStatementCraftsvillaLogistic($statement)
    {
        $hlp = Mage::helper('udropship');
        
        $this->setStatement($statement); 
        
        $orderFrom = $statement->getOrderDateFrom();
        $orderTo = $statement->getOrderDateTo(); 
        $orderVendor = $statement->getVendorId(); 
        $orderStatementId = $statement->getStatementId(); 
        
        $ordersData = array();
        
       
    $statementQuery = "SELECT `t`.entity_id, `t`.increment_id FROM `sales_flat_shipment_grid` AS `main_table` INNER JOIN `sales_flat_shipment` AS `t` ON t.entity_id=main_table.entity_id INNER JOIN `sales_flat_order_payment` AS `b` ON b.parent_id=main_table.order_id WHERE ((t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')) AND (t.udropship_vendor='".$orderVendor."') AND (t.created_at IS NOT NULL) AND (t.created_at!='0000-00-00 00:00:00') AND (t.updated_at>='".$orderFrom."') AND (t.updated_at<='".$orderTo."') AND ((main_table.statement_id='".$orderStatementId."' OR main_table.statement_id IS NULL OR main_table.statement_id='')) ORDER BY `main_table`.`entity_id` asc";
    //echo $statementQuery = "SELECT `t`.entity_id, `t`.increment_id FROM `sales_flat_shipment_grid` AS `main_table` INNER JOIN `sales_flat_shipment` AS `t` ON t.entity_id=main_table.entity_id INNER JOIN `sales_flat_order_payment` AS `b` ON b.parent_id=main_table.order_id WHERE ((t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')) AND (t.udropship_vendor='".$vendorId."') AND (t.updated_at IS NOT NULL) AND (t.updated_at!='0000-00-00 00:00:00') AND (t.updated_at>='".$dateFrom."') AND (t.updated_at<='".$dateTo."') ORDER BY `main_table`.`entity_id` asc";

    $readCon = Mage::getSingleton('core/resource')->getConnection('core_read');
    $writeCon = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $statementQueryRes = $readCon->query($statementQuery)->fetchAll();
        
        foreach($statementQueryRes as $_po)
        {
            
            $po = Mage::getModel('sales/order_shipment')->load($_po['entity_id']);
            //echo '<pre>'; print_r($po); exit; 
            $actualServiceTax = $this->getServicetaxCv($po->getUpdatedAt()); 
            $shipmentId = $_po['increment_id']; 
            //echo '<pre>'; print_r($_po); exit;
            $order = array(
                'po_id' => $po->getId(),
                'date' => $hlp->getPoOrderCreatedAt($po),
                'id' => $hlp->getPoOrderIncrementId($po),
                'com_percent' => '20',
                'adjustments' => $po->getAdjustments(),
                'order_id' => $po->getOrderId(),
                'po_id' => $po->getId(),
                'order_created_at' => $hlp->getPoOrderCreatedAt($po),
                'order_increment_id' => $hlp->getPoOrderIncrementId($po),
                'po_increment_id' => $shipmentId,
                'po_created_at' => $po->getCreatedAt(),
                'po_updated_at' => $po->getUpdatedAt(),
                'base_shipping_amount' => $po->getBaseShippingAmount(),
                'itemised_total_shippingcost' => $po->getItemisedTotalShippingcost(),
                'udropship_vendor' => $po->getUdropshipVendor (),
                'po_statement_date' => $po->getStatementDate(),
                'cod_fee' => $codFee,
                'po_type' => $po instanceof Unirgy_DropshipPo_Model_Po ? 'po' : 'shipment'
                );
                
                $order['amounts'] = array_merge($this->_getEmptyTotals(), array(
                'subtotal' =>  $po->getBaseTotalValue(),
                'shipping' => $po->getShippingAmount(),
                'tax' => $po->getBaseTaxAmount(),
                'handling' => $po->getBaseHandlingFee(),
                'trans_fee' => $po->getTransactionFee(),
                'adj_amount' => $po->getAdjustmentAmount(),
            ));
                 
                 $order1 = $this->calculateOrderLogistic($order);
              //echo '<pre>';print_r($order1);exit;   

             $ordersData[]['orders']=$order1;

                 
            }
  
            $this->_curPageNum = 0;
            $this->addPage()->insertPageHeaderLogistic(array('first'=>true, 'data'=>$ordersData));
        
            $TotalCommission = 0;
            $TotalServiceTax = 0;
            $TotalSubTotal = 0;
            $totalOrdersCount = 0;
            
            if (!empty($ordersData)) 
            { 
                // iterate through orders
                foreach ($ordersData as $order) 
                {
                    
                    $stmtId = $statement->getStatementId(); 
                    $incId = $order['orders']['po_increment_id'];
                    $totalOrdersCount++;
                    $this->insertOrder($order);
                    //$TotalSubTotal += number_format($order['orders']['amounts']['subtotal'],2);
                    $TotalCommission += number_format($order['orders']['amounts']['com_amount'],2);
                    $TotalServiceTax += number_format(($order['orders']['amounts']['com_amount']*$actualServiceTax),2);
                    
                }
            
            } else { 
                $this->text($hlp->__('No orders found for this period.'), 'down')
                    ->moveRel(0, .5);
            }
            
            //$order['orders']['amounts']['subtotal'] = $TotalSubTotal;
            $order['orders']['amounts']['com_amount'] = $TotalCommission;
            $order['orders']['amounts']['tax'] = $TotalServiceTax;
            $Totalamount = number_format($TotalCommission+$TotalServiceTax,0);
            $order['orders']['amounts']['total_payout'] =  $Totalamount;
            $this->setAlign('left')->font('normal', 10);
            $this->insertTotals($order); 
            
            //$statement->setSubtotal($TotalSubTotal); 
            $statement->setComAmount($TotalCommission); 
            $statement->settax($TotalServiceTax);
            $statement->setTotalOrders($totalOrdersCount);
            $statement->setTotalPayout($Totalamount);
            $statement->save();
            $writeCon->closeConnection();
             $readCon->closeConnection();
            
            $this->insertAdjustmentsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
            if ($hlp->isUdpayoutActive()) {
                $this->insertPayoutsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
            }
            
            $this->setAlign('left')->font('normal', 10);
            foreach ($this->_pageFooter as $k=>&$p) 
            {
                if (!empty($p['done'])) {
                    continue;
                }
                $p['done'] = true;
                
            
                $str = $hlp->__('%s for %s - Page %s of %s',
                    $statement->getVendor()->getVendorName(),
                    $statement->getStatementPeriod(),
                    $p['page_num'],
                    $this->_curPageNum
                );
                $this->setPage($this->getPdf()->pages[$k])->move(.5, 10.6)->text($str);
            }
            
            unset($p);
            return $this;
        
    
    }
    public function calculateOrderLogistic($order1)
    {  
    
      $order1['amounts']['com_amount'] = Mage::getModel('udropship/vendor_statement')->getCommissionLogistic($order1['po_increment_id']);
      return $order1;                 
        
    }
    public function insertPageHeaderLogistic($params=array())
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        $this->_insertPageHeaderLogistic($params);
        // grid titles
        $this->insertGridHeaderLogistic();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    protected function _insertPageHeaderLogistic($params=array())
    {
        
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $store = null;

        // letterhead info
        $this->move(4.25, 0.5)
            ->font('bold', 16)->setAlign('center')
            //->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));
            //->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));
            
            //$this->move(4.25, 0.35)
            //->font('normal', 20)->setAlign('center')


            ->text('Craftsvilla Handicrafts Pvt Ltd');
            
            
            $this->move(4.25, 0.75)
            ->font('normal', 10)->setAlign('center')    
            ->text('1502 G Wing, 15th Floor, Lotus Corporate Park, Goregaon (East), Mumbai - 400063, Maharashtra'); 
            
            $this->move(1,1)->line(6);
            
            $this->move(4.25, 1.1)
            ->font('bold', 12)->setAlign('center')
            ->text('Service Invoice');      
        

        //$this->rectangle(0.75,0.95,0.75,0.95);
        // only for first page
        
        if (!empty($params['first'])) {
            $this->move(1.1, 1.6)->font('bold',10)->text('To');            
            $statement = $this->getStatement();
            
            $vendor = $statement->getVendor(); 
            $vendor1 = Zend_Json::decode($statement->getVendor()->getData('custom_vars_combined'));   
            $this->setAlign('left')->move(1.1, 1.8)->font('normal',9)->text($vendor1['check_pay_to']);
            // vendor info
            $this->setAlign('left')->move(1.1,2)->font('normal', 9)
                ->text($vendor->getBillingInfo());
            // statement info
            $stInfoHeight = $this->getTextHeight()*2;
            if ($hlp->isUdpoActive()) {
                $stInfoHeight += $this->getTextHeight();
            }
            $stTotalHeight = $this->getTextHeight()*6;
            if ($hlp->isUdpayoutActive()) {
                $stTotalHeight += $this->getTextHeight()*2;
            }
            $this->setAlign('right')
                ->move(6, 2)
                    ->text($hlp->__("Invoice #"), 'down')
                    ->text($hlp->__("Invoice Date"), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text($hlp->__("PO Type"), 'down');
            }
        
            $this->move(7.9, 2)
                    ->text($statement->getStatementId(), 'down')
                    ->text($core->formatDate(date('Y-m-d',strtotime($statement->getOrderDateTo().'+10 days')), 'medium'), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text(Mage::getSingleton('udropship/source')->setPath('statement_po_type')->getOptionLabel($statement->getPoType()), 'down');
            }
            $stTotalRectMargin = $this->getTextHeight()*.4;
            $stTotalRectPad = $this->getTextHeight()*.3;
            $stTotalRectY = 2+$stInfoHeight+$stTotalRectMargin;
            $stTotalTxtY = 2+$stInfoHeight+$stTotalRectMargin+$stTotalRectPad;
            $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
            // statement total
            /* commented by mandar ******/ /*
            $this->move(4.5, $stTotalRectY)
                ->rectangle(3.5, $stTotalHeightOut, .8, .8)
                ->font('bold')
                ->move(6, $stTotalTxtY)
                    ->text($hlp->__("Total Payment"), 'down');
             */
             
            if ($hlp->isUdpayoutActive()) {
                $this->text($hlp->__("Total Paid"), 'down')
                    ->text($hlp->__("Total Due"), 'down');
            }
            /*$this->move(7.9, $stTotalTxtY)
                    ->text($params['data']['totals']['total_payout'], 'down');*/
            if ($hlp->isUdpayoutActive()) {
                $this->text($params['data']['totals']['total_paid'], 'down')
                    ->text($params['data']['totals']['total_due'], 'down');
            }
            
            $this->move(.5, $stTotalRectY+$stTotalHeightOut+$stTotalRectMargin);
        }
        else
            {
                $this->move(0.5, 1.8);
            }
        
        return $this;
    }
    public function insertGridHeaderLogistic()
    {
        $hideTax = $this->getStatement()->getVendor()->getData('statement_tax_in_payout') == 'exclude_hide';
        $hideShipping = $this->getStatement()->getVendor()->getData('statement_shipping_in_payout') == 'exclude_hide';
        $hideBoth = $hideTax && $hideShipping;
        $hlp = Mage::helper('udropship');
        $this->rectangle(7.5, .4, .8, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text($hlp->__("Date"));
        if ($this->isInPayoutAmount('all', 'exclude_hide')) {  
            $this->moveRel(2.2, 0)->text($hlp->__("Shipment#"))
                //->moveRel(1.6, 0)->text($hlp->__("Product"))
                ->moveRel(2.6, 0)->text($hlp->__("Handling Charges"))
                ->moveRel(2.5, 0)->text($hlp->__("Service Tax"))
                ->moveRel(2.6, 0)->text($hlp->__("Total"))
            ->movePop(0, .4);
        } else { 
            $this->moveRel(1.2, 0)->text($hlp->__("Shipment#"))
                //->moveRel(1, 0)->text($hlp->__("Product"));
                //->moveRel(1, 0)->text($hlp->__("Commission"));
              ->moveRel(1.6, 0)->text($hlp->__("Handling Charges"));
            //$this->moveRel(1, 0)->text($hlp->__("Service Tax"));
            //if ($this->isInPayoutAmount('tax', 'exclude_hide')) {
              //  $this->moveRel(1, 0)->text($hlp->__("Service Tax"));
            //} //elseif ($this->isInPayoutAmount('shipping', 'exclude_hide')) {
                //$this->moveRel(1, 0)->text($hlp->__("Tax"));
            //}
             //else {
                //if(Mage::getSingleton("udropship/session")->getImanage() == 1):
                    $this->moveRel(1.5, 0)->text($hlp->__("Service Tax"));
                //endif;
            //}
            
            $this->moveRel(1.6, 0)->text($hlp->__("Total"))
            ->movePop(0,.4);            ;
        }
        
        return $this;
    }
    
}
