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
        $this->move(.5, .5)
            ->font('normal', 10)
            ->text(Mage::getStoreConfig('udropship/admin/letterhead_info', $store));
        // letterhead logo
        $image = Mage::getStoreConfig('udropship/admin/letterhead_logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/udropship/' . $image;
            $this->move(6, .5)->image($image, 2, 1);
        }
        $this->move(.5, 2);
        // only for first page
        if (!empty($params['first'])) {
            $statement = $this->getStatement();
            $vendor = $statement->getVendor();
            // vendor info
            $this->font('normal', 12)
                ->text($vendor->getBillingInfo());
            // statement info
            $stInfoHeight = $this->getTextHeight()*2;
            if ($hlp->isUdpoActive()) {
                $stInfoHeight += $this->getTextHeight();
            }
            $stTotalHeight = $this->getTextHeight();
            if ($hlp->isUdpayoutActive()) {
                $stTotalHeight += $this->getTextHeight()*2;
            }
            $this->setAlign('right')
                ->move(6, 2)
                    ->text($hlp->__("Statement #"), 'down')
                    ->text($hlp->__("Statement Date"), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text($hlp->__("PO Type"), 'down');
            }
            $this->move(7.9, 2)
                    ->text($statement->getStatementId(), 'down')
                    ->text($core->formatDate($statement->getCreatedAt(), 'medium'), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text(Mage::getSingleton('udropship/source')->setPath('statement_po_type')->getOptionLabel($statement->getPoType()), 'down');
            }
            $stTotalRectMargin = $this->getTextHeight()*.4;
            $stTotalRectPad = $this->getTextHeight()*.3;
            $stTotalRectY = 2+$stInfoHeight+$stTotalRectMargin;
            $stTotalTxtY = 2+$stInfoHeight+$stTotalRectMargin+$stTotalRectPad;
            $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
            // statement total
            $this->move(4.5, $stTotalRectY)
                ->rectangle(3.5, $stTotalHeightOut, .8, .8)
                ->font('bold')
                ->move(6, $stTotalTxtY)
                    ->text($hlp->__("Total Payment"), 'down');
            if ($hlp->isUdpayoutActive()) {
                $this->text($hlp->__("Total Paid"), 'down')
                    ->text($hlp->__("Total Due"), 'down');
            }
            $this->move(7.9, $stTotalTxtY)
                    ->text($params['data']['totals']['total_payout'], 'down');
            if ($hlp->isUdpayoutActive()) {
                $this->text($params['data']['totals']['total_paid'], 'down')
                    ->text($params['data']['totals']['total_due'], 'down');
            }
            $this->move(.5, $stTotalRectY+$stTotalHeightOut+$stTotalRectMargin);
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
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text($hlp->__("Date"));
        if ($this->isInPayoutAmount('all', 'exclude_hide')) {
            $this->moveRel(1.2, 0)->text($hlp->__("Order#"))
            	->moveRel(1.6, 0)->text($hlp->__("Product"))
                ->moveRel(1.5, 0)->text($hlp->__("Comm (%)/Trans"))
                ->moveRel(1.6, 0)->text($hlp->__("Net Amount"))
            ->movePop(0, .4);
        } else {
        	$this->moveRel(0.8, 0)->text($hlp->__("Order#"))
            	->moveRel(1, 0)->text($hlp->__("Product"));
            if ($this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->moveRel(1, 0)->text($hlp->__("Shipping"));
            } elseif ($this->isInPayoutAmount('shipping', 'exclude_hide')) {
            	$this->moveRel(1, 0)->text($hlp->__("Tax"));
            } else {
            	$this->moveRel(1, 0)->text($hlp->__("Shipping/Tax"));
            }
            $this->moveRel(2, 0)->text($hlp->__("Comm (%)/Trans"))
                ->moveRel(1.6, 0)->text($hlp->__("Net Amount"))
            ->movePop(0, .4)
        	;
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
        $core = Mage::helper('core');

        foreach (array('trans_fee','com_percent','com_amount') as $_k) {
            $order[$_k] = strpos($order[$_k], '-') === 0
                ? substr($order[$_k], 1)
                : '-'.$order[$_k];
        }

        $this->checkPageOverflow()
            ->setMaxHeight(0)
            ->font('normal', 10)
            ->movePush()
                ->setAlign('left')
                    ->text($core->formatDate($order['date'], 'short'));
		if ($this->isInPayoutAmount('all', 'exclude_hide')) {
			$this->moveRel(1.2, 0)->text($order['id'])
                ->moveRel(1.6, 0)->text($order['subtotal'])
                ->moveRel(1.5, 0)->text("{$order['com_amount']} ({$order['com_percent']}%) / {$order['trans_fee']}")
            	->setAlign('right')
                ->moveRel(3, 0)->text($order['total_payout']);
		} else {
            $this->moveRel(.8, 0)->text($order['id'])
                ->moveRel(1, 0)->text($order['subtotal']);
			if ($this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->moveRel(1, 0)->text("{$order['shipping']}");
            } elseif ($this->isInPayoutAmount('shipping', 'exclude_hide')) {
            	$this->moveRel(1, 0)->text("{$order['tax']}");
            } else {
            	$this->moveRel(1, 0)->text("{$order['shipping']} / {$order['tax']}");
            }
            $this->moveRel(2, 0)->text("{$order['com_amount']} ({$order['com_percent']}%) / {$order['trans_fee']}")
                ->setAlign('right')
                    ->moveRel(2.5, 0)->text($order['total_payout']);

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
                            ->text($core->formatDate($order['date'], 'short'))
                            ->moveRel(.8, 0)->text($order['id'])
                            ->moveRel(1, 0)->text($adj['comment'], null, 70)
                        ->setAlign('right')
                            ->moveRel(5.5, 0)->text($adj['amount'])
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
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');

        foreach (array('trans_fee','com_amount') as $_k) {
            $totals[$_k] = strpos($totals[$_k], '-') === 0
                ? substr($totals[$_k], 1)
                : '-'.$totals[$_k];
        }

        $this->checkPageOverflow(1.5)
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(5.7, .2)
            ->movePush()
                ->setAlign('right')
                ->font('bold', 12)
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
                }
                    //->text($hlp->__("Total Handling"), 'down')
                $this->text($hlp->__("Total Commission"), 'down')
                    ->text($hlp->__("Total Transaction Fees"), 'down')
                    ->text($hlp->__("Total Adjustments"), 'down')
            ->movePop(1.7, 0)
            ->font('bold', 12)
                ->text($totals['subtotal'], 'down')
            ->font('normal');
            if (!$this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->text($totals['tax'], 'down');
            }
            if (!$this->isInPayoutAmount('shipping', 'exclude_hide')) {
                $this->text($totals['shipping'], 'down');
            }
                //->text($totals['handling'], 'down')
            $this->text($totals['com_amount'], 'down')
                ->text($totals['trans_fee'], 'down')
                ->text($totals['adj_amount'], 'down')
            ->movePush()
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
                ->text($hlp->__("Total Payment"))
            ->moveRel(1.7, 0)->text($totals['total_payout'], 'down')
        ;
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
        $image = Mage::getStoreConfig('udropship/admin/letterhead_logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/udropship/' . $image;
            $this->move(6, .5)->image($image, 2, 1);
        }

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
                    $this->moveRel(.6, 0)->text($hlp->__("Shipping"));
                } elseif (!$hideAll) {
                   	if ($hideTax) {
                   		$this->moveRel(.9, 0)->text($hlp->__("Shipping"));
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
                    $this->moveRel(.7, 0)->text($hlp->__("Shipping"));
                } elseif (!$hideAll) {
                   	if ($hideTax) {
                   		$this->moveRel(1, 0)->text($hlp->__("Shipping"));
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
                      	$this->moveRel(.6, 0);
                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                        	$this->text($line['shipping']);
                        }
                   	} elseif (!$hideAll) {
                   		if ($hideTax) {
                   			$this->moveRel(.9, 0);
	                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['shipping']);
	                        }
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
	                if ($hideAll) {
	                    $this->moveRel(3, 0);
	                } else {
	                	$this->moveRel(2.3, 0);
	              	}
                    $this->text($line['subtotal']);
	                if ($showAll) {
	                    $this->moveRel(.7, 0);
	                	if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
                        } else {
                        	$this->text($line['tax']);
                        }
                        $this->moveRel(.7, 0);
	                	if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                        	$this->text($line['shipping']);
                        }
	                } elseif (!$hideAll) {
	                   	if ($hideTax) {
	                   		$this->moveRel(1, 0);
	                   		if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
	                        	$this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
	                        } else {
	                        	$this->text($line['shipping']);
	                        }
	                   	} elseif ($hideShipping) {
	                   		$this->moveRel(1, 0);
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

                        //->moveRel(.7, 0)->text($line['handling'])
                    $this->moveRel(.7, 0)->text($line['com_amount'])
                        ->moveRel(.9, 0)->text($line['trans_fee'])
                        ->moveRel(.9, 0)->text($line['adj_amount'])
                        ->moveRel(1, 0)->text($line['total_payout']);
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
                   	if ($showAll) {
                        $this->moveRel(.6, 0);
                        	$this->price($totals['tax']);
                      	$this->moveRel(.6, 0);
                        	$this->price($totals['shipping']);
                   	} elseif (!$hideAll) {
                   		if ($hideTax) {
                   			$this->moveRel(.9, 0);
	                        	$this->price($totals['shipping']);
                   		} elseif ($hideShipping) {
                   			$this->moveRel(.9, 0);
	                        	$this->price($totals['tax']);
                   		}
                   	}

                foreach (array('trans_fee','com_amount') as $_k) {
                    $totals[$_k] = strpos($totals[$_k], '-') === 0
                        ? substr($totals[$_k], 1)
                        : '-'.$totals[$_k];
                }
                    //->moveRel(.6, 0)->price($totals['handling'])
                $this->moveRel(.6, 0)->price($totals['com_amount'])
                    ->moveRel(.5, 0)->price($totals['trans_fee'])
                    ->moveRel(.7, 0)->price($totals['adj_amount'])
                    ->moveRel(.95, 0)->price($totals['total_payout'])
                    ->moveRel(.9, 0)->price($totals['total_paid'])
                    ->moveRel(.8, 0)->price($totals['total_due']);
            } else {
            		if ($hideAll) {
	                    $this->moveRel(3, 0);
	                } else {
	                	$this->moveRel(2.3, 0);
	              	}
                    $this->price($totals['subtotal']);
	                if ($showAll) {
	                    $this->moveRel(.7, 0);
                        	$this->price($totals['tax']);
                        $this->moveRel(.7, 0);
                        	$this->price($totals['shipping']);
	                } elseif (!$hideAll) {
	                   	if ($hideTax) {
	                   		$this->moveRel(1, 0);
	                        $this->price($totals['shipping']);
	                   	} elseif ($hideShipping) {
	                   		$this->moveRel(1, 0);
	                        $this->price($totals['tax']);
	                	}
	                }
                foreach (array('trans_fee','com_amount') as $_k) {
                    $totals[$_k] = strpos($totals[$_k], '-') === 0
                        ? substr($totals[$_k], 1)
                        : '-'.$totals[$_k];
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
}
