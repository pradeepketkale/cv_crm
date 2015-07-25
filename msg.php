<style type="text/css">
div.toggler				{ border:1px solid #ccc; background:url(gmail2.jpg) 10px 12px #eee no-repeat; cursor:pointer; padding:10px 32px; }
div.toggler .subject	{ font-weight:bold; }
div.read					{ color:#666; }
div.toggler .from, div.toggler .date { font-style:italic; font-size:11px; }
div.body					{ padding:10px 20px; }
</style>

<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$user = 'update@craftsvilla.com';
$password1 = 'cv@up23!45';

/* try to connect */
$inbox = imap_open($hostname,$user,$password1) or die('Cannot connect to Gmail: ' . imap_last_error());
$check = imap_mailboxmsginfo ($inbox);
$count = imap_num_msg($inbox);
/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
	
	/* for every email... */
	foreach($emails as $email_number) {
		
		$overview = imap_fetch_overview($inbox,$email_number,0);
		$message = imap_fetchbody($inbox,$email_number,1);
		$mailhead  = imap_headerinfo($inbox, $email_number);
		$header  = imap_header($inbox, $email_number);
		$sendername = $header->fromaddress;
		$emailAddress = $mailhead->from[0]->mailbox . "@" . $mailhead->from[0]->host;
		$messagearraynew = explode("\n",$message);
	    $arraycount = sizeof($messagearraynew);
		$i=0;
		for($i=0; $i<$arraycount;$i++)
		{
		$messagearray = explode(',',$messagearraynew[$i]);
		//$shipmentid = explode(",",$messagearraynew[0]);
		//echo sizeof($messagearray);
		//echo '<pre>';print_r($messagearray);
		if(sizeof($messagearray)==3)
		{
		echo $incrementid = intval(preg_replace('/\s\s+/',' ',$messagearray[0])); 
		if(is_int($incrementid) && (strlen((string)$incrementid) == 9))
		{
		$couriername = preg_replace('/\s\s+/',' ',$messagearray[1]);
		
		//$message2 = explode("\n\r",$messagearray[2]);
		$trackingnumber = preg_replace('/\s\s+/',' ',$messagearray[2]);
		
		if(strrpos(strtolower($overview[0]->subject),'tracking update')!== false)
		{
		/* output the email body */
		$vendors = "select `vendor_id` from udropship_vendor where `email`='$emailAddress'";
		$result = $db->query($vendors)->fetchAll();
		//while($vid = mysql_fetch_array($result))
 		foreach($result as $vid)
		{
			$output.= $vid['vendor_id'].'<br><br>';
			$shipment= Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
			$entity = $shipment['shipping_address_id'];
			 /* $address = Mage::getModel('sales/order_address')
            ->getCollection()
            ->getItemById($entity);
			echo '<pre>';print_r($address);exit;*/
			$shipmentaddress = Mage::getModel('sales/order_address')->load($entity);
			$country = $shipmentaddress->getCountryId();
			if($shipment->getUdropshipStatus()==11 || $shipment->getUdropshipStatus()==18 || $shipment->getUdropshipStatus()==21)
			{
				if($country == 'IN')
				{
					
			 $track = Mage::getModel('sales/order_shipment_track')
							->setNumber($trackingnumber)
							->setCourierName($couriername)
							 ->setTitle('Domestic Shipping');
				$shipment->addTrack($track);
				
				if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
				
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
					 Mage::helper('udropship')->addShipmentComment($shipment,
							   ('Shipment has been complete by System')
							  );
				}			  
				
				$shipment->save();
				$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
					$_shipmentId = $shipment->getIncrementId();
					$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
					$_order = $shipment->getOrder();
					$customerTelephone = $_order->getBillingAddress()->getTelephone();
					$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
					$storeId = $_order->getStoreId();
					$shipment->sendEmail();
					$shipment->setEmailSent(true);
					$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
					$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
					$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
					$_smsSource = Mage::getStoreConfig('sms/general/source');
					if($_orderBillingCountry == 'IN')
					{
							$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$incrementid.' , Track Number: '.$trackingNumber.'Courier Name :'.$couriername.' - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
							$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
							$parse_url = file($_customerSmsUrl);	
							
					}
				}
				if($country !== 'IN')
				{
				
				if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED_CRAFTSVILLA) {
				
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED_CRAFTSVILLA);
					Mage::helper('udropship')->addShipmentComment($shipment,($trackingnumber.$couriername));
				
				}
				$shipment->save();
				}
				 $storeId = Mage::app()->getStore()->getId();
						$templateId = 'shipmentupdate_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Tracking Number For Shipment No: '.$incrementid.' is Updated!';
						$updateHtml = '';
						            
					$vars = Array('updateHtml' =>$updateHtml, 'shipmentid' => $incrementid);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $emailAddress, $recname, $vars, $storeId);
						$translate->setTranslateInline(true);
						echo "Email has been sent successfully";
		    
		}
			
		
		elseif($shipment->getUdropshipStatus()==6 || $shipment->getUdropshipStatus()==12 || $shipment->getUdropshipStatus()==13 || $shipment->getUdropshipStatus()==19 || $shipment->getUdropshipStatus()==20 || $shipment->getUdropshipStatus()==22 || $shipment->getUdropshipStatus()==22)
		{
			 $storeId = Mage::app()->getStore()->getId();
						$templateId1 = 'shipmentnotupdate_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Tracking Number For Shipment No: '.$incrementid.' is Not Updated!';
						$shipmentnotupdateHtml = '';
						            
					$vars = Array('shipmentnotupdateHtml' =>$shipmentnotupdateHtml, 'shipmentid' => $incrementid);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId1, $sender, $emailAddress, $recname, $vars, $storeId);
						$translate->setTranslateInline(true);
			
		}
		
		echo "Email has been sent successfully";
		}
					imap_delete($inbox, $email_number);
		}
		}
		}
		}
	   
	
	}
	 
	
	 
}
/* close the connection */
//imap_delete($inbox, $email_number);
imap_expunge($inbox);

imap_close($inbox);


 
	
?>
