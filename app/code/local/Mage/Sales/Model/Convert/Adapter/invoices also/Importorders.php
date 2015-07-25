<?php

/**

 * ImportOrders.php

 * CommerceThemes @ InterSEC Solutions LLC.

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the EULA

 * that is bundled with this package in the file LICENSE.txt.

 * It is also available through the world-wide-web at this URL:

 * http://www.commercethemes.com/LICENSE-M1.txt

 *

 * @category   Orders

 * @package    Importorders

 * @copyright  Copyright (c) 2003-2009 CommerceThemes @ InterSEC Solutions LLC. (http://www.commercethemes.com)

 * @license    http://www.commercethemes.com/LICENSE-M1.txt

 */ 

 

class Mage_Sales_Model_Convert_Adapter_Importorders

    extends Mage_Catalog_Model_Convert_Adapter_Product

{



		/**

     * Retrieve order create model

     *

     * @return  Mage_Adminhtml_Model_Sales_Order_Create

     */

    protected function _getOrderCreateModel()

    {

        return Mage::getSingleton('adminhtml/sales_order_create');

    }



    /**

     * Retrieve session object

     *

     * @return  Mage_Adminhtml_Model_Session_Quote

     */

    protected function _getSession()

    {

        return Mage::getSingleton('adminhtml/session_quote');

    }



    /**

     * Initialize order creation session data

     *

     * @param   array $data

     * @return  Mage_Adminhtml_Sales_Order_CreateController

     */

    protected function _initSession($data)

    {

        /**

         * Identify customer

         */

        if (!empty($data['customer_id'])) {

            $this->_getSession()->setCustomerId((int) $data['customer_id']);

        }



        /**

         * Identify store

         */

        if (!empty($data['store_id'])) {

            $this->_getSession()->setStoreId((int) $data['store_id']);

        }



        return $this;

    }



    /**

     * Processing quote data

     *

     * @param   array $data

     * @return  Yournamespace_Yourmodule_IndexController

     */

    protected function _processQuote($data = array())

    {

        /**

         * Saving order data

         */

        if (!empty($data['order'])) {

            $this->_getOrderCreateModel()->importPostData($data['order']);

        }



        /**

         * init first billing address, need for virtual products

         */

        $this->_getOrderCreateModel()->getBillingAddress();

        $this->_getOrderCreateModel()->setShippingAsBilling(true);



        /**

         * Adding products to quote from special grid and

         */

        if (!empty($data['add_products'])) {

            $this->_getOrderCreateModel()->addProducts($data['add_products']);

        }



        /**

         * Collecting shipping rates

         */

        $this->_getOrderCreateModel()->collectShippingRates();



        /**

         * Adding payment data

         */

        if (!empty($data['payment'])) {

            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment']);

        }



        $this->_getOrderCreateModel()

             ->initRuleData()

             ->saveQuote();



        if (!empty($data['payment'])) {

            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment']);

        }



        return $this;

    }



		protected function _CreateCustomer($password, $company="", $city, $telephone, $fax="", $email, $prefix="", $firstname, $middlename="", $lastname, $suffix="", $taxvat="", $street1, $street2="", $postcode, $billing_region, $country_id, $storeId, $shipping_prefix="", $shipping_firstname, $shipping_middlename="", $shipping_lastname, $shipping_suffix="", $shipping_taxvat="", $shipping_street1, $shipping_street2="", $shipping_postcode, $shipping_region, $shipping_country_id, $shipping_city, $shipping_telephone, $shipping_fax) {

		

				#require_once '../app/Mage.php';

				#$app=Mage::app();

				#Mage::register('isSecureArea', true);

				

				

				$customer = Mage::getModel('customer/customer');

				

				///make sure dob is mm/dd/yy

		

		$dob="5/6/88";

		$region = 0;
		$region1 = 0;

		#$region=34;//getRegionByZip($postcode);

		$regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($billing_region)->load();

			if ($regions) {

				 foreach($regions as $region) {

				   $region = $region->getId();

				 }

			} else {

					 $region = 0;

			}

		$shipping_regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($shipping_region)->load();

			if ($shipping_regions) {

				 foreach($shipping_regions as $regions) {

				   $region1 = $regions->getId();

				 }

			} else {

					$region1 = 0;

			}

		

		$street_r=array("0"=>$street1,"1"=>$street2);
		$shipping_street_r=array("0"=>$shipping_street1,"1"=>$shipping_street2);

		$group_id=1; ///double-check this 1 = general group

		$website_id=1;

		//$website_id=Mage::getModel('core/store')->load($storeId)->getWebsiteId();

				

		

		$default_billing="_item1";
		$default_shipping="_item2";

		$index="_item1";
		$index2="_item2";

		

		///end hard-coding//*/

		

		$salt="XD";

		//$hash=md5($salt . $password).":$salt";

		$hash="";

		if($password !="") {

			$customerData=array(

									"prefix"=>$prefix,

									"firstname"=>$firstname,

									"middlename"=>$middlename,

									"lastname"=>$lastname,

									"suffix"=>$suffix,

									"email"=>$email,

									"group_id"=>$group_id,

									"password_hash"=>$hash,

									"taxvat"=>$taxvat,

									"website_id"=>$website_id,

									"password"=>$password,

									"default_billing"=>$default_billing,

									"default_shipping"=>$default_shipping

							);

		} else {

			$customerData=array(

									"prefix"=>$prefix,

									"firstname"=>$firstname,

									"middlename"=>$middlename,

									"lastname"=>$lastname,

									"suffix"=>$suffix,

									"email"=>$email,

									"group_id"=>$group_id,

									"taxvat"=>$taxvat,

									"website_id"=>$website_id,

									"default_billing"=>$default_billing,

									"default_shipping"=>$default_shipping

							);

		}		

						$customer->addData($customerData); ///make sure this is enclosed in arrays correctly

				

						$addressData=array(

								"prefix"=>$prefix,

								"firstname"=>$firstname,

								"middlename"=>$middlename,

								"lastname"=>$lastname,

								"suffix"=>$suffix,

								"company"=>$company,

								"street"=>$street_r,

								"city"=>$city,

								"region"=>$region,

								"country_id"=>$country_id,

								"postcode"=>$postcode,

								"telephone"=>$telephone,

								"fax"=>$fax

						);
						//added shipping address
						$addressData2=array(

								"prefix"=>$shipping_prefix,

								"firstname"=>$shipping_firstname,

								"middlename"=>$shipping_middlename,

								"lastname"=>$shipping_lastname,

								"suffix"=>$shipping_suffix,

								"company"=>$company,

								"street"=>$shipping_street_r,

								"city"=>$shipping_city,

								"region"=>$region1,

								"country_id"=>$shipping_country_id,

								"postcode"=>$shipping_postcode,

								"telephone"=>$shipping_telephone,

								"fax"=>$shipping_fax

						);

						

						$address = Mage::getModel('customer/address');
						$address->setData($addressData);


						/// We need set post_index for detect default addresses

						///pretty sure index is a 0 or 1

						$address->setPostIndex($index);

						//added shipping address
						$shippingaddress = Mage::getModel('customer/address');
						//$customAddress = new Mage_Customer_Model_Address();
						$shippingaddress->setData($addressData2);
						
						$shippingaddress->setPostIndex($index2);
						$shippingaddress->setIsDefaultShipping(1);
						$shippingaddress->setSaveInAddressBook(1);
							
						$customer->addAddress($address);
						//added shipping address
						$customer->addAddress($shippingaddress);

						$customer->setIsSubscribed(false);


						///make sure password is encrypted

						#$customer->setPassword($password);

						#$customer->setForceConfirmed(true);

						

						///adminhtml_customer_prepare_save

						if($password !="") {

						//make sure password is encrypted

							$customer->setPassword($password);

							$customer->setForceConfirmed(true);

						} else {

							$customer->setPassword($customer->generatePassword(8));

						}

						///adminhtml_customer_prepare_save

						$customer->save();

						$customer->sendNewAccountEmail();

		

				///adminhtml_customer_save_after

				$customerId=$customer->getId();

		

				Mage::log("customerId:$customerId");

		

				return $customerId;

		} 

		

		public function _getStoreById($storeId)

    {

        if (is_null($this->_stores)) {

            $this->_stores = Mage::app()->getStores(true);

        }

        if (isset($this->_stores[$storeId])) {

            return $this->_stores[$storeId];

        }

        return false;

    }

    /**

     * Import Orders model

     *

     * @var Mage_Sales_Model_Convert_Adapter

     */

		 

    	public function saveRow( array $importData )

		{

				 /*THIS IS FOR SETTING CUSTOM ORDER NUMBERS */

				 $orderTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
 				 $resource = Mage::getSingleton('core/resource');

				 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 

				 $write = $resource->getConnection('core_write');

				 $read = $resource->getConnection('core_read');
				 

				 if(isset($importData['store_id']) && isset($importData['order_id']) && $orderTypeId != "" && $importData['order_id'] !="") {

					

					 $finalorderID = $importData['order_id'] - 1;

					 $write_qry = $write->query("UPDATE `".$prefix."eav_entity_store` SET increment_last_id = ".$finalorderID." WHERE store_id = ". $importData['store_id'] ." AND entity_type_id = ". $orderTypeId ."");

				 }

				 /* END CUSTOM ORDER NUMBER */

				 

				$add_products_array = array();

				$store = $this->getStoreById($importData['store_id']);

				

				if(isset($importData['billing_street_2'])) {

					$billing_street_2 = $importData['billing_street_2'];

				} else {

					$billing_street_2="";

				}
				//shipping
				if(isset($importData['shipping_street_2'])) {

					$shipping_street_2 = $importData['shipping_street_2'];

				} else {

					$shipping_street_2="";

				}

				/* HAD SOME ISSUE HERE FINDING PROPER WEBSITE IDs .. looks like latest is ok */

				#$valueid = $store->getData('website_id');

				#$valueid = Mage::app()->getStore()->getWebsiteId();

				$valueid = Mage::getModel('core/store')->load($importData['store_id'])->getWebsiteId();

				#$website = $this->getWebsiteById($valueid);

				//DUPLICATE CUSTOMERS are appearing after import this value above is likely not found.. so we have a little check here

				if($valueid < 1) {

					$valueid =1;

				}

				// look up customer

				$customer = Mage::getModel('customer/customer')

						->setWebsiteId($valueid)

						->loadByEmail($importData['email']);

				#echo "VALUEID: " . $valueid;

				#echo "CUSTYID: " . $customer->getId();

				#print_r($importData);

				

				if ($customer->getId()) {

					if(isset($importData['customer_id']) && $importData['customer_id'] !="") {

			  		$customerId = $importData['customer_id'];

					} else {

			  		$customerId = $customer->getId();

					}

					

				} else {

					
					$customerId = $this->_CreateCustomer($importData['password'], "", $importData['billing_city'], $importData['billing_telephone'], $importData['billing_fax'], $importData['email'], $importData['billing_prefix'], $importData['billing_firstname'], $importData['billing_middlename'], $importData['billing_lastname'], $importData['billing_suffix'], $taxvat="", $importData['billing_street_full'], $billing_street_2, $importData['billing_postcode'], $importData['billing_region'], $importData['billing_country'], $importData['store_id'], $importData['shipping_prefix'], $importData['shipping_firstname'], $importData['shipping_middlename'], $importData['shipping_lastname'], $importData['shipping_suffix'], $taxvat="", $importData['shipping_street_full'], $shipping_street_2, $importData['shipping_postcode'], $importData['shipping_region'], $importData['shipping_country'], $importData['shipping_city'], $importData['shipping_telephone'], $importData['shipping_fax']);

					

				$customer = Mage::getModel('customer/customer')->setData(array())->load($customerId);

				}

				#echo "CUSTYID: " . Mage::app()->getStore()->getStoreId();

				#echo "CUSTYID1: " . $customer->getStoreId();

				

				$products_ordered = explode('|',$importData['products_ordered']);

				

				foreach ($products_ordered as $data) {

					$parts = explode(':',$data);

					#$productId = $product->getIdBySku($parts[0]);

				   //$product = Mage::getModel('catalog/product')->loadBySku($parts[0]); 
				  $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$parts[0]); 

					#print_r($product);

					#echo "ID: " . $product->getId();
					#echo "Type: " . $product->getTypeId(). "<br/>";;
					// For configurable product
					if($product->getTypeId() == "configurable") {
						$config = $product->getTypeInstance(true);
						#print_r($config->getConfigurableAttributesAsArray($product));
						foreach($config->getConfigurableAttributesAsArray($product) as $attributes)
						 { 
							   # print_r($attributes);
								#echo $attributes["label"] . "<br/>";
								#echo "ID: " . $attributes["attribute_id"] . "<br/>";
					
								foreach($attributes["values"] as $values)
								 {
								 	#print_r($values);
									#echo $values["label"] . "<br/>";
									if($parts[2] == $values["label"]) {
										#echo "2: ". $values["value_index"] . "<br/>";
										$super_attribute_order_values[$attributes["attribute_id"]] = $values["value_index"];
									}
									if(isset($parts[3])) {
										if($parts[3] == $values["label"]) {
											#echo "3: ". $values["value_index"] . "<br/>";
											$super_attribute_order_values[$attributes["attribute_id"]] = $values["value_index"];
										}
									}
								 }
								// its attributeID => attribute value ID
								/*
								$super_attribute_order_values = array(
									80 =>8,
									122 =>4
								);
								*/
						 }
						$add_products_array[$product->getId()]['super_attribute'] = $super_attribute_order_values;
					}
					$add_products_array[$product->getId()]['qty'] = $parts[1];
					#$add_products_array array('738' => array('qty' => 1), '737'    => array('qty' => 2),)

				}

				#print_r($add_products_array);
				//custom shipping added
				if(method_exists($customer, 'getDefaultShippingAddress')) { 
					$final_shipping_entity_id = $customer->getDefaultShippingAddress()->getEntityId();
					$final_shipping_prefix = $customer->getDefaultShippingAddress()->getPrefix();
					$final_shipping_firstname = $customer->getDefaultShippingAddress()->getFirstname();
					$final_shipping_middlename = $customer->getDefaultShippingAddress()->getMiddlename();  
					$final_shipping_lastname = $customer->getDefaultShippingAddress()->getLastname();  
					$final_shipping_suffix = $customer->getDefaultShippingAddress()->getSuffix();  
					$final_shipping_company = $customer->getDefaultShippingAddress()->getCompany();  
					$final_shipping_street = $customer->getDefaultShippingAddress()->getStreet();  
					$final_shipping_city = $customer->getDefaultShippingAddress()->getCity();   
					$final_shipping_countryid = $customer->getDefaultShippingAddress()->getCountryId(); 
					$final_shipping_region = $customer->getDefaultShippingAddress()->getRegion();  
					$final_shipping_regionid = $customer->getDefaultShippingAddress()->getRegionId();
					$final_shipping_postcode = $customer->getDefaultShippingAddress()->getPostcode(); 
					$final_shipping_telephone = $customer->getDefaultShippingAddress()->getTelephone();
					$final_shipping_fax = $customer->getDefaultShippingAddress()->getFax();  
				} else { 
					#echo "BILLING";
					$final_shipping_entity_id = $customer->getDefaultBillingAddress()->getEntityId(); 
					$final_shipping_prefix = $customer->getDefaultBillingAddress()->getPrefix();
					$final_shipping_firstname = $customer->getDefaultBillingAddress()->getFirstname();
					$final_shipping_middlename = $customer->getDefaultBillingAddress()->getMiddlename();
					$final_shipping_lastname = $customer->getDefaultBillingAddress()->getLastname();
					$final_shipping_suffix = $customer->getDefaultBillingAddress()->getSuffix();
					$final_shipping_company = $customer->getDefaultBillingAddress()->getCompany();
					$final_shipping_street = $customer->getDefaultBillingAddress()->getStreet();
					$final_shipping_city = $customer->getDefaultBillingAddress()->getCity();
					$final_shipping_countryid = $customer->getDefaultBillingAddress()->getCountryId();
					$final_shipping_region = $customer->getDefaultBillingAddress()->getRegion();
					$final_shipping_regionid = $customer->getDefaultBillingAddress()->getRegionId();
					$final_shipping_postcode = $customer->getDefaultBillingAddress()->getPostcode();
					$final_shipping_telephone = $customer->getDefaultBillingAddress()->getTelephone();
					$final_shipping_fax = $customer->getDefaultBillingAddress()->getFax();
				}		

				$orderData = array(

										'session'       => array(

												'customer_id'   => $customerId,

												#'store_id'      => $customer->getStoreId(),

												'store_id'      => $importData['store_id'],

										),

										'payment'       => array(

												'method'    => $importData['payment_method'],

												'po_number' => (string) $importData['order_id'],

										),

										// 123456 denotes the product's ID value

										'add_products'  => $add_products_array,

										'order'         => array(

												'currency'  => 'USD',

												'account'   => array(

														'group_id'  => $customer->getGroupId(),

														'email'     => (string) $customer->getEmail(),

												),

												'comment'           => array('customer_note' => 'API ORDER'),

												'send_confirmation' => 1,

												'shipping_method'   => $importData['shipping_method'],

												'billing_address'   => array(

														'customer_address_id' => $customer->getDefaultBillingAddress()->getEntityId(),

														'prefix'             => $customer->getDefaultBillingAddress()->getPrefix(),

														'firstname'           => $customer->getDefaultBillingAddress()->getFirstname(),

														'middlename'          => $customer->getDefaultBillingAddress()->getMiddlename(),

														'lastname'            => $customer->getDefaultBillingAddress()->getLastname(),

														'suffix'             => $customer->getDefaultBillingAddress()->getSuffix(),

														'company'              => $customer->getDefaultBillingAddress()->getCompany(),

														'street'               => $customer->getDefaultBillingAddress()->getStreet(),

														'city'                   => $customer->getDefaultBillingAddress()->getCity(),

														'country_id'           => $customer->getDefaultBillingAddress()->getCountryId(),

														'region'               => $customer->getDefaultBillingAddress()->getRegion(),

														'region_id'           => $customer->getDefaultBillingAddress()->getRegionId(),

														'postcode'               => $customer->getDefaultBillingAddress()->getPostcode(),

														'telephone'           => $customer->getDefaultBillingAddress()->getTelephone(),

														'fax'                   => $customer->getDefaultBillingAddress()->getFax(),

												),
												
												'shipping_address'  => array(

														'customer_address_id' => $final_shipping_entity_id,

														'prefix'             =>  $final_shipping_prefix,

														'firstname'           => $final_shipping_firstname,

														'middlename'          => $final_shipping_middlename,

														'lastname'            => $final_shipping_lastname,

														'suffix'             => $final_shipping_suffix,

														'company'              => $final_shipping_company,

														'street'               => $final_shipping_street,

														'city'                   => $final_shipping_city,

														'country_id'           => $final_shipping_countryid,

														'region'               => $final_shipping_region,

														'region_id'           => $final_shipping_regionid,

														'postcode'               => $final_shipping_postcode,

														'telephone'           => $final_shipping_telephone,

														'fax'                   => $final_shipping_fax,

												),

										),

								); 

					

				if (!empty($orderData)) {

						// we have valid order data

						$this->_initSession($orderData['session']);

						try {

								$this->_processQuote($orderData);

								if (!empty($orderData['payment'])) {

										$this->_getOrderCreateModel()->setPaymentData($orderData['payment']);

										$this->_getOrderCreateModel()->getQuote()->getPayment()->addData($orderData['payment']);

								}


								try {
								$order1 = $this->_getOrderCreateModel()
										 ->importPostData($orderData['order'])
										 ->createOrder();
								} catch (Exception $e){
									echo "ERROR Saving Order: " . $e->getMessage();
									#Mage::throwException(Mage::helper('catalog')->__('Order saving error: %s', $e->getMessage()));
									Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);
								}
								#this is needed to not have orders repeat themselves. this is when you have items from previous order as part of new order
								Mage::getSingleton('adminhtml/session_quote')->clear();

								

								

							
								if(isset($importData['created_at'])) {

									$dateTime = strtotime($importData['created_at']);
									
										
										if(Mage::getVersion() > '1.4.0.1') {
											try {
												$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."', updated_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												$write_qry3 = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."', updated_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												$write_qry4 = $write->query("UPDATE `".$prefix."sales_flat_order_item` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."', updated_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE order_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												$write_qry4 = $write->query("UPDATE `".$prefix."sales_flat_order_status_history` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE parent_id = '". $order1->getId() ."'");
												
												//echo "SQL: UPDATE `".$prefix."sales_flat_order` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'";
												
											} catch (Exception $e){
												echo "ERROR: " . $e->getMessage();
												#Mage::throwException(Mage::helper('catalog')->__('Order saving error: %s', $e->getMessage()));
												Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);
											}
										} else {
											try {
												$write_qry2 = $write->query("UPDATE `".$prefix."sales_order` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
											} catch (Exception $e){
												echo "ERROR: " . $e->getMessage();
												#Mage::throwException(Mage::helper('catalog')->__('Order saving error: %s', $e->getMessage()));
												Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);
											}
										}

									
								}

								//TJR Adding invoice creation
                                $itemQty = array();

								/* CUSTOM ADDITION FOR CUSTOM PRICE OPTION [START] */

								foreach ($products_ordered as $data) {

										$parts = explode(':',$data);

										#$productId = $parts[0];

										#$producQTY = $parts[1];

										#$producCUSTOMPRICE = $parts[2];

										if(isset($parts[2]) && isset($importData['custom_order_total'])) { 	  

											
										//this is for setting correct subtotal + base shipping 
										#$customordersubtotalamt = $importData['custom_order_total'] - $order1->getBaseShippingAmount();
										#$customordergrandtotalamt = $importData['custom_order_total'] + $order1->getBaseShippingAmount();
										
										if(isset($importData['shipping_amount'])) {
										$customordergrandtotalamt = number_format($importData['custom_order_total'],2) + number_format($importData['shipping_amount'],2);									
										} else {
										$customordergrandtotalamt = number_format($importData['custom_order_total'],2) + number_format($order1->getBaseShippingAmount(),2);
										}
										
										if(Mage::getVersion() > '1.4.0.1') {
												try {
											//$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$importData['custom_order_total']."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$importData['custom_order_total']."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
											$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$customordergrandtotalamt."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$customordergrandtotalamt."', base_shipping_amount = '". $importData['shipping_amount'] ."', shipping_amount = '". $importData['shipping_amount'] ."', shipping_incl_tax = '". $importData['shipping_amount'] ."', base_shipping_incl_tax = '". $importData['shipping_amount'] ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
													} catch (Exception $e){
													echo "ERROR: " . $e->getMessage();
													#Mage::throwException(Mage::helper('catalog')->__('Order saving error: %s', $e->getMessage()));
													Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);
													}
										
										} else {
											//$write_qry2 = $write->query("UPDATE `".$prefix."sales_order` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$importData['custom_order_total']."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$importData['custom_order_total']."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
											$write_qry2 = $write->query("UPDATE `".$prefix."sales_order` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$customordergrandtotalamt."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$customordergrandtotalamt."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
										}

										//UPDATE FOR SALES GRID VIEW -- sales -> orders
										$write_qry3 = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET base_grand_total = '".$customordergrandtotalamt."', grand_total = '".$customordergrandtotalamt."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");

										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order_item` SET price = '".$parts[2]."', base_price = '".$parts[2]."', row_total = '".$importData['custom_order_total']."', base_row_total = '".$importData['custom_order_total']."' WHERE order_id = '". $order1->getId() ."'");

										

										$select_qry = $read->query("SELECT item_id FROM `".$prefix."sales_flat_order_item` WHERE order_id = '". $order1->getId() ."'");

										$newrowItemId = $select_qry->fetch();

										$item_id = $newrowItemId['item_id'];
                                        //TJR: Track the quantities ordered. Need the item_id as the key. Silly.
                                        $itemQty[$item_id]=$parts[1];
										

										//$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_quote` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$importData['custom_order_total']."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$importData['custom_order_total']."', subtotal_with_discount = '".$importData['custom_order_total']."', base_subtotal_with_discount = '".$importData['custom_order_total']."' WHERE entity_id = '". $item_id ."'");
										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_quote` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$customordergrandtotalamt."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$customordergrandtotalamt."', subtotal_with_discount = '".$importData['custom_order_total']."', base_subtotal_with_discount = '".$importData['custom_order_total']."' WHERE entity_id = '". $item_id ."'");

										

										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_quote_item` SET row_total_with_discount = '".$importData['custom_order_total']."', base_row_total = '".$importData['custom_order_total']."', row_total = '".$importData['custom_order_total']."', custom_price = '".$parts[2]."', original_custom_price = '".$parts[2]."' WHERE quote_id = '". $item_id ."'");

										

										}

								}


                            //Create a new Invoice for the order
                            $invoice = $order1->prepareInvoice($itemQty);
                            $invoice->register();
                            try {
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder())
                                    ->save();


                            } catch (Mage_Core_Exception $e) {
                                Mage::log("failed to create an invoice");
                                Mage::log($e->getMessage());
                                Mage::log($e->getTraceAsString());
                            }


/* CUSTOM ADDITION FOR CUSTOM PRICE OPTION [END] */

								//CUSTOM SALES REP FIELD
								//http://www.magentocommerce.com/magento-connect/jlevi/extension/6412/sales_rep_commission_manager
								/*
								if(isset($importData['order_sales_rep']) && $importData['order_sales_rep'] != "") {
									
									$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order` SET salesrep = '".$importData['order_sales_rep']."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												
								}
								*/
								/* CUSTOM ADDITION FOR CUSTOM PRICE OPTION [END] */
								if($importData['order_status']=="complete" || $importData['order_status']=="Complete") {
									
									if(Mage::getVersion() > '1.4.0.1') {
											try {
												$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order` SET state = 'complete', status = 'complete' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												$write_qry3 = $write->query("UPDATE `".$prefix."sales_flat_order_grid` SET status = 'complete' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
												
												$write_qry3 = $write->query("UPDATE `".$prefix."sales_flat_order_status_history` SET status = 'complete' WHERE parent_id = '". $order1->getId() ."'");
												
											} catch (Exception $e){
												echo "ERROR: " . $e->getMessage();
												#Mage::throwException(Mage::helper('catalog')->__('Order saving error: %s', $e->getMessage()));
												Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);
											}
									
									} else {
										$order1->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE); 
										$order1->setState(Mage_Sales_Model_Order::STATE_COMPLETE, false); 
										$order1->addStatusToHistory($order1->getStatus(), '', false); 
										$order1->save();
									}

								}
								

								$this->_getSession()->clear();

								Mage::unregister('rule_data');

								Mage::log('Order Successfull', Zend_Log::INFO);

						}

						catch (Exception $e){

								Mage::log(sprintf('Order saving error: %s', $e->getMessage()), Zend_Log::ERR);

						}

				}

		}



}