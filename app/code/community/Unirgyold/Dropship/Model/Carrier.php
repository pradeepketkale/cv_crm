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

        $address = $hlpd->getQuote()->getShippingAddress();
        foreach ($items as $item) {
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }
        $requests = $hlpd->getRequestsByVendor($items, $request);
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
                    if (empty($systemMethods[$rates[$k1]->getCarrier()][$rates[$k1]->getMethod()])) {
                        continue;
                    }

                    foreach ($systemMethods[$rates[$k1]->getCarrier()][$rates[$k1]->getMethod()] as $s) {
                        if ($s->getIsSkipped()) {
                            continue;
                        }
                        $vMethod = $vMethods[$s->getId()];
                        $ecCode = !empty($vMethod['est_carrier_code'])
                            ? $vMethod['est_carrier_code']
                            : (!empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendor->getCarrierCode());
                        $ocCode = !empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendor->getCarrierCode();
                        if ($ecCode!=$rates[$k1]->getCarrier()) {
                            continue;
                        }

                        // set free method if required
                        if ($freeMethods && $requests[$k]['request']->hasFreeMethodWeight() && $requests[$k]['request']->getFreeMethodWeight()==0
                            && in_array($s->getShippingCode(), $freeMethods)
                        ) {
                            $rates[$k1]->setPrice(0);
                        }

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
                        $details['methods'][$s->getShippingCode()]['id'] = $s->getShippingId();
                        $details['methods'][$s->getShippingCode()]['vendors'][$k] = $detail;
                    }
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
        foreach ($details['methods'] as $mCode=>$method) {
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
        foreach ($totals as $mCode=>$total) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('udropship');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($mCode);
            $method->setMethodTitle($total['title']);

            $method->setCost($total['cost']);
            $method->setPrice($this->getMethodPrice($total['price'], $mCode));

            $result->append($method);
        }

        Mage::dispatchEvent('udropship_carrier_collect_after', array('request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details));

        return $result;
    }

    public function getMethodPrice($cost, $method='')
    {
#if ($_SERVER['REMOTE_ADDR']=='24.20.46.76') { echo "<pre>"; print_r($this->_rawRequest->debug()); exit; }
        $freeMethods = explode(',', $this->getConfigData('free_method'));
        if (in_array($method, $freeMethods) &&
            $this->getConfigData('free_shipping_enable') &&
            $this->getConfigData('free_shipping_subtotal') <= $this->_rawRequest->getPackageValueWithDiscount())
        {
            $price = '0.00';
        } else {
            $price = $this->getFinalPriceWithHandlingFee($cost);
        }
        return $price;
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
}
