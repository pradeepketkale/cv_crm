<?php
class Craftsvilla_Uagent_IndexController extends Craftsvilla_Uagent_Controller_AgentAbstract

{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	public function loginAction(){
		$ajax = $this->getRequest()->getParam('ajax');
		 
        if ($ajax) {
        	Mage::getSingleton('uagent/session')->addError($this->__('Your session has been expired. Please log in again.'));
    	    }
        $this->_renderPage($ajax ? 'uagent_index_login_ajax' : null);
    }
	
	public function logoutAction()
    {
        $this->_getSession()->logout();
        $this->_redirect('uagent');
		//$this->_redirect('uagent/index/login');
		
    }
	
	
	 public function preferencesAction()
    {
		$this->_renderPage(null, 'preferences');
    }
	
	public function preferencesPostAction()
	{
		$hlp = Mage::helper('uagent');
		$session = Mage::getSingleton('uagent/session');
		$agId = $session->getId();
		$agent_name = $this->getRequest()->getParam('agent_name');
		$telephone = $this->getRequest()->getParam('telephone');
		$email = $this->getRequest()->getParam('email');
		$password = $this->getRequest()->getParam('password');
		$bank_ac_number = $this->getRequest()->getParam('bank_ac_number');
		$bank_name = $this->getRequest()->getParam('bank_name');
		$check_pay_to = $this->getRequest()->getParam('check_pay_to');
		$bank_ifsc_code = $this->getRequest()->getParam('bank_ifsc_code');
		$agent_attn = $this->getRequest()->getParam('agent_attn');
		$city = $this->getRequest()->getParam('city');
		$zip = $this->getRequest()->getParam('zip');
		
		
		$getAgentdata = Mage::getModel('uagent/uagent')->load($agId);
		$getAgentdata->setAgentName($agent_name)
					 ->setTelephone($telephone)
					 ->setEmail($email)
					 ->setPassword($password)
					 ->setPasswordHash(Mage::helper('core')->getHash($password, 2))
					 ->setBankAccountNumber($bank_ac_number)
					 ->setBankName($bank_name)
					 ->setCheckPayTo($check_pay_to)
					 ->setBankIfscCode($bank_ifsc_code)
					 ->setAgentAttn($agent_attn)
					 ->setCity($city)
					 ->setZip($zip)
					 ->setUpdateTime(now())
					 ->save();
					 
		$session->addSuccess('Settings has been saved');			 
		$this->_redirect('uagent/index/preferences');
	}
	public function agentorderInfoAction()
    {
				
       // $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('uagent');
        if (($url = Mage::registry('uagent_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('uagent/session');

        $this->getResponse()->setBody($block->toHtml());
    }
	
	
    public function agentorderPostAction()
    {
		$hlp = Mage::helper('uagent');
        $r = $this->getRequest();
        $id = $r->getParam('id');
		$agentorder = Mage::getModel('sales/order')->load($id);
		$_agentstatus = $agentorder->getAgentStatus();
        $agent = $hlp->getAgent($agentorder->getAgentId());
        $session = $this->_getSession();
		if (!$agentorder->getId()) {
            return;
        }

        try {
            
            $highlight = array();
			$statusPending = Craftsvilla_Uagent_Model_Source::ORDER_STATUS_PENDING;
			$statusCanceled = Craftsvilla_Uagent_Model_Source::ORDER_STATUS_CANCELED;
			$statusPaymentCollected = Craftsvilla_Uagent_Model_Source::ORDER_STATUS_PAYMENT_COLLECTED;
			$statusPaymentDeposited = Craftsvilla_Uagent_Model_Source::ORDER_STATUS_PAYMENT_DEPOSITED;
			$statusPaymentReceived = Craftsvilla_Uagent_Model_Source::ORDER_STATUS_PAYMENT_RECIEVED;

            $statuses = Mage::getSingleton('uagent/source')->setPath('order_statuses')->toOptionHash();
            $status = $r->getParam('status');
		 //$agentorderStatuses = false;
            
           
			   
            	$oldStatus = $agentorder->getAgentStatus();
                if ($oldStatus==$statusCanceled) {
                    Mage::throwException(Mage::helper('uagent')->__('Canceled Order cannot be reverted'));
                }
                $changedComment = $this->__('%s has changed the order status to %s', $agent->getAgentName(), $statuses[$status]);
                $triedToChangeComment = $this->__('%s tried to change the order status to %s', $agent->getAgentName(), $statuses[$status]);
                if ($status == $statusPaymentCollected) {
					Mage::getModel('sales/order_status_history')
						->setParentId($id)
						->setStatus($status)
						->setAgentStatus($status)
						->setComment($changedComment)
						->setCreatedAt(NOW())
						->save(); 
				$agentorder->setAgentStatus($status);		
                } elseif ($status == $statusCanceled) {
                    
                    Mage::getModel('sales/order_status_history')
						->setParentId($id)
						->setStatus($status)
						->setAgentStatus($status)
						->setComment($changedComment)
						->setCreatedAt(NOW())
						->save();
						$agentorder->setAgentStatus($status);   
                   } else { 
                    $agentorder->setAgentStatus($status)->save();
                    
					Mage::helper('uagent')->addAgentorderComment(
                        $agentorder,
                        $changedComment
                    );
                }
                $agentorder->save();
                $session->addSuccess($this->__('Order status has been changed'));
           
            $comment = $r->getParam('comment');
            
			if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($agentorder->getAllItems() as $item) {
                    	if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('uagent')->sendAgentComment($agentorder, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }
            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
		
        $this->_forward('agentorderInfo');
   
	
	}
	public function sendUagentebslinkAction()
	{
		$session = Mage::getSingleton('uagent/session');
		$_orderId = $this->getRequest()->getparam('uagentorderid');
		
			$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
			$_orderData = $read->query("SELECT * FROM `sales_flat_order` as sfo
										LEFT JOIN `sales_flat_order_address` as sfoa 
										ON sfo.`entity_id` = sfoa.`parent_id`
										LEFT JOIN `ebslink` as es
										ON sfo.`increment_id` = es.`order_no`
										WHERE sfo.`entity_id` = '".$_orderId."' and sfoa.`address_type` = 'billing'")->fetch();
					/*echo '<pre>';
					print_r($_orderData);exit;*/
					
					$namecust = $_orderData['customer_firstname'];
					$email = $_orderData['customer_email'];
					$currencyTotal = Mage::app()->getLocale()->currency($_orderData['order_currency_code'])->getSymbol();
					$currency = $_orderData['order_currency_code'];
					$grandtotal = $_orderData['grand_total'];
					//$ebslinkurl =  $_orderData[0]['ebslinkurl'];
					$entityid = $_orderData['entity_id'];
					$incrementid = $_orderData['increment_id'];
					$_customerTelephone = $_orderData['telephone'];
					$address = $_orderData['street'];
					$city = $_orderData['city'];
					$region = $_orderData['region'];
					$postcode = $_orderData['postcode'];
					$country_id = $_orderData['country_id'];
					$total_qt_ordered = $_orderData['total_qty_ordered'];
					$_grandTotal = $grandtotal/$total_qt_ordered;
					$expiry_days = 30;
		//GET ebslinkurlllll
				$url = 'https://secure.ebs.in/api/invoice';
				$myvar1 = 'create';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $incrementid;
				$myvar5 = $currency;
				$myvar6 = $namecust;
				$myvar7 = $address;
				$myvar8 = $city;
				$myvar9 = $region;
				$myvar10 = $postcode;
				$myvar11 = 'IND';
				$myvar12 = $email;
				$myvar13 = $_customerTelephone;
				$myvar14 = 'Craftsvilla Products';
				$myvar15 = $total_qt_ordered;
				$myvar16 = $_grandTotal;
				$myvar17 = '0';
				$myvar18 = $expiry_days;
				
				
				$fields = array(
								'action' => urlencode($myvar1),
								'account_id' => urlencode($myvar2),
								'secret_key' => urlencode($myvar3),
								'reference_no' => urlencode($myvar4),
								'currency' => urlencode($myvar5),
								'name' => urlencode($myvar6),
								'address' => urlencode($myvar7),
								'city' => urlencode($myvar8),
								'state' => urlencode($myvar9),
								'postal_code' => urlencode($myvar10),
								'country' => urlencode($myvar11),
								'email' => urlencode($myvar12),
								'phone' => urlencode($myvar13),
								'products[0][name]' => urlencode($myvar14),
								'products[0][qty]' => urlencode($myvar15),
								'products[0][price]' => urlencode($myvar16),
								'payment_mode' => urlencode($myvar17),
								'expiry_in_days' => urlencode($myvar18)
								
								);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				/*$j = 0;
				while ($j < $totalItems) { 
					$fields_string .= 'products['.$j.'][name]'.'='.$products[$j]['name'].'&';
					$fields_string .= 'products['.$j.'][qty]'.'='.$products[$j]['qty'].'&';
					$fields_string .= 'products['.$j.'][price]'.'='.$products[$j]['price'].'&';
					$j++;
					}*/
				
				rtrim($fields_string, '&');
				
				//$url = 'https://secure.ebs.in/api/1_0';
				//$url = 'http://www.craftsvilla.com';
				
				//$myvars = 'Action=getCurrencyValue' . '&AccountID=' . $myvar2 . '&SecretKey='.$myvar3 . '&Currency='.$myvar4;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				
				
				$response = curl_exec( $ch );
				if(curl_errno($ch))
				{		
				print curl_error($ch);
				}
				else
				{
				//print_r($response);exit;
				//echo $response;exit;
				
				$xml = @simplexml_load_string($response);
				/*echo '<pre>';
				print_r($xml);exit;*/
				$ebslinkurl = (string)$xml->invoice[0]->payment_url;
				//$ebslinkurl = 'http://craftsvilla.com';
				$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
				
				if($readEbslink) {
					$session->addSuccess($this->__('Ebslink Email & SMS Already Sent! '));
					}
				else
					{
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					//echo "INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW())";exit;
					$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$_orderId."')");
					$session->addSuccess($this->__('Ebslink Email & SMS Sent Successfully! '));
					
					}
				}
				curl_close($ch);
				Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
		  $this->_redirect('uagent/index/index');	
		}
//***********Below function has added By Dileswar On dated 21-10-2013****************//
	public function agentgeneratecouponAction(){
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');
		$updateStatus = $this->getRequest()->getParam('inactive');
		$ruleId = $this->getRequest()->getParam('generatecoupon');
		$salesRulewrite = Mage::getSingleton('core/resource')->getConnection('core_write');
	
		if ($updateStatus == "DeActivate")
			{
			$iaActivate = 0;
			$saleruleactiveQuery = "update `salesrule` set `is_active` = '".$iaActivate."' WHERE `rule_id` = '".$ruleId."'";
			$resultActivateStatus =$salesRulewrite->query($saleruleactiveQuery);
			$session->addSuccess($hlp->__('Your Rule Has Succesfully Deactivated '));
			}
	$this->_renderPage(null, 'agentgeneratecoupon');
	}
	
/*Action Of first row*/	
	public function generatecouponblockAction(){
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
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');	
		$agentId = Mage::getSingleton('uagent/session')->getAgentId();
		$_agentName = Mage::getModel('uagent/uagent')->load($agentId);
		$agentName = $_agentName->getAgentName();
		$couponCode='CVAG'.generateUniqueId(4).$agentId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('percentage');
		$startDate = $this->getRequest()->getParam('from_date');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date');
		$ed = date('Y-m-d',strtotime($endDate));
		 if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/index/agentgeneratecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/index/agentgeneratecoupon');
            return;
        endif;
		$vId = $agentId;
		$rule = Mage::getModel('salesrule/rule');
    	$customer_groups = array(0, 1, 2, 3);
	if($discount > 10)
	{
		$session->addError($hlp->__('You Can Give Only Up To 10% Discount. Please Note Your Commission Payout Will be Deducted By The Percentage Of Coupon You Share With Customer'));
	}	
	else{
		$rule->setName($discount.'% Off -'.$agentName.' Tll '.$ed)
		  ->setDescription('Flat:-'.$discount.'% Off Over All Products')
		  ->setFromDate($sd)
		  ->setToDate($ed)
		  ->setCouponType(2)
		  ->setCouponCode($couponCode)
		  ->setUsesPerCustomer(100)
		  ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
		  ->setIsActive(1)
		  ->setConditionsSerialized('')
		  ->setActionsSerialized('')
		  ->setStopRulesProcessing(0)
		  ->setIsAdvanced(1)
		  ->setProductIds('')
		  ->setSortOrder(0)
		  ->setSimpleAction('by_percent')
		  ->setDiscountAmount($discount)
		  ->setDiscountQty(null)
		  ->setDiscountStep(0)
		  ->setSimpleFreeShipping('0')
		  ->setApplyToShipping('0')
		  ->setIsRss(0)
		  ->setAgentid($vId)
		  ->setWebsiteIds(array(1));
	 
		$item_found = Mage::getModel('salesrule/rule_condition_product_found')
		  ->setType('salesrule/rule_condition_product_found')
		  ->setValue(1) // 1 == FOUND
		  ->setAggregator('all'); // match ALL conditions
		$rule->getConditions()->addCondition($item_found);
		$conditions = Mage::getModel('salesrule/rule_condition_product')
		  ->setType('salesrule/rule_condition_product')
		  ->setAttribute('uagent_agent')
		  ->setOperator('==')
		  ->setValue('');
		$item_found->addCondition($conditions);
	 
		//$actions = Mage::getModel('salesrule/rule_condition_product')
		  //->setType('salesrule/rule_condition_product');
		  //->setAttribute('uagent_agent')
		  //->setOperator('==')
		  //->setValue($agentId);
		//$rule->getActions()->addCondition($actions);
		$rule->save();
		
		$session->addSuccess($hlp->__('Your Coupon Has Been Succesfully Created. Please Note Your Commission Payout Will be Deducted By The Percentage Of Coupon You Share With Customer'));
	}
	$this->_redirect('*/index/agentgeneratecoupon');		
	}
	/*Action Of Second row*/
	public function generatecouponfixedcartAction(){
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
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');	
		$agentId = Mage::getSingleton('uagent/session')->getAgentId();
		$_agentName = Mage::getModel('uagent/uagent')->load($agentId);
		$agentName = $_agentName->getAgentName();
		$couponCode='CVAG'.generateUniqueId(4).$agentId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('rupees');
		$discountOff = $this->getRequest()->getParam('rupees1');
		$startDate = $this->getRequest()->getParam('from_date1');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date1');
		$ed = date('Y-m-d',strtotime($endDate));
		if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/index/agentgeneratecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/agent/agentgeneratecoupon');
            return;
        endif;
		$vId = $agentId;
		$rule = Mage::getModel('salesrule/rule');
    	$customer_groups = array(0, 1, 2, 3);
    $rule->setName($discount.'/- Off -'.$agentName.' Tll '.$ed)
      ->setDescription($discount.'/- Off If Order is greater than Rs.'.$discountOff)
      ->setFromDate($sd)
	  ->setToDate($ed)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCustomer(100)
      ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
      ->setIsActive(1)
      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('cart_fixed')
      ->setDiscountAmount($discount)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('0')
      ->setIsRss(0)
      ->setAgentid($vId)
	  ->setWebsiteIds(array(1));

    $item_found = Mage::getModel('salesrule/rule_condition_product_subselect')
      ->setType('salesrule/rule_condition_product_subselect')
	  ->setAttribute('base_row_total')
	  ->setOperator('>=')
	  ->setValue($discountOff)
      ->setAggregator('all'); // match ALL conditions
	  
	  
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('uagent_agent')
      ->setOperator('==')
      ->setValue('');
    $item_found->addCondition($conditions);
 
    //$actions = Mage::getModel('salesrule/rule_condition_product')
    //  ->setType('salesrule/rule_condition_product');
      //->setAttribute('uagent_agent')
      //->setOperator('==')
      //->setValue($agentId);
    //$rule->getActions()->addCondition($actions);
    $rule->save();
	
	$session->addSuccess($hlp->__('Your Rule2 Has Succesfully created & applied '));
	$this->_redirect('*/index/agentgeneratecoupon');		
	}
/*Action Of Third row*/	
	public function generatecouponpercentageAction(){
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
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');	
		$agentId = Mage::getSingleton('uagent/session')->getAgentId();
		$_agentName = Mage::getModel('uagent/uagent')->load($agentId);
		$agentName = $_agentName->getAgentName();
		$couponCode='CVAG'.generateUniqueId(4).$agentId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('rupeepercent');
		$discountOff1 = $this->getRequest()->getParam('rupeepercent1');
		$startDate = $this->getRequest()->getParam('from_date2');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date2');
		$ed = date('Y-m-d',strtotime($endDate));
				if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/index/agentgeneratecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/index/agentgeneratecoupon');
            return;
        endif;
		$vId = $agentId;
		$rule = Mage::getModel('salesrule/rule');
		//echo '<pre>';print_r($rule->getCollection()->getData());exit;
    	$customer_groups = array(0, 1, 2, 3);
    if($discount > 10)
	{
		$session->addError($hlp->__('You Can Give Only Up To 10% Discount. Please Note Your Commission Payout Will be Deducted By The Percentage Of Coupon You Share With Customer'));
	}	
	else{
	$rule->setName($discount.'% Off -'.$agentName.' Tll '.$ed)
      ->setDescription($discount.'% Off If order is greater than rupees Rs.'.$discountOff1)
      ->setFromDate($sd)
	  ->setToDate($ed)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCustomer(100)
      ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
      ->setIsActive(1)
      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('by_percent')
      ->setDiscountAmount($discount)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('0')
      ->setIsRss(0)
      ->setAgentid($vId)
	  ->setWebsiteIds(array(1))
	  ;
 
    $item_found = Mage::getModel('salesrule/rule_condition_product_subselect')
      ->setType('salesrule/rule_condition_product_subselect')
	  ->setAttribute('base_row_total')
	  ->setOperator('>=')
	  ->setValue($discountOff1)
      ->setAggregator('all'); // match ALL conditions
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('uagent_agent')
      ->setOperator('==')
      ->setValue('');
    $item_found->addCondition($conditions);
 
    $actions = Mage::getModel('salesrule/rule_condition_product');
//      ->setType('salesrule/rule_condition_product');
      //->setAttribute('uagent_agent')
      //->setOperator('==')
      //->setValue($agentId);
    //$rule->getActions()->addCondition($actions);
    $rule->save();
	
	$session->addSuccess($hlp->__('Your Coupon Has Been Succesfully Created. Please Note Your Commission Payout Will be Deducted By The Percentage Of Coupon You Share With Customer'));
	}
	$this->_redirect('*/index/agentgeneratecoupon');		
	}
	public function paidstatusAction(){
	$this->_renderPage(null, 'paidstatus');
	}
	
	public function createcatalogAction(){
	$this->_renderPage(null, 'createcatalog');
	}
	
	public function createcataloglistAction()
	{
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');
		$agentId = Mage::getSingleton('uagent/session')->getAgentId();
		$_agentName = Mage::getModel('uagent/uagent')->load($agentId);
		$agentName = $_agentName->getAgentName();
        $catalogtitle = $this->getRequest()->getParam('catalogtitle');
		$skuId = $this->getRequest()->getParam('sku');
		$couponid = $this->getRequest()->getParam('couponcode');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$pr = "select * from `catalog_product_craftsvilla3` where sku = '".$skuId."'";
		$prres = $read->query($pr)->fetch();
		$_sku = $prres['sku'];
		if($_sku)
		{
		$couponQuery = "SELECT * FROM `salesrule_coupon` WHERE `code` = '".$couponid."'";
		$couponQueryres = $read->query($couponQuery)->fetch();
		$couponcode = $couponQueryres['code'];
		$ruleid = $couponQueryres['rule_id'];
		$salesrule = "SELECT * FROM `salesrule` WHERE `rule_id` = '".$ruleid."'";
		$salesruleres = $read->query($salesrule)->fetch();
		$description = $salesruleres['description'];
		$todate = $salesruleres['to_date'];
		$date = date('jS F Y', $todate);
		$skus = explode(",",$skuId);
		$count = count($skus);
		$cols = 2;
		$i=1;
		$html = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 690px;">
    <tbody>
      <tr>
        <td align="center" height="170">
          <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;" name="imgHolder">
            <tbody>
              <tr>
                <td align="center"><a href="http://www.craftsvilla.com"><img title="Craftsvilla.com"caption="" src="http://www.craftsvilla.com/email-banners/craftsvilla-logo.jpg" alt="Craftsvilla.com" width="300" height="161" border="0" hspace="0" vspace="0" /></a>
                </td>
              </tr>
              <tr>
                <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td style="line-height: 26px; text-align: center; font-weight: bold; font-family: Times New Roman, Arial, Helvetica, sans-serif; font-size: 16px; color: #d2152f; letter-spacing: 1px;">Indias Largest Marketplace for Indian Products</td>
      </tr>
      <tr>
        <td><img style="display: block;"src="https://improxy.benchmarkemail.com/http://craftsvilla.com/mailers/navratri/top-stroke.gif" alt=""width="690" height="14" border="0" />
        </td>
      </tr>
      <tr>
        <td>
          <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 620px;">
            <tbody>
              <tr>
                <td style="background: #877A5E;">
                </td>
              </tr>
              <tr>
                <td>
                  <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;"name="imgHolder">
                    <tbody>
                      <tr>
                        <td align="center"><a href="http://www.craftsvilla.com"><img title="Craftsvilla in Media" caption="" src="https://images.benchmarkemail.com/client136252/image704976.jpg" alt="Craftsvilla in Media" width="622" height="48" border="0" hspace="0" vspace="0" /></a>
                        </td>
                      </tr>
                      <tr>
                        <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr>
                <td height="15">&nbsp;</td>
              </tr>              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 620px;">
                    <tbody>
                      <tr>
<td colspan="3" style="background-image: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: #f3ece2; font-size: 18px; font-weight: bold; text-align: center; background-position: initial initial; background-repeat: initial initial; border-width: 1px; border-color: #ece2d5; border-style: solid; padding: 8px;"><span style="font-family: "Segoe Print", "Comic Sans MS", Arial, sans-serif; font-size: 14px; color: #ef0f64;"><font color="#D2152F"><span style="font-size: 16px;">'.$description.' Till '.$date.'<br />Use This Coupon Code on Checkout: '.$couponcode.'<br /></span><span style="color: #d2152f;"><br /></font></span>                        </td>
                      </tr>
                      <tr>
                        <td colspan="3">&nbsp;</td>
                      </tr>';
		foreach($skus as $sku)
		{
			$sku = trim($sku);
			$collection = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
			$vendorid = $collection->getUdropshipVendor();
			$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
			$vendorName = $vendor->getVendorName();
			$vendorurl = $vendor->getUrlKey();
			$childId= $collection->getId();
			$purl = 'catalog/product/view/id/'.$childId;
			$_skuid = $collection['sku'];
			$name = $collection['name'];
			$price = $collection['price'];
			$shortdescription = $name.' in Rs. '.$price;
			$imageurl = Mage::getBaseUrl().$purl;
			$image = Mage::helper('catalog/image')->init($collection, 'image')->resize(300);
			$_target = Mage::getBaseDir('media') . DS . 'agenthtml' . DS;
			$newfilename = mt_rand(10000000, 99999999).'_Catalog.html';
			$_path = $_target.$newfilename;
			
			if($i%$cols==1)
			{
			  $html .= '<tr>';
			
			}
			$html .=     '<td height="15">
                  <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 100px;">
                    <tbody>
                      <tr>
                        <td colspan="3" height="5">
                        </td>
                      </tr>
					  <tr>
<td height="206" style="border: #ECE2D5 1px solid; border-bottom: none;"width="302">
                          <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;"name="imgHolder">
                            <tbody>
                              <tr>
                                <td align="center"><a href="'.$imageurl.'" target="_blank"><img title="'.$name.'" caption="" src="'.$image.'" alt="'.$name.'" width="300" height="300" border="0" hspace="0" vspace="0" /></a>
                                </td>
                              </tr>
                              <tr>
                                <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td></tr>';
				
				$html .= '<tr><td style="border: #ECE2D5 1px solid; padding: 5px 0;" valign="top">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 95%;">
                            <tbody>
                              <tr>
                                <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 5px 0px;"><strong>'.$name.'&nbsp;- Rs. '.$price.'</strong><br /><br />
                                </td>
                                <td>
                                  <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;" name="imgHolder">
                                    <tbody>
                                      <tr>
                                        <td align="center"><a href="'.$imageurl.'"><img title="'.$name.'" caption=""src="https://images.benchmarkemail.com/client136252/image774099.gif" alt="'.$name.'" width="83" height="29" border="0" hspace="0" vspace="0" /></a>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                                        </td>
                                      </tr>    
                                    </tbody>
                                  </table>
                                </td>
                                 </tr>           
';
            $html .= ' <tr><td style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">'.$name.'&nbsp;by <a href="'.Mage::getBaseUrl().$vendorurl.'"><strong>'.$vendorName.'</strong></a> &nbsp;in Rs. '.$price.' Only.</td>
                                <td style="font-family: Arial, Times New Roman, Times, serif; font-size: 13px; color: #d2152f; font-weight: bold; text-align: center;">&nbsp;</td>
                              </tr>
                             </tbody>
                  </table>
                  </td><td>&nbsp;</td></tr>
                    </tbody>
                  </table>
                </td>
                   ';
			if($i%$cols==0)
			{
			  $html .= '</tr>';
			
			}
			$i++;
					}
		
		$html .= '</table>';
		 $fileSavename = Mage::getBaseDir('media').'/agenthtml/';
		file_put_contents($fileSavename.$newfilename, $html);
		//file_put_contents('/var/www/html/media/agenthtml/'.$newfilename, $html);
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
        /*$agentsave = Mage::getModel('uagent/cataloguecraftsvilla');
												$agentsave->setCreatedAt(NOW())
												->setFilename($newfilename)
												->setCouponCode($couponid)
												->setAgentid($agentId)
												->setType(1)
												->setCatalogtitle($catalogtitle)
												->save();*/
	$agentsaveqery = "INSERT INTO `cataloguecraftsvilla`(`created_at`, `filename`, `coupon_code`, `catalogtitle`, `agentid`, `type`) VALUES (NOW(),'".$newfilename."','".$couponid."','".$catalogtitle."','".$agentId."','1')";											
	$agentsave = $write->query($agentsaveqery);	
	
	$this->_getSession()->addSuccess("Record inserted suceessfully");						
		}
		else
		{
			$this->_getSession()->addError("This Product is Out Of Stock. Cannot Create Catalog.");
		}
	
		$this->_redirect('*/index/createcatalog');	
	}
	
  public function sendemailAction()
	{
		$couponid = $this->getRequest()->getParam('catalog_id');
		$hlp = Mage::helper('uagent');
        $session = Mage::getSingleton('uagent/session');
		$agentId = Mage::getSingleton('uagent/session')->getAgentId();
		$_agent = Mage::getModel('uagent/uagent')->load($agentId);
		$agentName = $_agent->getAgentName();
		$agentemail = $_agent->getEmail();
		$skuId = $this->getRequest()->getParam('sku');
		$getProductId = $this->getRequest()->getParam('couponcode');
		$id = $this->getRequest()->getParam('sendemail');
         $storeId = Mage::app()->getStore()->getId();
		 /*$collection = Mage::getModel('uagent/cataloguecraftsvilla')->getCollection()
						->addFieldToFilter('agentid', $agentId)
						->addFieldToFilter('catalog_id', $couponid)
						->setOrder('catalog_id', 'DESC');*/
		$readCollect = 	Mage::getSingleton('core/resource')->getConnection('core_read');			
		$collectionQry = "SELECT * FROM  `cataloguecraftsvilla` WHERE  `agentid` =  '".$agentId."' AND `catalog_id` = '".$couponid."' ORDER BY  `cataloguecraftsvilla`.`catalog_id` DESC";
		$collection = $readCollect->query($collectionQry)->fetchAll();				
						//echo '<pre>';echo $collection->getSelect()->__ToString();exit;
		foreach($collection as $collect)
		{
		$file = $collect['filename'];
		$filepath = Mage::getBaseUrl('media'). 'agenthtml'.'/';
		$filenamepath = $filepath.$file;
		$content = file_get_contents($filenamepath);
		$storeId = Mage::app()->getStore()->getId();
			$templateId = 'agent_catalaog_email_template';
			$translate  = Mage::getSingleton('core/translate');
			$_email = Mage::getModel('core/email_template');
			$mailSubject = 'Exclusive Coupon Code To Buy Unique Indian Products From Craftsvilla!';
			$sender = Array('name' => 'Craftsvilla',
			'email' => 'customercare@craftsvilla.com');
			
		$vars = array('filename' => $content, 'file' => $file);
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId,$sender,$agentemail,$recname, $vars,$storeId);
						$translate->setTranslateInline(true);
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId,$sender,'manoj@craftsvilla.com',$recname, $vars,$storeId);
						$translate->setTranslateInline(true);
			$successnotice = 'Email has been sent successfully to your Email-id.';
				$this->_getSession()->addSuccess($successnotice);
		}
			
	   $this->_redirect('*/index/createcatalog');
	}


}
