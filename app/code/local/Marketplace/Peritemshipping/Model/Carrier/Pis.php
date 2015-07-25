<?php
class Marketplace_Peritemshipping_Model_Carrier_Pis extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

	protected $_code = 'pis';
     /**
     * Collects the shipping rates for local delivery from our setting in the admin.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		// Check if this method is active

		/*if (!$this->getConfigFlag('active'))
		{
			return false;
		}

		$shipPrice=0;
			//$items = Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection()->getItems();
			$this->_request = $request;
			$item1 = $this->_request->getAllItems();
			foreach( $item1 as $item )
			{
				$product = Mage::getModel('catalog/product')->load( $item->getProductId() );

				if(!empty($_POST['payment']['method']) && isset($_POST['payment']['method']))
				{
					if($_POST['payment']['method']=='ddpayment'){
						$shipcost = $product->getDdShippingamount();}
						else{$shipcost = $product->getShippingcost();}
				}
				else {$shipcost = $product->getShippingcost();}
				$qty = $item->getQty();
				$shipPrice+=$shipcost*$qty;
			}
		
		
		//adding the quotation Id for the order totals for logistic restrictions
		$object = new Mage_Adminhtml_Model_Session_Quote();
//		echo $SessionQuoteId = $object->getQuote()->getEntityId();
		$orderCost = $object->getQuote()->getGrandTotal()+$shipPrice;
		
//		$orderCost = $request->getData('package_value_with_discount')+$shipPrice;
		//The the destination Zip Code
		$toZipCode = $request->getDestPostcode();
		$foundValid = false;
		$AddmoreVendor = array();
		$shippingMethodsAvail='';
		$serviceZips= array();

		$items = $request->getAllItems();

		foreach( $items as $item )
		{
			$product = Mage::getModel('catalog/product')->load( $item->getProductId() );
			$is_vendorshipping = $product->getData('is_vendorshipping');

			if($is_vendorshipping==1)
			{
echo "if"; exit;
				$productVendorId = $product->getData('udropship_vendor');
				$carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
				foreach ($carrierInstances as $code => $carrier) {
					if($carrier->getConfigData('vendorpartner'))
					{
						$vendor= $carrier->getConfigData('vendorpartner');
						if($vendor===$productVendorId)
						{
							$AddmoreVendor[]= $code;
							break;
						}
					}
				}
			}
			else {
echo "else"; exit;
				if($shippingMethodsAvail=='')
				{$shippingMethodsAvail = Mage::getStoreConfig('carriers/pis/psc_method');}
			}
		}

		$optionsAvail =  array();
		if(!empty($shippingMethodsAvail)){$optionsAvail = explode("," , $shippingMethodsAvail);}
		$optionsAvail=array_merge($AddmoreVendor,$optionsAvail);
		$optionsAvail=array_unique($optionsAvail);



		//$tiercode = 'tier1zips';

		//$Codcodes = 'allowedcodzips';

		$carriercode ='tier1zips';


		if(!empty($_POST['payment']['method']) && isset($_POST['payment']['method']))
		{
			if($_POST['payment']['method']=='ig_cashondelivery'){
				$shipmentPaymentMethod ='ig_cashondelivery';
				$carriercode = 'allowedcodzips';}
				else{$shipmentPaymentMethod = '';}
		}


		foreach($optionsAvail as $key => $carrier){

			$flag = 1;

			$config[$carrier] = Mage::getStoreConfig('carriers/'.$carrier);

			if(isset($shipmentPaymentMethod) && trim($shipmentPaymentMethod)==='ig_cashondelivery'){
				if($config[$carrier]['allowedcodzips']) //&& $config[$carrier]['airwaybills']
				{
					$allowedcodzips = explode(',' , $config[$carrier]['allowedcodzips']);
					if(in_array($toZipCode,$allowedcodzips))  //searching the serviceable pincodes
					{
						$flag=$flag*1;
					}else{
						$flag=$flag*0;
					}
				}


				if(isset($config[$carrier]['maxordervalue']) && (!empty($config[$carrier]['maxordervalue']) || $config[$carrier]['maxordervalue']>0))
				{
					if($orderCost <= $config[$carrier]['maxordervalue'])
					{
						$flag=$flag*1;
					}else{
						$flag=$flag*0;
					}
				}
				if($flag){

//					error_log($carrier);
//					error_log($orderCost);
//					error_log($config[$carrier]['maxordervalue']);
					//						echo "<br>".$config[$carrier]['maxordervalue'];
					$foundValid=true;
					break;
				}

			}
			else{
				if($config[$carrier]['tier1zips']) //&& $config[$carrier]['airwaybills']
				{
					$allowedzips = explode(',' , $config[$carrier]['tier1zips']);
					if(in_array($toZipCode,$allowedzips))  //searching the serviceable pincodes
					{
						$flag=$flag*1;
					}else{
						$flag=$flag*0;
					}
				}

				if(isset($config[$carrier]['maxordervalue']) && (!empty($config[$carrier]['maxordervalue']) || $config[$carrier]['maxordervalue']>0))
				{
					if($orderCost <= $config[$carrier]['maxordervalue'])
					{
						$flag=$flag*1;
					}else{
						$flag=$flag*0;
					}
				}

				if($flag){
//					error_log($carrier);
//					error_log($orderCost);
//					error_log($config[$carrier]['maxordervalue']);
					$foundValid=true;
					break;
				}

			}


		}

		if(!$foundValid)
		{
			return false;
		}
		else
		{*/
			$shippingPrice=0;
			//$items = Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection()->getItems();
			$this->_request = $request;
			/*$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
			$subtotal = $totals["subtotal"]->getValue();
			$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
			$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
			$subtotal = Mage::helper('directory')->currencyConvert($subtotal, $currentCurrencyCode, $baseCurrencyCode);*/
			//$subtotal = 0;
			//if(($this->_request->getDestCountryId()!='IN') && ($subtotal>=5000))
			//	$shippingPrice = 0;
			//else{
				$counter = 1;
				$items = $this->_request->getAllItems();
				foreach( $items as $item )
				{
					$product = Mage::helper('catalog/product')->loadnew($item->getProductId());
					//$subtotal += $product->getPrice()*$item->getQty();
					//if($product->getOptionTablerate())
						//{
				                            /*if($item->getQty()>1)
				                               {
													///// Craftsvilla Comment By Amit Pitre On 18-06-2012 to calculate per item shipping cost for outside india.///////////////
													if($this->_request->getDestCountryId()!='IN')
														$shippingPrice += ($product->getIntershippingcost()+$product->getInterShippingTablerate()*($item->getQty()-1))/$counter;
							 						else
														$shippingPrice += ($product->getShippingcost()+$product->getShippingTablerate()*($item->getQty()-1));
						   						}
						   						else {
													if($this->_request->getDestCountryId()!='IN')
														$shippingPrice += ($product->getIntershippingcost()*$item->getQty())/$counter;
													else
														$shippingPrice += ($product->getShippingcost()*$item->getQty());
				                             	}
				                             }*/
				                             //else {
												if($this->_request->getDestCountryId()!='IN')
				                             		$shippingPrice += ($product->getIntershippingcost()*$item->getQty())/$counter;
												else
													$shippingPrice += ($product->getShippingcost()*$item->getQty());
				                             //}
											  //$counter++;
	/////////////////////////////////////////////////////// Craftsvilla Comment End ///////////////////////////////////////////////////////////////
		
					
					
					
					/*if(!empty($_POST['payment']['method']) && isset($_POST['payment']['method']))
					{
						if($_POST['payment']['method']=='ddpayment'){
							$shippingcost = $product->getDdShippingamount();}
							else{$shippingcost = $product->getShippingcost();}
					}
					else {$shippingcost = $product->getShippingcost();}
					$qty = $item->getQty();
					$shippingPrice+=$shippingcost*$qty;*/
				}
			//}
		//}
		///// Craftsvilla Comment By Amit Pitre On 13-07-2012 for shipping free outside india above Rs 5000.///////////////
		//if(($this->_request->getDestCountryId()!='IN') && ($subtotal>=5000))
		//	$shippingPrice = 0;

		//if we get to here we should have a valid $shippingPrice
		$result = Mage::getModel('shipping/rate_result');
		$method = Mage::getModel('shipping/rate_result_method');
		
		
		//add the handling fee if needed
		$shippingPrice += $this->getConfigData('handling_fee');

		$method->setCarrier('pis');
		$method->setCarrierTitle($this->getConfigData('title'));

		$shipping_method = "Delivery Fee(" .$shippingPrice . ")";

		$method->setMethod('pis');
		$method->setMethodTitle("$shipping_method");

		$method->setPrice(round($shippingPrice));
		$method->setCost(round($shippingPrice));
		$result->append($method);
		return $result;
	}

	/**
     * Get allowed shipping methods
     *
     * @return array
     */
	public function getAllowedMethods()
	{
		//we only have one method so just return the name from the admin panel
		return array('pis' => $this->getConfigData('name'));
	}

	public function isTrackingAvailable(){
		return true;
	}

	public function getTrackingInfo($tracking_number)
	{
		$tracking_result = $this->getTracking($tracking_number);

		if ($tracking_result instanceof Mage_Shipping_Model_Tracking_Result)
		{
			if ($trackings = $tracking_result->getAllTrackings())
			{
				return $trackings[0];
			}
		}
		elseif (is_string($tracking_result) && !empty($tracking_result))
		{
			return $tracking_result;
		}

		return false;
	}

	public function getTracking($tracking_number)
	{
		$tracking_result = Mage::getModel('shipping/tracking_result');

		$tracking_status = Mage::getModel('shipping/tracking_result_status');
		$tracking_status->setCarrier($this->_code);
		$tracking_status->setCarrierTitle($this->getConfigData('title'));
		$tracking_status->setTracking($tracking_number);
		//Getting xml of shippment bu curl
		$path = $this->getConfigData('gateway_url').'?ShipmentNumber='.$tracking_number;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$path);
		curl_setopt($ch, CURLOPT_FAILONERROR,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$retValue = curl_exec($ch);
		curl_close($ch);
		$xmlarray = new SimpleXMLElement($retValue);
		$tracking_status->addData(
		array(
		//'status'=>'<a target="_blank" href="'.$this->getConfigData('gateway_url').'?ShipmentNumber='.$tracking_number.'">Track your parcel</a><br>'
		'status'=>'<b>Current Status: </b>'.$xmlarray->HAWBDetails->CurrentStatus.'<br>'.
		'<b>Origin City: </b>'.$xmlarray->HAWBDetails->HAWBOriginEntity.'<br>'.
		'<b>Origin Phone: </b>'.$xmlarray->HAWBDetails->HAWBOriginEntityPhone.'<br>'.
		'<b>Destination City: </b>'.$xmlarray->HAWBDetails->HAWBDestEntity.'<br>'.
		'<b>Destination Phone: </b>'.$xmlarray->HAWBDetails->HAWBDestEntityPhone.'<br>'.
		'<b>Shipper Name: </b>'.$xmlarray->HAWBDetails->ShipperName.'<br>'.
		'<b>Sent By: </b>'.$xmlarray->HAWBDetails->SentBy.'<br>'.
		'<b>Shipper Address: </b>'.$xmlarray->HAWBDetails->ShipperAddress.'<br>'.
		'<b>Shipper Reference: </b>'.$xmlarray->HAWBDetails->ShipperReference.'<br>'.
		'<b>Consignee Name: </b>'.$xmlarray->HAWBDetails->ConsigneeName.'<br>'.
		'<b>Attn Of: </b>'.$xmlarray->HAWBDetails->AttnOf.'<br>'.
		'<b>Consignee Address: </b>'.$xmlarray->HAWBDetails->ConsigneeAddress.'<br>'.
		'<b>Commodity Description: </b>'.$xmlarray->HAWBDetails->CommodityDescription.'<br>'
		)
		);
		$tracking_result->append($tracking_status);

		return $tracking_result;
	}

}
