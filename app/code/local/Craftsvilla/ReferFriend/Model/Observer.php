<?php
class Craftsvilla_ReferFriend_Model_Observer
{	
	public function hookToOrderSaveEvent($observer)
	{
		if(Mage::registry('sales_order_save_commit_observer_executed')){
        	return $this; //this method has already been executed once in this request (see comment below)
    	}
		$order = $observer->getOrder();
		$cust = $order->getCustomerId();
		$custEmail = $order->getCustomerEmail();
		$orderState = $order->getState();
		
		$query = "SELECT referral_parent_id,referral_purchase_status,referral_code FROM referfriend_referral WHERE refer_id = '".$cust."' and referral_register_status = 1 and referral_purchase_status = 0";
		$data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
		
		$customer_id = $data[0]['referral_parent_id'];
		$refercode = $data[0]['referral_code'];
		$customer_data = Mage::getModel('customer/customer')->load($customer_id);
		$senderEmail =$customer_data->getEmail();
		$senderName =$customer_data->getName();
		
		if($orderState == 'complete' && !empty($data) )
		{
			$referral_model = Mage::getModel('referfriend/referral');
			
			$write = Mage::getSingleton('core/resource')->getConnection('mymod_write');
			$voucher = $this->getVoucher($customer_id,'Refer Friend');
			$voucherCode = $voucher['couponCode'];
			$query = "SELECT coupon_id FROM salesrule_coupon WHERE code = '".$voucherCode."'";	
			$datacoupon = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
			$voucherId = $datacoupon[0]['coupon_id'];
			$voucherAmount = round($voucher['discount_amount'],2);
			$expiry = $voucher['expiry'];
			
			$sendemail = $referral_model->sendVoucherMail($senderEmail,$senderName,$custEmail,$voucherCode,$voucherAmount,$expiry);
			$update_query = "UPDATE referfriend_referral SET referral_purchase_status='1',coupon_id = $voucherId WHERE refer_id = '".$cust."' and referral_register_status = 1";
			$write->query($update_query);
		}
		
		Mage::register('sales_order_save_commit_observer_executed',true);
		return $this;
	}
	
	function generateUniqueId($length = null)
    {
        $rndId = crypt(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId));
        $rndId = str_replace(array(".", "$"),"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));
            if (!is_null($rndId)){
            	return strtoupper(substr($rndId, 0, $length));
            }
        return strtoupper($rndId);
    }
	
	public function getVoucher($customer_id,$coupon_type)
    {
		$query = "SELECT rule_id,discount_amount,conditions_serialized,to_date FROM salesrule WHERE name = '".$coupon_type."'";	
		$data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
		$ruleId = $data[0]['rule_id'];
		
		$couponCode=$this->generateUniqueId(8);
		$discount_amount = $data[0]['discount_amount'];
		
		$today_plus_1_year = $data[0]['to_date'];
		if($ruleId > 0)
		{	
			$data = Mage::getSingleton('core/resource')->getConnection('core_write');
			$insLastData = array('rule_id' => $ruleId,'customer_id' => $customer_id, 'times_used' => 0);
			$result = $data->insert('salesrule_customer', $insLastData);
			
			$insCouponData = array('rule_id' => $ruleId, 'code' => $couponCode, 'usage_limit' => 1, 'usage_per_customer' => 1, 'times_used' => 0, 'expiration_date' => $today_plus_1_year);
			$data->insert('salesrule_coupon', $insCouponData);
		}
		$voucher_data = array('couponCode' => $couponCode, 'discount_amount' => $discount_amount, 'expiry' => $today_plus_1_year);
		return $voucher_data;
    }

    public function hookToCustomerSave($observer)
	{
		if(Mage::registry('customer_save_observer_executed')){
        	return $this; //this method has already been executed once in this request (see comment below)
    	}

		$customer = $observer->getCustomer();
		$customer_id = $customer->getId();
		
		//added for telephone number in registration page
		$address = Mage::getModel('customer/address');
		/* @var $addressForm Mage_Customer_Model_Form */
		$addressForm = Mage::getModel('customer/form');
		$addressForm->setFormCode('customer_register_address')
		->setEntity($address);
		
		$addressData    = $addressForm->extractData(Mage::app()->getFrontController()->getRequest(), 'address', false);
		$address->setId(null)
		->setIsDefaultBilling(Mage::app()->getFrontController()->getRequest()->getParam('default_billing', true))//changed false to true by amit
		->setIsDefaultShipping(Mage::app()->getFrontController()->getRequest()->getParam('default_shipping', true));//changed false to true by amit
		$addressForm->compactData($addressData);
		$customer->addAddress($address);
		
		$customerEmail = $customer->getEmail();
		$insCookieData = array();
		$mymodRead = Mage::getSingleton('core/resource')->getConnection('refer_read');		
		$mymodWrite = Mage::getSingleton('core/resource')->getConnection('refer_write');
		$w = Mage::getSingleton('core/resource')->getConnection('core_write');
		$referCode = Mage::app()->getFrontController()->getRequest()->getParam('refer_code');
		if($referCode!="")
		{
			$utm_source="Refer Email";
			$utm_medium="Refer Friend";
			$utm_campaign="Refer Friend";
			
			$insCookieData = array('entity_id'=>$customer_id, 'utm_source'=>$utm_source, 'utm_campaign'=>$utm_campaign,'utm_medium'=>$utm_medium);
			$query = "SELECT referfriend_referral_id FROM  `referfriend_referral` WHERE  `referral_email` =  '".$customerEmail."' AND  `referral_code` =  '".$referCode."' AND  `referral_register_status` = 0";
			$data = $mymodRead->fetchAll($query);
			if(empty($data)) {
				$referfriend_referral_id = $data[0]['referfriend_referral_id'];
				$customername = $customer->getName();
				$cust_select = "SELECT ce.entity_id,ce.email FROM customer_entity as ce join customer_entity_varchar as cev on ce.entity_id = cev.entity_id WHERE cev.value = '".$referCode."'";
				$cust_data = $mymodRead->fetchAll($cust_select);
				
				$refer_select = "select entity_id from customer_entity where email  = '".$customerEmail."'";
				$refer_data = $mymodRead->fetchAll($refer_select);
				if(!empty($cust_data) and $cust_data[0]['email']!=$customerEmail and empty($refer_data)) {
					try {
						$cust_id = $cust_data[0]['entity_id'];
						$insert_query = "INSERT INTO referfriend_referral (`referral_parent_id`, `referral_child_id`, `referral_email`, `refer_id`, `referral_name`, `referral_code`, `referral_register_status`, `referral_purchase_status`, `referral_reminder`) values (".$cust_id." , 0 , '".$customerEmail."' , ".$customer_id." , '".$customername."' , '".$referCode."' , 1 , 0 , 1)";
						$new_user = $mymodWrite->query($insert_query);
					} catch (Exception $e) {
						echo 'Caught exception: ',  $e->getMessage(), "\n";
					}
				}

			}
			else {
				try {
					$update_query = "UPDATE referfriend_referral SET referral_register_status='1', refer_id='".$customer_id."' WHERE referfriend_referral_id = ".$data[0]['referfriend_referral_id'];
					$mymodWrite->query($update_query);
				} catch (Exception $e) {
						echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			}
			
		}
		else {
			if(isset($_COOKIE['utm_crafts']))
			{
				$cookievalue=$_COOKIE['utm_crafts'];
				$acookievalue=explode('-', $cookievalue);
				$utm_source=$acookievalue[0];
				$utm_medium=$acookievalue[1];
				$utm_campaign=$acookievalue[2];
				
				$insCookieData = array('entity_id'=>$customer_id, 'utm_source'=>$utm_source, 'utm_campaign'=>$utm_campaign, 						'utm_medium'=>$utm_medium);   
				
			}
		}
		$utm_select = "SELECT * FROM customer_utm_master WHERE entity_id = '".$customer_id."'";
		$utm_data = $mymodRead->fetchAll($utm_select); 
		if(!empty($insCookieData) && empty($utm_data)) {
			try {
				$w->insert('customer_utm_master', $insCookieData);
			} catch (Exception $e) {
						echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
		Mage::register('customer_save_observer_executed',true);
	}
}
