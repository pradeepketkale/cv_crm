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

		protected function _CreateCustomer($password, $company="", $city, $telephone, $fax="", $email, $prefix="", $firstname, $middlename="", $lastname, $suffix="", $taxvat="", $street1, $street2="", $postcode, $billing_region, $country_id, $storeId) {
		
				#require_once '../app/Mage.php';
				#$app=Mage::app();
				#Mage::register('isSecureArea', true);
				
				
				$customer = Mage::getModel('customer/customer');
				
				///make sure dob is mm/dd/yy
		
		$dob="5/6/88";
		$region = 0;
		#$region=34;//getRegionByZip($postcode);
		$regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($billing_region)->load();
			if ($regions) {
				 foreach($regions as $region) {
				   $region = $region->getId();
				 }
			} else {
					 $region = 0;
			}
		
		$street_r=array("0"=>$street1,"1"=>$street2);
		$group_id=1; ///double-check this 1 = general group
		$website_id=1;
		//$website_id=Mage::getModel('core/store')->load($storeId)->getWebsiteId();
				
		
		$default_billing="_item1";
		$index="_item1";
		
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
									"default_billing"=>$default_billing
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
									"default_billing"=>$default_billing
							);
		}		
		
		//print_r($customerData);
		
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
						
						
						$address = Mage::getModel('customer/address');
						$address->setData($addressData);
				
						/// We need set post_index for detect default addresses
						///pretty sure index is a 0 or 1
						$address->setPostIndex($index);
						$customer->addAddress($address);
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
//print_r($importData);
				 /*THIS IS FOR SETTING CUSTOM ORDER NUMBERS */
				 $orderTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
				 
				 if(isset($importData['store_id']) && isset($importData['order_id']) && $orderTypeId != "") {
					 $resource = Mage::getSingleton('core/resource');
					 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
					 $write = $resource->getConnection('core_write');
					 $read = $resource->getConnection('core_read');
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
					$customerId = $this->_CreateCustomer($importData['password'], "", $importData['billing_city'], $importData['billing_telephone'], $importData['billing_fax'], $importData['email'], $importData['billing_prefix'], $importData['billing_firstname'], $importData['billing_middlename'], $importData['billing_lastname'], $importData['billing_suffix'], $taxvat="", $importData['billing_street_full'], $billing_street_2, $importData['billing_postcode'], $importData['billing_region'], $importData['billing_country'], $importData['store_id']);
					
				$customer = Mage::getModel('customer/customer')->setData(array())->load($customerId);
				}
				#echo "CUSTYID: " . Mage::app()->getStore()->getStoreId();
				#echo "CUSTYID1: " . $customer->getStoreId();
				
				$products_ordered = explode('|',$importData['products_ordered']);
				
				foreach ($products_ordered as $data) {
					$parts = explode(':',$data);
					#$productId = $product->getIdBySku($parts[0]);
				  $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$parts[0]); 
					#print_r($product);
					#echo "ID: " . $product->getId();
					$add_products_array[$product->getId()]['qty'] = $parts[1];
					#$add_products_array array('738' => array('qty' => 1), '737'    => array('qty' => 2),)
				}
				
				#print_r($add_products_array);		
				
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
										),
								); 
					//print_r($orderData);
				if (!empty($orderData)) {
						// we have valid order data
						$this->_initSession($orderData['session']);
						try {
								$this->_processQuote($orderData);
								if (!empty($orderData['payment'])) {
										$this->_getOrderCreateModel()->setPaymentData($orderData['payment']);
										$this->_getOrderCreateModel()->getQuote()->getPayment()->addData($orderData['payment']);
								}

								$order1 = $this->_getOrderCreateModel()
										 ->importPostData($orderData['order'])
										 ->createOrder();
								#this is needed to not have orders repeat themselves. this is when you have items from previous order as part of new order
								Mage::getSingleton('adminhtml/session_quote')->clear();
								
								
								if(isset($importData['created_at'])) {
									$dateTime = strtotime($importData['created_at']);
									$write_qry2 = $write->query("UPDATE `".$prefix."sales_order` SET created_at = '". date ("Y-m-d H:i:s", $dateTime) ."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
								}
								
								/* CUSTOM ADDITION FOR CUSTOM PRICE OPTION [START] */
								foreach ($products_ordered as $data) {
										$parts = explode(':',$data);
										#$productId = $parts[0];
										#$producQTY = $parts[1];
										#$producCUSTOMPRICE = $parts[2];
										//print_r($parts); print_r($importData); 
										if(isset($parts[2]) && isset($importData['custom_order_total'])) { 	  
											//print_r($parts); print_r($importData); 
										$write_qry2 = $write->query("UPDATE `".$prefix."sales_order` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$importData['custom_order_total']."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$importData['custom_order_total']."' WHERE entity_id = '". $order1->getId() ."' AND store_id = '". $importData['store_id'] ."'");
										
										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_order_item` SET price = '".$parts[2]."', base_price = '".$parts[2]."', row_total = '".$importData['custom_order_total']."', base_row_total = '".$importData['custom_order_total']."' WHERE order_id = '". $order1->getId() ."'");
										
										$select_qry = $read->query("SELECT item_id FROM `".$prefix."sales_flat_order_item` WHERE order_id = '". $order1->getId() ."'");
										$newrowItemId = $select_qry->fetch();
										$item_id = $newrowItemId['item_id'];
										
										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_quote` SET base_subtotal = '".$importData['custom_order_total']."', base_grand_total = '".$importData['custom_order_total']."', subtotal = '".$importData['custom_order_total']."', grand_total = '".$importData['custom_order_total']."', subtotal_with_discount = '".$importData['custom_order_total']."', base_subtotal_with_discount = '".$importData['custom_order_total']."' WHERE entity_id = '". $item_id ."'");
										
										$write_qry2 = $write->query("UPDATE `".$prefix."sales_flat_quote_item` SET row_total_with_discount = '".$importData['custom_order_total']."', base_row_total = '".$importData['custom_order_total']."', row_total = '".$importData['custom_order_total']."', custom_price = '".$parts[2]."', original_custom_price = '".$parts[2]."' WHERE quote_id = '". $item_id ."'");
										
										}
								}
								/* CUSTOM ADDITION FOR CUSTOM PRICE OPTION [END] */
								
								
								
								if($importData['order_status']=="complete") {
									$order1->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE); 
									$order1->setState(Mage_Sales_Model_Order::STATE_COMPLETE, false); 
									$order1->addStatusToHistory($order1->getStatus(), '', false); 
									$order1->save();
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