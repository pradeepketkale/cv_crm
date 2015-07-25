<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'udropship';

    protected $_methods = array();
    protected $_allowedMethods = array();

    protected $_rawRequest;

    /**
    * Collect and combine rates from vendor carriers
    *
    * @param Mage_Shipping_Model_Rate_Request $request
    * @return Mage_Shipping_Model_Rate_Result|boolean
    */
   public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
		//echo 'Entered Marketplace';
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->_rawRequest = $request;

        $hlp = Mage::helper('udropship');
        $hlpd = Mage::helper('udropship/protected');

        $carrierNames = Mage::getSingleton('udropship/source')->getCarriers();

        // get available dropship shipping methods
        $shipping = $hlp->getShippingMethods();
        // associate them with system carrier methods
        $systemMethods = $hlp->getMultiSystemShippingMethods();

        // prepare data
        $items = $request->getAllItems();
		
        try {
            $hlpd->prepareQuoteItems($items);
        } catch (Exception $e) {
            return;
        }

        if (!$hlpd->getQuote()) {
            return;
        }

        $quote = $hlpd->getQuote();
        $address = $hlpd->getQuote()->getShippingAddress();
        foreach ($items as $item) {
        	if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }

        Mage::dispatchEvent('udropship_carrier_collect_before', array('request'=>$request, 'address'=>$address));

        $requests = $hlpd->getRequestsByVendor($items, $request);
        
		if ($quote->getUdropshipCarrierError()) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage($quote->getUdropshipCarrierError() ? $quote->getUdropshipCarrierError() : $errorMsg);
            return $error;
        }
        
        $requestVendors = array_keys($requests);
		// build separate requests grouped by carrier and vendor
        $carriers = array();
        $numMethodsPerVendor = array();
        foreach ($requests as $vId=>$vRequests) {
            foreach ($vRequests as $cCode=>$r) {
                #$cCode = $r->getCarrierCode();
                $carriers[$cCode][$vId]['request'] = $r;
               	$methods = $hlp->getVendor($vId)->getShippingMethods();
                foreach ($methods as $m) {
                    if (($s = $shipping->getItemById($m['shipping_id']))) {
                        $carriers[$cCode][$vId]['methods'][$s->getShippingCode()] = $s->getSystemMethods($cCode);
                    }
                }
            }
            // skip methods that are not shared by ALL vendors
            $vendorMethods = $hlp->getVendor($vId)->getShippingMethods();
            foreach ($shipping as $s) {
                if (empty($vendorMethods[$s->getId()])) {
                    $s->setIsSkipped(true);
                }
            }
        }

        $freeMethods = explode(',', Mage::getStoreConfig('carriers/udropship/free_method', $hlpd->getStore()));
        if ($freeMethods) {
            $_freeMethods = array();
            foreach ($freeMethods as $freeMethod) {
                if (is_numeric($freeMethod)) {
                    if ($shipping->getItemById($freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                } else {
                    if ($shipping->getItemByColumnValue('shipping_code', $freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                }
                $_freeMethods[] = $freeMethod;
            }
            $freeMethods = $_freeMethods;
        }

        // quote.udropship_shipping_details
        $details = array('version' => $hlp->getVersion());
        // vendors participating in the estimate
        $vendors = array();

        $errorAction = $hlpd->getStore()->getConfig('udropship/customer/estimate_error_action');

        // send actual requests and collect results
        foreach ($carriers as $cCode=>$requests) {
            $keys = array_keys($requests);
            foreach ($keys as $k) {
                $vendor = $hlp->getVendor($k);
                $vMethods = $vendor->getShippingMethods();
                $result = $hlpd->collectVendorCarrierRates($requests[$k]['request']);
                if ($result===false) {
                    if ($errorAction=='fail') {
                        //return $hlpd->errorResult();
                        continue;
                    } elseif ($errorAction=='skip') {
                        continue;
                    }
                }

                $rates = $result->getAllRates();
                $keys1 = array_keys($rates);
                foreach ($keys1 as $k1) {
                    if ($rates[$k1] instanceof Mage_Shipping_Model_Rate_Result_Error) {
                        if ($errorAction=='fail') {
                            //return $hlpd->errorResult('udropship', $rates[$k1]->getErrorMessage());
                            continue 2;
                        } elseif ($errorAction=='skip') {
                            continue 2;
                        }
                    }
                    $wildcardUsed = false;
                    if (empty($systemMethods[$rates[$k1]->getCarrier()][$rates[$k1]->getMethod()])) {
                        if (!empty($systemMethods[$rates[$k1]->getCarrier()]['*'])) {
                            $wildcardUsed = true;
                        } else {
                            continue;
                        }
                    }

                    if ($wildcardUsed) {
                        $smArray = $systemMethods[$rates[$k1]->getCarrier()]['*'];
                    } else {
                        $smArray = $systemMethods[$rates[$k1]->getCarrier()][$rates[$k1]->getMethod()];
                    }

                    foreach ($smArray as $s) {
                        if ($s->getIsSkipped()) {
                            continue;
                        }
                        $vMethod = $vMethods[$s->getId()];
                        $vendorCode = $vendor->getCarrierCode();
                        if ($requests[$k]['request']->getForcedCarrierFlag()) {
                            $ecCode = $ocCode = $rates[$k1]->getCarrier();
                        } else {
                            $ecCode = !empty($vMethod['est_carrier_code'])
                                ? $vMethod['est_carrier_code']
                                : (!empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode);
                            $ocCode = !empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode;
                        }
                        $oldEstCode = null;
                        if (!empty($details['methods'][$s->getShippingCode()]['vendors'][$k]['est_code'])) {
                            list($oldEstCode, ) = explode('_', $details['methods'][$s->getShippingCode()]['vendors'][$k]['est_code'], 2);
                        }
                        if ($ecCode!=$rates[$k1]->getCarrier()) {
                            if ($vendor->getUseRatesFallback()) {
                                if ($oldEstCode==$ecCode) {
                                    continue;
                                } elseif ($oldEstCode!=$ocCode && $ocCode==$rates[$k1]->getCarrier()) {
                                    $ecCode = $ocCode;
                                } elseif (!$oldEstCode && $vendorCode==$rates[$k1]->getCarrier()) {
                                    $ecCode = $vendorCode;
                                } else {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        }
                        if ('**estimate**' == $ocCode) {
                            $ocCode = $ecCode;
                        }
                        if ($wildcardUsed && $ecCode!=$ocCode) {
                            continue;
                        }
						// Craftsvilla Comment By Amit Pitre On 03-07-2012 For the error in per item shipping as it 
						//calculating based on product weight.

                        // set free method if required
                        /*if ($freeMethods
                            && $this->getConfigData('free_shipping_allowed')
                            && $this->getConfigData('freeweight_allowed')
                            && $requests[$k]['request']->hasFreeMethodWeight()
                            && $requests[$k]['request']->getFreeMethodWeight()==0
                            && in_array($s->getShippingCode(), $freeMethods)
                        ) {
                            $rates[$k1]->setPrice(0);
                        }*/
						///////////////////////////////////////////////////////////////////////////////////////////////
                        $detail = array(
                            'cost' => sprintf('%.4f', $rates[$k1]->getCost()),
                            'price' => sprintf('%.4f', $rates[$k1]->getPrice()),
                            'est_code' => $rates[$k1]->getCarrier().'_'.$rates[$k1]->getMethod(),
                            'est_carrier_title' => $rates[$k1]->getCarrierTitle(),
                            'est_method_title' => $rates[$k1]->getMethodTitle(),
                        );
                        if ($ecCode==$ocCode) {
                            $detail['code'] = $detail['est_code'];
                            $detail['carrier_title'] = $detail['est_carrier_title'];
                            $detail['method_title'] = $detail['est_method_title'];
                        } else {
                            $ocMethod = $s->getSystemMethods($ocCode);
                            if (empty($ocMethod)) {
                                continue;
                            }
                            $methodNames = $hlp->getCarrierMethods($ocCode);
                            $detail['code'] = $ocCode.'_'.$ocMethod;
                            $detail['carrier_title'] = $carrierNames[$ocCode];
                            $detail['method_title'] = $methodNames[$ocMethod];
                        }
                        $vendors[$k] = 1;
						$scKey = !$wildcardUsed ? $s->getShippingCode() : $s->getShippingCode().'___'.$detail['code'];
                        $details['methods'][$scKey]['id'] = $s->getShippingId();
                        if (($curUdpoSeqNumber = $requests[$k]['request']->getUdpoSeqNumber())
                            && !empty($details['methods'][$scKey]['vendors'][$k])
                        ) {
                            $snByVendor = $address->getSeqNumbersByVendor();
                            $snByVendor[$k][$curUdpoSeqNumber][$scKey] = true;
                            $address->setSeqNumbersByVendor($snByVendor);
                            $ratesBySeqNumber = @$details['methods'][$scKey]['vendors'][$k]['rates_by_seq_number'];
                            if (!is_array($ratesBySeqNumber)) {
                                $ratesBySeqNumber = array();
                            }
                            $ratesBySeqNumber[$curUdpoSeqNumber] = $detail;
                            if (is_array($snByVendor) && $curUdpoSeqNumber>=max(array_keys($snByVendor))) {
                                $_resCost = $detail['cost'] + $details['methods'][$scKey]['vendors'][$k]['cost'];
                                $_resPrice = $detail['price'] + $details['methods'][$scKey]['vendors'][$k]['price'];
                                $details['methods'][$scKey]['vendors'][$k] = $detail;
                                $details['methods'][$scKey]['vendors'][$k]['cost'] = $_resCost;
                                $details['methods'][$scKey]['vendors'][$k]['price'] = $_resPrice;
                            }
                            $details['methods'][$scKey]['vendors'][$k]['rates_by_seq_number'] = $ratesBySeqNumber;
                        } else {
                            $details['methods'][$scKey]['vendors'][$k] = $detail;
                            if (!empty($curUdpoSeqNumber)) {
                                $snByVendor = $address->getSeqNumbersByVendor();
                                $snByVendor[$k][$curUdpoSeqNumber][$scKey] = true;
                                $address->setSeqNumbersByVendor($snByVendor);
                                $details['methods'][$scKey]['vendors'][$k]['rates_by_seq_number'] = array(
                                    $curUdpoSeqNumber => $detail
                                );
                            }
                        }
                        if ($wildcardUsed) {
                            $details['methods'][$scKey]['wildcard_code'] = $detail['code'];
                            $details['methods'][$scKey]['wildcard_carrier_title'] = $detail['carrier_title'];
                            $details['methods'][$scKey]['wildcard_method_title'] = $detail['method_title'];
                        }
                    }
                }
            }
        }
        $snByVendor = $address->getSeqNumbersByVendor();
        if (!empty($snByVendor) && is_array($snByVendor)) {
            $totalSnUsed = array();
            $methodsUsedBySn = array();
            foreach ($snByVendor as $vId => $snData) {
                foreach ($snData as $__sn => $snMethods) {
                    $totalSnUsed[$vId.'-'.$__sn] = 1;
                    foreach ($snMethods as $_snMethod => $_dummy) {
                        $methodsUsedBySn[$_snMethod][$vId.'-'.$__sn] = 1;
                    }
                }
            }
            $totalSnUsed = array_sum($totalSnUsed);
            foreach ($methodsUsedBySn as $_snMethod => $_snMethodTotals) {
                if (array_sum($_snMethodTotals)<$totalSnUsed) {
                    unset($details['methods'][$_snMethod]);
                }
            }
        }
        $address->setUdropshipShippingDetails(Zend_Json::encode($details));
#Mage::log($hlpd->getQuote()->getUdropshipShippingDetails());
#exit;
        // googlecheckout merchant calculations don't save address
        if (Mage::app()->getRequest()->getRouteName()=='googlecheckout') {
            $transaction = Mage::getModel('core/resource_transaction');
            $transaction->addObject($address);
            foreach ($request->getAllItems() as $item) {
                $transaction->addObject($item);
            }
            $transaction->save();
        }

        if (empty($vendors) || ($errorAction == 'fail' && count($vendors)<count($requestVendors))) {
            return $hlpd->errorResult('udropship');
        }

        $totalMethod = Mage::getStoreConfig('udropship/customer/estimate_total_method');

        // collect prices from details
        $totals = array();
        $numVendors = sizeof($vendors);
        $processedMethods = array();
        foreach ($details['methods'] as $mCode=>$method) {
            $sId = $method['id'];
            unset($method['id']);
            if (in_array($method, $processedMethods)) continue;
            $processedMethods[] = $method;
            $method['id'] = $sId;
            $s = $shipping->getItemById($method['id']);
            // skip not common methods
            if ($s->getIsSkipped() || sizeof($method['vendors'])<$numVendors) {
                continue;
            }
            if (empty($prices[$mCode])) {
                $totals[$mCode] = array(
                    'cost' => 0,
                    'price' => 0,
                    'title' => $s->getShippingTitle()
                );
                if (!empty($method['wildcard_code'])) {
                    $totals[$mCode]['title'] .= sprintf(' [%s - %s]', $method['wildcard_carrier_title'], $method['wildcard_method_title']);
                }
            }
            foreach ($method['vendors'] as $vId=>$rate) {
                $totals[$mCode]['cost'] = $hlp->applyEstimateTotalCostMethod($totals[$mCode]['cost'], $rate['cost']);
                $totals[$mCode]['price'] = $hlp->applyEstimateTotalPriceMethod($totals[$mCode]['price'], $rate['price']);
            }
        }
#Mage::log($totals);

        // return Magento formated shipping carrier result
        $result = Mage::getModel('shipping/rate_result');

        // flat rate customization
        /*
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier('udropship');
        $method->setCarrierTitle($dropshipCarrier->getConfigData('title'));
        $method->setMethod('flatrate');
        $method->setMethodTitle('Flat Rate');
        $method->setCost(7.5);
        $method->setPrice(7.5);
        $result->append($method);
        */
		//echo '<pre>';print_r($totals);exit;
        if (!empty($totals)) {
            foreach ($totals as $mCode=>$total) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier('udropship');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($mCode);
                $method->setMethodTitle($total['title']);
				// Craftsvilla Comment Added By Amit Pitre For Free International Shipping above 5000 on 20-07-2012
				// Craftsvilla Comment Added By Dileswar For Close Free International Shipping above 5000 on 01-10-2012
					//$method->setCost($total['cost']);
		            //$method->setPrice($this->getMethodPrice($total['price'], $mCode));
				
				//if(($this->_rawRequest->getDestCountryId()!='IN') && ($address->getBaseSubtotal()>=60000))

$spclcatagoryShipPrice = $this->addspclCategoryShippingPrice($items,$this->_rawRequest->getDestCountryId());

				if($this->_rawRequest->getDestCountryId()!='IN' && ($this->getConfigData('free_shipping_subtotal') <= $this->_rawRequest->getPackageValueWithDiscount()))
				{
		            $method->setCost(0);
					$method->setPrice($spclcatagoryShipPrice);
				}
				else{
					$method->setCost($total['cost']);
		            //$method->setPrice($this->getMethodPrice($total['price'], $mCode));
					$items = $request->getAllItems();
					$method->setPrice($this->getExactShippingRate($items,$request));
				}
				///////////////////////////////////////////////////////////////////////////////////////////////////////
                $result->append($method);
            }
        } else {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage($errorMsg);
            return $error;
        }
        Mage::dispatchEvent('udropship_carrier_collect_after', array('request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details));
        return $result;
    }

    public function getMethodPrice($cost, $method='')
    {
#if ($_SERVER['REMOTE_ADDR']=='24.20.46.76') { echo "<pre>"; print_r($this->_rawRequest->debug()); exit; }
        $freeMethods = explode(',', $this->getConfigData('free_method'));
        if (in_array($method, $freeMethods)
            && $this->getConfigData('free_shipping_allowed')
            && $this->getConfigData('free_shipping_enable')
            && $this->getConfigData('free_shipping_subtotal') <= $this->_rawRequest->getPackageValueWithDiscount())
        {
            $price = '0.00';
        } else {
            $price = $this->getFinalPriceWithHandlingFee($cost);
        }
        return $price;
    }
	
	public function addspclCategoryShippingPrice($items,$countryId)
	{
		$shippingPrice = 0;
		foreach( $items as $item )
		{
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$prdCatQuery = "SELECT `category_id2` FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$item->getProductId()."'";
			$resultprdCatQuery = $read->query($prdCatQuery)->fetch();
			$resultprdCatId = $resultprdCatQuery['category_id2'];					
		
			if($resultprdCatId == 5)
			{	
				$product = Mage::helper('catalog/product')->loadnew($item->getProductId());
				$shippingPrice += ($product->getIntershippingcost()*($item->getQty()));
			}				
				
		}
		return $shippingPrice;
	}

    public function getAllowedMethods()
    {
        if (empty($this->_allowedMethods)) {
            $shipping = $this->_getAllMethods();
            $methods = array();
            foreach ($shipping as $m) {
                $methods[$m->getShippingCode()] = $m->getShippingTitle();
            }
            $this->_allowedMethods = $methods;
        }
        return $this->_allowedMethods;
    }

    protected function _getAllMethods()
    {
        if (empty($this->_methods)) {
            $this->_methods = Mage::helper('udropship')->getShippingMethods()
                ->setOrder('days_in_transit', 'desc');
        }
        return $this->_methods;
    }

    public function getUseForAllProducts()
    {
        return true;
    }
	public function getExactShippingRate($items,$request){
		$counterIntship = '1';
		foreach( $items as $item )
				{
					$product = Mage::helper('catalog/product')->loadnew($item->getProductId());
					//$subtotal += $product->getPrice()*$item->getQty();
					
							
//if($item->getQty()>1)
							   //{
									///// Craftsvilla Comment By Amit Pitre On 18-06-2012 to calculate per item shipping cost for outside india.///////////////
									//if($request->getDestCountryId()!='IN')
																	
											//$shippingPrice += ($product->getIntershippingcost()+$product->getInterShippingTablerate()*($item->getQty()-1))/$counterIntship;
									//else
									//$shippingPrice += ($product->getShippingcost()+$product->getShippingTablerate()*($item->getQty()-1));
								//}
								//else {
									if($request->getDestCountryId()!='IN'){
										$shippingPrice += ($product->getIntershippingcost()*$item->getQty())/$counterIntship;
									}else
									{	$shippingPrice += ($product->getShippingcost()*$item->getQty());}
								//}
											  
					//$counterIntship++;
				}
				
		return round($shippingPrice);

		}
}
