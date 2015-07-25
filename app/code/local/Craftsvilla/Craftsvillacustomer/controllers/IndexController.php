<?php
class Craftsvilla_Craftsvillacustomer_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();     
        $this->renderLayout();
    }
    
    public function custdataAction()
    {
        $values=$this->getRequest()->getParams();
        $customerEmail=$values['mail'];
        $month=$values['month'];
        $year=$values['year'];
        $dobs=$year.'-'.$month;
        $dob=date('Y-m-d',strtotime($dobs));

        $gender=$values['gender'];

        if(count($values[intrest])>1)
        {    
            $interest=$values[intrest][0].','.$values[intrest][1];
        }
        else{
            $interest=$values[intrest][0];
        }
        
        if(count($values[love])>1)
        {    
            $love=$values[love][0].','.$values[love][1];
        }
        else{
            $love=$values[love][0];
        }
        
        if(count($values[describe1])>0)
        {
            $describe=$values[describe1];
        }
        else
        {
            $describe='NULL';
        }
        if(count($values[describe])>1)
        {    
            $haveChildren=$values[describe][0].','.$values[describe][1];
        }
        else{
            $haveChildren=$values[describe][0];
        }

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
	 
	$customer = Mage::getModel("customer/customer");
	$customer->website_id = $websiteId;
        $customer->loadByEmail($customerEmail);
        if($customer)
        {
            $custId=$customer->getEntityId();
            $custName=$customer->getFirstname();
        }
        else
        {
            $custId='';
            $custName='';
        }
        
        $data=Mage::getModel('craftsvillacustomer/craftsvillacustomer')->getCollection()
                ->addFieldToFilter('cust_email', array('in' => array($customerEmail)))
                ->load();
        if(!$data->getData())
            {
            $data=Mage::getModel('craftsvillacustomer/craftsvillacustomer')
                    ->setCustId($custId)
                    ->setCustEmail($customerEmail)
                    ->setCustName($custName)
                    ->setCustDob($dob)
                    ->setCustGender($gender)
                    ->setShoppingInterest($interest)
                    ->setShoppingCategories($love)
                    ->setDescribesYou($describe)
                    ->setHaveChild($haveChildren)
                    ->save();
             
        }
        else{
            $this->_redirect('*/*/already');
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    public function alreadyAction()
    {
        $this->loadLayout();     
        $this->renderLayout();
    }
    
    public function sendemailcommunicationAction()
    {
        $storeId = Mage::app()->getStore()->getId();
        //$translate  = Mage::getSingleton('core/translate');
	//	$translate->setTranslateInline(false);
		$vars = array();       
        //echo 'name=='.$_FILES['imgfile']['name'];exit;
        
        if(Mage::getSingleton('core/session')->getAttachImage()!=''){
           $image="<img src='".Mage::getSingleton('core/session')->getAttachImage()."' alt='' width='154' border='0' style='float:left; border:2px solid #7599AB; margin:0 20px 20px;' />";
        }
        else{
            $image='';
        }
        
        //echo "<br />hi".$data['imgfile'];exit;
/*Some columns Like url image ,product name added By dileswar On dated 22-07-2013  for showing in customer a/c page as well as on email*/    
        $values=$this->getRequest()->getPost();
        $recepientnewcustomerEmail = $values['newcustemail'];
		$recepientEmail=$values['recmail'];
        $recepientName=$values['recname'];
        $messageId=$values['msgid'];
        $vendorId=$values['vendorid'];
        $subject=$values['subject'];
		$productidd=$values['productid'];
        $content = $values['message_text'];
		$_productImagephoto=$values['imagephoto'];
		$_productImagename=$values['productname'];
		$_productUrl = $values['producturl'];
        //$cc='messages@craftsvilla.com';
        $ccName='kribhasanvi';
        $sendEmail=$values['sendemail'];
        $sendName=$values['sendname'];
        //$sendccName='messages@craftsvilla.com';
        
        $templateId='sellerbuyer_comm_template';
        $sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
		
       	//Commented By dileswar on dated 06-12-2012
		
		// $senderCc = Array('name'  => $sendName,
		//'email' => $sendccName);
        
         $url=Mage::getBaseUrl().'marketplace/vendor/vendorinboxread/msgid/'.$messageId.'/sub/'.$subject.'/custid/'.$values['custid'].'/vendid/'.$vendorId;
        $infoText='<strong> Customer has a question for you! </strong> You can directly reply to this from your email. Please respond to this email ASAP. DO NOT RESPOND to the sender if this message requests that you complete the transaction outside of Craftsvilla. This type of communication is against Craftsvilla policy, is not covered by the Craftsvilla Buyer Protection Program and can result in termination of your account with us.';
        $vars['content']=$content;
        $vars['image']=$image;
        $vars['replyurl']=$url;
        $vars['infotext']=$infoText;
		$vars['imagephoto'] = $_productImagephoto;
		$vars['productname'] = $_productImagename;
        $vars['prod_url'] = $_productUrl;
        
        date_default_timezone_set('Asia/Kolkata');
        $emailCommunication=Mage::getModel('craftsvillacustomer/emailcommunication');
        $emailCommunication->setVendorId($vendorId)
                           ->setCustomerId($values['custid'])
                           ->setMessageId($messageId)
                           ->setRecepientEmail($recepientEmail)
                           ->setRecepientName($recepientName)
                           ->setImage(Mage::getSingleton('core/session')->getAttachImage())
                           ->setSubject($subject)
						   ->setProductid($productidd)
                           ->setContent($content)
                           ->setType(0)
                           ->setCreatedAt(now())
                           ->save();
        
        $mailTemplate=Mage::getModel('core/email_template');
        /* Send mail To vendor*/
        $mailTemplate->setTemplateSubject($subject)->setReplyTo($recepientnewcustomerEmail)->sendTransactional($templateId,$sender,$recepientEmail,$recepientName,$vars,$storeId);
		
		//$mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$recepientnewcustomerEmail,$recepientName,$vars,$storeId);
        /* Send mail To Customer*/
        //$mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$values['sendemail'],$values['sendname'],$vars,$storeId);
		
	//$mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,'places@craftsvilla.com',$values['sendname'],$vars,$storeId);
        /* Send mail To Saanvi for cc*/
        
        $_vendor=Mage::helper('udropship')->getVendor($vendorId);
        $_vendorName = $_vendor['vendor_name'];
        $_customerName=Mage::getModel('customer/customer')->load($values['custid'])->getFirstname();
        $vars['custtovendor']= 'Customer - '.$_customerName.'('.$values['custid'].') => Vendor - ' .$_vendorName. '(' .$vendorId .') ';
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$senderCc,$cc,$ccName,$vars,$storeId);

		//$translate->setTranslateInline(true);
        
        echo 'message sent!';
    }
	
	
/*public function captchacustcodordercodeAction()
    {
       if($_POST['captcha']==$_SESSION["catchacod_code159"])
       {
         echo 'Successfully submitted';
       }
       else
       {
         echo 'Wrong Code Entered';
       }
   }*/
   
/*public function custsimplecodorderAction()
    {
    	
    	$storeId = Mage::app()->getStore()->getId();
        //$translate  = Mage::getSingleton('core/translate');
		//$translate->setTranslateInline(false);
		$vars = array();       
        $values=$this->getRequest()->getPost();
        $recepientnewcustomerEmail = $values['newcustemail'];
		$vendorId=$values['vendorid'];
        $productid=$values['productid'];
        $custphone = $values['newcustphone'];
        $captchacodno = $values['captchaid'];
       // $captchaimageno =  $values['captchaimage'];
       // $product = Mage::getModel('catalog/product')->load($productid);
        if($values['captchaid']==$_SESSION["catchacod_code159"])
        {
         
	        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$productQuery = "SELECT * FROM `catalog_list_craftsvilla_s1` where `entity_id` = '".$productid."'";
			$_product = $read->query($productQuery)->fetch();
			$image = $_product['image_url'];
			$name = $_product['name'];
			$price = $_product['price'];
			$spprice = $_product['special_price'];
	        //$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
	        $address = $values['address_text1'];
	        $city = $values['newcustcity'];
	        
	        $qty = $values['prqty'];
			$ccName='kribhasanvi';
	        $custEmail=$values['newcustemail'];
	        $custfName=$values['newcustfirstname'];
	        $custlName=$values['newcustlastname'];
	        $zip = $values['newcustzip'];
	        $state = $values['state'];
	        $write = Mage::getSingleton('core/resource')->getConnection('core_write');	
			$insertorderquery = "INSERT INTO `simple_cod_order` (`cod_id`, `cust_fname`, `cust_lname`, `cust_phone`, `cust_address`, `cust_city`, `cust_zip`, `cust_state`, `cust_country`, `cust_email`, `product_id`, `qty`) VALUES ('', '" . $custfName . "', '" . $custlName . "', '" . $custphone . "', '" . $address . "', '" . $city . "', '" . $zip . "', '" . $state . "', 'IN', '" . $custEmail . "', '" . $productid . "', '1')";
			$write->query($insertorderquery);
			
			//$product = Mage::getModel('catalog/product')->load($productid);
			//$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
			$codorderhtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;width: 13px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;width: 18px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;width: 13px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Qty</td></tr><tr><td style='font-size: 13px;height: 26px;width:13px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><img src='".$image."' alt='' height='154' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' /></td><td style='font-size: 13px;height: 26px;width:268px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$name."</td><td style='font-size: 13px;height: 26px;width: 68px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".floor($price*$qty)."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$qty."</td></tr></table>";
	       /* $storeId = Mage::app()->getStore()->getId();	 
	        $templateId='cod_order_create_template';
	        	$sender = Array('name'  => 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							//$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Your Request Has Been Successfully Submitted';
							$rejectionreason = '';
							
	            
						$vars = Array('custfname'=>$custfName, 'custlname'=>$custlName, 'codorderhtml'=>$codorderhtml);		
						
						$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
								->setTemplateSubject($mailSubject)
								->sendTransactional($templateId, $sender, 'gsonar8@gmail.com', $recname, $vars, $storeId);
							//	$translate->setTranslateInline(true);
			
	        // Mage::getSingleton('core/session')->addSuccess("Your request has been successfully submitted");
       }
        else
        {
        	Mage::getSingleton('core/session')->addError($this->__('You have entered wrong code. Please enter the code as shown in the figure.'));
        	exit;
        }
        
        
    }*/
    
    public function customerinboxAction()
    {
        $this->loadLayout();     
        $this->renderLayout();
    }
    
    public function customerinboxreadAction()
    {
        $this->loadLayout();     
        $this->renderLayout();
    }
    
    public function customerreplyAction()
    {
        $storeId = Mage::app()->getStore()->getId();
        //$translate  = Mage::getSingleton('core/translate');
	//$translate->setTranslateInline(false);
	$vars = array();
	/*Some columns Like url image ,product name added By dileswar On dated 22-07-2013  for showing in customer a/c page as well as on email*/    
        $values=$this->getRequest()->getParams();
		$content=$values['reply_text'];
        $recepientEmail=$values['recepmail'];
        $recepientName=$values['recepname'];
        $messageId=$values['msgid'];
        $customerId=$values['custid'];
        $vendorId=$values['vendorid'];
        $subject = str_replace('%','',$values['subject']);
        $productidcust=$values['productid'];
		$_productImagephoto=$values['imagephoto'];
		$_productImagename=$values['productname'];
        $_productUrl = $values['producturl'];
        //$cc='messages@craftsvilla.com';//'message@craftsvilla.com';
        $ccName='kribhasanvi';
        $sendEmail=$values['sendemail'];
        $sendName=$values['sendname'];
        //$sendccName='messages@craftsvilla.com';
        
        $templateId='sellerbuyer_comm_template';
        $sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
        //Commented By dileswar on dated 06-12-2012
		
		//$senderCc = Array('name'  => $sendName,
		//'email' => $sendccName);
        
        $image='';
        $url=Mage::getBaseUrl().'marketplace/vendor/vendorinboxread/msgid/'.$messageId.'/sub/'.$productidcust.$subject.'/custid/'.$values['custid'].'/vendid/'.$vendorId;
        $infoText='<strong> Customer has a question for you! </strong> Please respond to this email by going to your inbox in your vendor panel after you log in. DO NOT RESPOND to the sender if this message requests that you complete the transaction outside of Craftsvilla. This type of communication is against Craftsvilla policy, is not covered by the Craftsvilla Buyer Protection Program and can result in termination of your account with us.';
        $vars['content']=$content;
        $vars['image']=$image;
        $vars['replyurl']=$url;
        $vars['infotext']=$infoText;
		$vars['imagephoto'] = $_productImagephoto;
		$vars['productname'] = $_productImagename;
        $vars['prod_url'] = $_productUrl;

		date_default_timezone_set('Asia/Kolkata');
        $emailCommunication=Mage::getModel('craftsvillacustomer/emailcommunication');
        $emailCommunication->setVendorId($vendorId)
                           ->setCustomerId($customerId)
                           ->setMessageId($messageId)
                           ->setRecepientEmail($recepientEmail)
                           ->setRecepientName($recepientName)
                           ->setImage($image)
                           ->setSubject($subject)
                           ->setContent($content)
                           ->setProductid($productidcust)
						   ->setType(0)
                           ->setCreatedAt(now())
                           ->save();
        
        $mailTemplate=Mage::getModel('core/email_template');
        /* Send mail To vendor*/
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$recepientEmail,$recepientName,$vars,$storeId);
        /* Send mail To Customer*/
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$values['sendemail'],$values['sendname'],$vars,$storeId);
        /* Send mail To Saanvi for cc*/
        
        $_vendor=Mage::helper('udropship')->getVendor($vendorId);
        $_vendorName = $_vendor['vendor_name'];
        $_customerName=Mage::getModel('customer/customer')->load($values['custid'])->getFirstname();
        $vars['custtovendor']= 'Customer - '.$_customerName.'('.$values['custid'].') => Vendor - ' .$_vendorName. '(' .$vendorId .') ';
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$senderCc,$cc,$ccName,$vars,$storeId);
        
        //$translate->setTranslateInline(true);
        
        $redirectUrl='craftsvillacustomer/index/customerinboxread/msgid/'.$messageId.'/sub/'.$subject.'/custid/'.$customerId.'/vendid/'.$vendorId;
        $this->_redirect($redirectUrl);
    }
    
    public function customerpicsaveAction()
    {
        if($_FILES['custphoto']['name']){
                $uploader = new Varien_File_Uploader('custphoto');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $media_path  = Mage::getBaseDir('media').'/customerPhotos'. DS;                
                $uploader->save($media_path, $_FILES['custphoto']['name']);
                $data['custphoto'] = Mage::getBaseUrl('media').'customerPhotos/'. $_FILES['custphoto']['name'];
        }
        else{
            $data['custphoto']='';
        }
        
        $custId=$this->getRequest()->getParam('custid');
        $custPic=$data['custphoto'];
        
        $_customer=Mage::getModel('customer/customer')->load($custId);
        $_customer->setCustomerPhoto($custPic)->save();
        $this->_redirect('customer/account/editPost/');
        
    }
    
    public function previewimageAction()
    {
        if($_FILES['imgfile']['error'] == 1){
             echo "filetype";
             exit;
        }  
        $file_size = $_FILES['imgfile']['size'];
        if($file_size > 2097152) {
            echo "image size!";
            exit;
        }
        
        $exts = explode(".", $_FILES['imgfile']['name']) ;
        $n = count($exts)-1;
        $exts = strtolower($exts[$n]);                        
        if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
        {

        } else {
                    echo "filetype";
                    exit;

        }
        $uploader = new Varien_File_Uploader('imgfile');
        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $media_path  = Mage::getBaseDir('media').'/emailcommunication'. DS;                
        $uploader->save($media_path, $_FILES['imgfile']['name']);
        $data['imgfile'] = Mage::getBaseUrl('media').'emailcommunication/'. $_FILES['imgfile']['name'];
        
        echo $data['imgfile'];
        Mage::getSingleton('core/session')->setAttachImage($data['imgfile']);
        
        exit;
    }
    public function vendornotePostAction()
	{
		$id = $this->getRequest()->getParam('id');
		$_vendornote = $this->getRequest()->getParam('vendornote');
		if($_vendornote == '')
			{
			Mage::getSingleton('customer/session')->addError($this->__('You have not entered any note below. Please enter text in the note to seller'));
            $this->_redirect('sales/order/history/');
			}
		else
			{
		$_vendornote1 = '<table border="0" width="750"><tr><td style="font-size: 17px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">'.$_vendornote.'</td></table>';
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment')->load($id);
		$storeId = Mage::app()->getStore()->getId();
		//$session = $this->_getSession();
		$path = Mage::getBaseDir('media') . DS . 'vendorreplyemail' . DS;
		
		//echo $_FILES["file"]["type"];
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		 $extension = end(explode(".", $_FILES["file"]["name"]));
		 if($_FILES["file"]["type"]=="image/jpeg")
		 /*if ((($_FILES["file"]["type"] == "image/gif")
		 || ($_FILES["file"]["type"] == "image/jpeg")
		 || ($_FILES["file"]["type"] == "image/jpg")
		 || ($_FILES["file"]["type"] == "image/pjpeg")
		 || ($_FILES["file"]["type"] == "image/x-png")
		 || ($_FILES["file"]["type"] == "image/png"))
			&& ($_FILES["file"]["size"] < 50000)
			&& in_array($extension, $allowedExts))
		*/		  {
			
				 if ($_FILES["file"]["error"] > 0)
					{
						
					echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
					}
				 else
					{
				   echo "Upload: " . $_FILES["file"]["name"] . "<br>";
				   echo "Type: " . $_FILES["file"]["type"] . "<br>";
					//echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
					//echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";exit;
				
				if (file_exists($path . $_FILES["file"]["name"]))
				  {
				  echo $_FILES["file"]["name"] . " already exists. ";
				  }
				else
				  {
				  
				  move_uploaded_file($_FILES["file"]["tmp_name"],$path . $_FILES["file"]["name"]);
				 // echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
				  $tmp = $path . $_FILES["file"]["name"];
				   //echo $tmp;				  
				   }
				  }
				}
		else
		 {
		 echo "Invalid file";
		 }
		
		 $dirpath = Mage::getBaseUrl('media') . DS . 'vendorreplyemail' . DS;
		 $_path = '<img src="'.$dirpath.$_FILES["file"]["name"].'">';
		$templateId = 'vendornote_reminder_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$customer = Mage::getModel('customer/customer');	
		
		$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		$shipmentData = '';
		$shipmentData = $shipment->load($id);
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		$orderEmail = $customerData->getCustomerEmail();
		$incmntId = $shipmentData->getIncrementId();
		$currencysym = Mage::app()->getLocale()->currency($customerData->getBaseCurrencyCode())->getSymbol();
		$_items = $shipment->getAllItems();
		//echo '<pre>';print_r($_items);exit;
		foreach ($_items as $_item)
				{
		//		$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
$product = Mage::helper('catalog/product')->loadnew($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incmntId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";		
		$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_vendornote1,
					'imagecust' => $_path,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
				);
		
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))->setReplyTo($orderEmail);
				//->sendTransactional($templateId, $sender, $orderEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		
		$_email->sendTransactional($templateId, $sender, $vendorEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
	//	$_email->sendTransactional($templateId, $sender, 'places@craftsvilla.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addSuccess('Sent Email To Vendor Sucessfully for your shipment :'.$shipmentData->getIncrementId());
			}
	}
	
public function disputeraisedAction()
	{
		$id = $this->getRequest()->getParam('id');
		$_custnote = $this->getRequest()->getParam('vendornote1');
		if($_custnote == '')
			{
			Mage::getSingleton('customer/session')->addError($this->__('You have not entered any note below. Please enter text in the note to seller'));
            $this->_redirect('sales/order/history/');
			}
		else
			{
		
		$_custnote1 = '<table border="0" width="750"><tr><td style="font-size: 17px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">'.$_custnote.'</td></table>';
		
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment');
		$shipmentid = $shipment->getIncrementId(); 
		$storeId = Mage::app()->getStore()->getId();
		$shipmentData = $shipment->load($id);
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		$vendortelephone = $vendorDataCust->getTelephone();
		$vendorStreet = $vendorDataCust->getStreet();
		$vendorAddress = json_encode($vendorStreet);
		$vendorCity = $vendorDataCust->getCity(); 
		$vendorCountryId = $vendorDataCust->getCountryId();
		
		
		$_target = Mage::getBaseDir('media') . DS . 'emailcommunication' . DS;
			$targetimg = Mage::getBaseUrl('media').'emailcommunication/';
			$name = $_FILES['file']['name'];
			$source_file_path = basename($name);
			$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
			$path = $_target.$newfilename;
			$path1 = $targetimg.$newfilename;
			$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
			file_put_contents($path, file_get_contents($_FILES['file']['tmp_name']));
		
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		//print_r($customerData); exit;
		$custname = $customerData->getCustomerFirstname().' '.$customerData->getCustomerLastname();
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$custphone = "select `telephone` from `sales_flat_order_address` where `parent_id`=".$shipmentData->getOrderId();
		$custphoneresult = $read->query($custphone)->fetch(); 
		$customertelephone = $custphoneresult['telephone'];
		$orderEmail = $customerData->getCustomerEmail();
		$customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('email',$orderEmail);
		
		foreach($customer as $_customer)
		{
		  $custid = $_customer['entity_id'];
		}
	
		$model = Mage::getModel('disputeraised/disputeraised');
		  $model->setIncrementId($shipmentData->getIncrementId())
		        ->setCustomerId($custid)
		        ->setVendorId($shipmentData->getUdropshipVendor())
		        ->setImage($pathhtml)
		        ->setContent($_custnote)
		        ->setAddedby($custname)
		        ->setStatus('3')
		        ->setCreatedAt(NOW())
		        ->setUpdatedAt(NOW())
		        ->save();
		   $write = Mage::getSingleton('core/resource')->getConnection('core_write');
		        $disputestatus = "update `disputeraised` set `status` = 3 where `increment_id` = '".$shipmentData->getIncrementId()."'";
				$writequery = $write->query($disputestatus);	    
		 $incmntId = $shipmentData->getIncrementId();
		$currencysym = Mage::app()->getLocale()->currency($customerData->getBaseCurrencyCode())->getSymbol();
		$_items = $shipment->getAllItems();
		$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		foreach ($_items as $_item)
				{
				//$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
				$product = Mage::helper('catalog/product')->loadnew($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incmntId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";
			
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'vendordisputenote_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$model1  = Mage::getModel('disputeraised/disputeraised')->getCollection()->addFieldToFilter('increment_id',$shipmentData->getIncrementId())->setOrder('id', 'DESC');;
		
		$html = '';
		$html .= '<div><table style="border-collapse: collapse;border: 1px solid black;"><tr><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Image</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Content</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Added By</font></th></tr>';
		
		foreach ($model1 as $_model)
		{
            $incrementid = $_model->getIncrementId();
           
            $html .= '<tr><td style="border-collapse: collapse;border: 1px solid black;">'.$_model['image'].'</td><td width="600px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['content'].'</td><td width="250px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['addedby'].'</td></tr>';
		}
		 $html .= '</table></div>';
		$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_custnote1,
					'imagecust' => $pathhtml,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
					'html' => $html,
					'customertelephone' => $customertelephone,
		            'customeremail' => $orderEmail,
				);
		if ($shipmentData->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DISPUTE_RAISED) {
				
					$shipmentData->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DISPUTE_RAISED);
					Mage::helper('udropship')->addShipmentComment($shipmentData,
							  ('Customer has raised the dispute for this shipment'));
						
							}
					$shipmentData->save();
		$_email->sendTransactional($templateId, $sender, $vendorEmail, $vendorName, $vars, $storeId);
		$_email->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', '', $vars, $storeId);
		
		$storeId1 = Mage::app()->getStore()->getId();
		$templateId1 = 'disputeraisebycustomer_email_template';
		$sender1 = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email1 = Mage::getModel('core/email_template');
	
		$vars1 = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_custnote1,
					'imagecust' => $pathhtml,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
					'html' => $html,
		             'vendorEmail'=>$vendorEmail,
		             'vendortelephone' => $vendortelephone,
		             'vendorAddress' => $vendorAddress,
		             'vendorCity' => $vendorCity,
		             'vendorCountryId' => $vendorCountryId,
				);
				//$_email1->sendTransactional($templateId1, $sender1, 'gsonar8@gmail.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars1, $storeId1);
		$_email1->sendTransactional($templateId1, $sender1, $orderEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars1, $storeId1);
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addSuccess('Sent Email To Vendor Sucessfully for your shipment :'.$shipmentData->getIncrementId());
		
	}
	}
	
	public function customerclosedisputeAction()
	{
		$id = $this->getRequest()->getParam('id');
			
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment');
		$shipmentid = $shipment->getIncrementId();
		$storeId = Mage::app()->getStore()->getId();
		$shipmentData = $shipment->load($id);
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		$_target = Mage::getBaseDir('media') . DS . 'emailcommunication' . DS;
			$targetimg = Mage::getBaseUrl('media').'emailcommunication/';
			$name = $_FILES['file']['name'];
			$source_file_path = basename($name);
			$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
			$path = $_target.$newfilename;
			$path1 = $targetimg.$newfilename;
			$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
			file_put_contents($path, file_get_contents($_FILES['file']['tmp_name']));
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		$orderEmail = $customerData->getCustomerEmail();
		$orderid = $customerData->getEntityId();
		$customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('email',$orderEmail);
		foreach($customer as $_customer)
		{
		  $custid = $_customer['entity_id'];
		}
		
		
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$disputeQuery = "update `disputeraised` set `status` = 2 where `increment_id` = ".$shipmentData->getIncrementId();
			$disputeQueryresult = $write->query($disputeQuery);
			
	
		 $incmntId = $shipmentData->getIncrementId();
		$currencysym = Mage::app()->getLocale()->currency($customerData->getBaseCurrencyCode())->getSymbol();
		$_items = $shipment->getAllItems();
		$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		foreach ($_items as $_item)
				{
				//$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
				$product = Mage::helper('catalog/product')->loadnew($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incmntId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";	
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'vendorclosedispute_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$read = Mage::getSingleton('core/resource')->getConnection('udropship_read');	
		$getshipmentQuery = "SELECT `entity_id` FROM `sales_flat_shipment` where `increment_id` = '".$incmntId."'";
	    $getshipmentQueryResult = $read->query($getshipmentQuery)->fetch();
	    $shipentityid = $getshipmentQueryResult['entity_id'];
	    $orderpayment = "select `method` from `sales_flat_order_payment` where `parent_id` = '".$orderid."'";
	    $orderpaymentResult = $read->query($orderpayment)->fetch();
	    $ordermethod = $orderpaymentResult['method'];
		$model1  = Mage::getModel('disputeraised/disputeraised')->getCollection()->addFieldToFilter('increment_id',$shipmentData->getIncrementId())->setOrder('id', 'DESC');;
		
		$html = '';
		$html .= '<div><table style="border-collapse: collapse;border: 1px solid black;"><tr><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Image</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Content</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Added By</font></th></tr>';
		foreach ($model1 as $_model)
		{
            $incrementid = $_model->getIncrementId();
           
            $html .= '<tr><td style="border-collapse: collapse;border: 1px solid black;">'.$_model['image'].'</td><td width="600px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['content'].'</td><td width="250px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['addedby'].'</td></tr>';
		}
		 $html .= '</table></div>';
       
		$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_custnote1,
					'imagecust' => $pathhtml,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
		            'html' => $html,
				);
		//print_r($vars);exit;	
		//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))->setReplyTo($orderEmail);
				
			if ($shipmentData->getUdropshipStatus()== Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DISPUTE_RAISED) {
				
					$getshipmenttrack = "SELECT `number` FROM `sales_flat_shipment_track` where `parent_id` = ".$shipentityid;
	   			$getshipmenttrackResult = $read->query($getshipmenttrack)->fetch();
	   			$tracknumber = $getshipmenttrackResult['number'];
	   			if($tracknumber=='' || empty($tracknumber) || $tracknumber==NULL)
	   			{ 
	   				$shipmentData->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PROCESSING);
	   			}
	   			elseif($ordermethod=='cashondelivery')
				{
					
	   				$shipmentData->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
	   			}
	   			else {
	   				
					$shipmentData->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
	   			}
					Mage::helper('udropship')->addShipmentComment($shipmentData,
							  ('Customer has closed the dispute for this shipment'));
						
							}
					$shipmentData->save();
		$_email->sendTransactional($templateId, $sender, $orderEmail, $customerData->getCustomerFirstname(), $vars, $storeId);
		
		$_email->sendTransactional($templateId, $sender, $vendorEmail, $vendorName, $vars, $storeId);
		$_email->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('core/session')->addSuccess('Sent Email To Vendor Sucessfully for your shipment :'.$shipmentData->getIncrementId());
		
	}
	

public function customerbankaccountAction() 
	{
		$id = $this->getRequest()->getParam('id');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment');
		$storeId = Mage::app()->getStore()->getId();
		$shipmentData = $shipment->load($id);
		//print_r($shipmentData); exit;
		$shipmentid = $shipmentData->getIncrementId();
		$shipmentQty = round($shipmentData->getTotalQty()); 
		$orderId = $shipmentData->getOrderId();
		$entityId = $shipmentData->getEntityId();
		$status = $shipmentData->getUdropshipStatus();
		$paymentamount = Mage::helper('udropship')->getrefundCv($entityId);	
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		
		
		$trackingQuery = "SELECT * FROM `sales_flat_shipment_track` WHERE `parent_id` = '".$entityId."'";
		$trackingQueryRes = $read->query($trackingQuery)->fetch(); //print_r($trackingQueryRes); exit;
		$trackNumber = $trackingQueryRes['number']; 
		
		$vendortelephone = $vendorDataCust->getTelephone();
		$vendorStreet = $vendorDataCust->getStreet();
		$vendorAddress = json_encode($vendorStreet);
		$vendorCity = $vendorDataCust->getCity(); 
		$vendorCountryId = $vendorDataCust->getCountryId();
		$_order = $shipment->getOrder();
		//$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
		$_items = $shipment->getAllItems();
		//echo '<pre>'; print_r($_items); exit;
		$currencysym = Mage::app()->getLocale()->currency($_order->getBaseCurrencyCode())->getSymbol();
		$shipmentDetails = '';
		$shipmentDetails .= "<table border='0' width='1000px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				//echo $_item['product_id'];exit;
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
				//print_r($product); exit;
			try{				
			$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
			   }
			catch(Exception $e){}
			$shipmentDetails .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipmentid."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$shipmentDetails .= "</table>";
//print_r($shipmentDetails); exit;
		
		$returncouriername = mysql_escape_string($this->getRequest()->getParam('returncouriername')); 
		$returntracknumber = mysql_escape_string($this->getRequest()->getParam('returntracknumber')); 
		if($returntracknumber == $trackNumber) { 
		
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addError('Please enter the TrackingNumber and CourierName by which you are returning the product to the Seller.');
		return;
		
		}
		
		/*if(!preg_match("/^[A-Z][0-9][a-z]*$/",$returntracknumber)) {
				$this->_redirect('sales/order/history/');
				Mage::getSingleton('customer/session')->addError('Please enter valid tracking number.');
				return;
		}*/
		$nameonbankaccount1 = mysql_escape_string($this->getRequest()->getParam('nameonbankaccount')); 
		$nameonbankaccount = preg_replace('/[^a-zA-Z0-9]+/',' ',$nameonbankaccount1);
		
		$ifscCode = str_replace(' ','',strtoupper(mysql_escape_string($this->getRequest()->getParam('bankifsccode'))));
		$firstFourChar = substr($ifscCode,0,4);
		$fifthChar = $ifscCode[4];  
		for($i = 0; $i < strlen($firstFourChar); $i++) 
		{
			if((!preg_match("/^[A-Z]*$/",$firstFourChar[$i])) || ($fifthChar != '0')) 
			{ 
				$this->_redirect('sales/order/history/');
				Mage::getSingleton('customer/session')->addError('Please enter First 4 Characters of IFSC code are alphabets followed by zero.');
				return;
			}
		}
		$ifscLength = strlen($ifscCode);
		if($ifscLength < 11) 
		{
			$this->_redirect('sales/order/history/');
			Mage::getSingleton('customer/session')->addError('Please enter valid 11 digit IFSC code');
			return;
		}
		$accountNumber1 = mysql_escape_string($this->getRequest()->getParam('accountnumber')); 
		$accountNumber = preg_replace('/[^0-9]+/','',$accountNumber1);
		 
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		$orderEmail = $customerData->getCustomerEmail();
		$orderid = $customerData->getEntityId();
		if(!preg_match("/^[0-9]*$/",$paymentamount)) 
		{
			$this->_redirect('sales/order/history/');
			Mage::getSingleton('customer/session')->addError('Please enter valid Payment Amount of numeric digits.');
			return;
		}
		$firstname = $customerData->getCustomerFirstname();
		$lastname = $customerData->getCustomerLastname();
		$customerName = mysql_escape_string($firstname.' '.$lastname); 
		
		$bankdetails = '';
		$duplicateRefundRequest = "SELECT * FROM `refundrequestedcustomer` WHERE `shipment_id` = '".$shipmentid."'"; 
		$duplicateRefundRequestRes = $read->query($duplicateRefundRequest)->fetch();
		if(!($duplicateRefundRequestRes))
		{ 
			
$custBankDetails = "INSERT INTO `refundrequestedcustomer`(`shipment_id`, `customer_name`, `account_number`, `name_on_account`, `ifsccode`, `trackingcode`, `couriername`, `created_at`, `refund_status`, `qty`) VALUES ($shipmentid,'$customerName','$accountNumber','$nameonbankaccount','$ifscCode','$returntracknumber','$returncouriername',Now(),1, '$shipmentQty')"; 
			 $writequeryCustomer = $write->query($custBankDetails);	    
			 
			Mage::helper('udropship')->addShipmentComment($shipmentData, ('Customer has Raised Dispute with Bank Account Details and Return Track Details.'));
 		$shipmentData->save(); 
			 
			 $read->closeConnection();
			 $write->closeConnection();
			 
			 $bankdetails .= '<table border="1">
			 <tr><td>CourierName</td><td>'.$returncouriername.'</td></tr>
			 <tr><td>AwbNumber</td><td>'.$returntracknumber.'</td></tr>
			 <tr><td>NameOnAccount</td><td>'.$nameonbankaccount.'</td></tr>
			 <tr><td>IFSCCode</td><td>'.$ifscCode.'</td></tr>
			 <tr><td>AccountNumber</td><td>'.$accountNumber.'</td></tr>
			 </table>';
			 
			 $returnTrackDetails = '';
			 $returnTrackDetails .= '<table border="1">
			 <tr><td>CourierName</td><td>'.$returncouriername.'</td></tr>
			 <tr><td>AwbNumber</td><td>'.$returntracknumber.'</td></tr></table>';
			 
			 
			  
			  
			  $templateId ='customer_bankaccount_details_template';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Successfully submitted bank account details for shipment.'.$shipmentid;
							$vars = Array( 'shipmentid'=>$shipmentid,
										   'firstname'=>$firstname,
										   'shipmentDetails'=>$shipmentDetails,
										   'bankdetails'=>$bankdetails,
										   );
							//print_r($vars); exit;
							$_email->setTemplateSubject($mailSubject)
							 	->setReplyTo($sender['email'])
								->sendTransactional($templateId, $sender,$orderEmail,'', $vars);
								echo "email sent successfully to your email";
			
			$templateIdVendor ='disputeraisecustomer_bankaccount_details_vendor_template';
							$senderVendor = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$_emailVendor = Mage::getModel('core/email_template');
							$mailSubjectVendor = 'Successfully submitted ReturnTrack and Bank Account Details for shipment.'.$shipmentid;
							$varsVendor = Array( 'shipmentid'=>$shipmentid,
										   'vendorName'=>$vendorName,
										   'returnTrackDetails'=>$returnTrackDetails,
										   'shipmentDetails'=>$shipmentDetails,
										   
										   );
							//print_r($varsVendor); exit;
							$_emailVendor->setTemplateSubject($mailSubjectVendor)
							 	->setReplyTo($senderVendor['email'])
								->sendTransactional($templateIdVendor, $senderVendor,$vendorEmail,'', $varsVendor);
								echo "email sent successfully to your email";
							
				$this->_redirect('sales/order/history/');
		      Mage::getSingleton('customer/session')->addSuccess('Bank Account Details Are successfully submitted for shipment:'.$shipmentData->getIncrementId().'. Our Customercare team will review your refund request. For any further queries please contact customercare@craftsvilla.com ');	
			 
		 } 
	     else 
	     { 
			  $this->_redirect('sales/order/history/');
			  Mage::getSingleton('customer/session')->addError('Duplicate ShipmentID. Bank Account Details Are already submitted for shipment:'.$shipmentData->getIncrementId());

		 }
	}


	
	public function customersurveyAction()
{
   //echo "Hello"; exit;
	$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
	$baseUrl =  Mage::getBaseUrl(); 
	$filePath = "craftsvillacustomer/index/customersurveycapture";


		   $eid = Mage::app()->getRequest()->getParam('shipid');
         $ref_n = Mage::app()->getRequest()->getParam('ref_n');

    if(!is_numeric($eid) || !is_numeric($ref_n)) exit;
  
         $hlp1 = Mage::helper('generalcheck');
         $statsconn1 = $hlp1->getStatsdbconnection();

       $cseDetails="SELECT * FROM craftsvilla_auth where shipment_id='".$eid."' and ref_id='".$ref_n."' and auth_ref='CSE'";
    
      $cseDetailsRes=mysql_query($cseDetails,$statsconn1);

      $cseRes=mysql_fetch_array($cseDetailsRes);
      mysql_close($statsconn1);
   // print_r($cseRes);exit;

    $ship_id= $cseRes['shipment_id'];
    $rand_n= $cseRes['ref_id'];



if(($eid == $ship_id) && ($ref_n == $rand_n)){
        
         $csf='CSF';
         $randNum = rand(pow(10,13),pow(10,14)-1);
         $statsconn2 = $hlp1->getStatsdbconnection();
         $insertQuery=mysql_query("INSERT INTO craftsvilla_auth(shipment_id,ref_id,auth_ref) VALUES('".$ship_id."','".$randNum."','".$csf."')",$statsconn2);
         mysql_close($statsconn2);
         $welcomePath =  $baseUrl.$filePath."?shipid=".$eid."&ref_n=".$randNum;
 
         $productDetails = Mage::getModel('sales/order_shipment')->load($eid);
		   $vendorid =$productDetails->getUdropshipVendor();
         $incrementid=$productDetails->getIncrementId();
         
			$vdname =Mage::getModel('udropship/vendor')->load($vendorid);

			$vname = $vdname['vendor_name']; 

			$all = $productDetails->getAllItems();

				foreach($all as $ab) {

				   $pid = $ab->getProductId();

				   $pname = $ab->getName().'<br>';

//	$vendorid =$ab->getUdropshipVendor();

				}

			$product =Mage::getModel('catalog/product')->load($pid);

$feedbackForm='';
	
$feedbackForm .="<style type='text/css'>
		#p1{
			
			font-size:12px;	
		}
		
		.p{
			font-weight:bold;
			font-size:12px;	
		}
		label{
			font-size:11px;
			 display:inline; 
			
		}
		#form{
			width:600px; 
			height:700px;			
		}
		
		</style>";

$feedbackForm .="<script language='javascript' type='text/javascript'>

		function validate()
		{
	

			var r1=document.getElementById('price1').checked;
			var r2=document.getElementById('price2').checked;
			var r3=document.getElementById('price3').checked; 
			var r4=document.getElementById('price4').checked; 
			var r5=document.getElementById('price5').checked;

			var q1=document.getElementById('quality1').checked;
			var q2=document.getElementById('quality2').checked;
			var q3=document.getElementById('quality3').checked; 
			var q4=document.getElementById('quality4').checked; 
			var q5=document.getElementById('quality5').checked;

			var si1=document.getElementById('sizing1').checked;
			var si2=document.getElementById('sizing2').checked;
			var si3=document.getElementById('sizing3').checked; 
			var si4=document.getElementById('sizing4').checked; 
			var si5=document.getElementById('sizing5').checked;

			var p1=document.getElementById('packaging1').checked;
			var p2=document.getElementById('packaging2').checked;
			var p3=document.getElementById('packaging3').checked; 
			var p4=document.getElementById('packaging4').checked; 
			var p5=document.getElementById('packaging5').checked;

			var e1=document.getElementById('experience1').checked;
			var e2=document.getElementById('experience2').checked;
			var e3=document.getElementById('experience3').checked; 
			var e4=document.getElementById('experience4').checked; 
			var e5=document.getElementById('experience5').checked;

			var s1=document.getElementById('satisfied1').checked;
			var s2=document.getElementById('satisfied2').checked;
			var s3=document.getElementById('satisfied3').checked; 
			var s4=document.getElementById('satisfied4').checked; 
			var s5=document.getElementById('satisfied5').checked;

			if(r1==false && r2==false &&  r3==false && r4==false && r5==false){
			alert('1.Please give your rate the price of the product?');
			return false;
			}

			if(q1==false && q2==false && q3==false && q4==false &&  q5==false){
			alert('2.Please give your rate the quality of the product?');
			return false;
			}

			if(si1==false && si2==false && si3==false && si4==false && si5==false){
			alert('3.Please give your sizing of the product?');
			return false;
			}

			if(p1==false && p2==false && p3==false &&  p4==false && p5==false){
			alert('4.Please give your quality of the packaging?');
			return false;
			}

			if(e1==false && e2==false &&  e3==false && e4==false && e5==false){
			alert('5.Please give your satisfaction of delivery time for your product?');
			return false;
			}

			if(s1==false && s2==false &&  s3==false && s4==false && s5==false){
			alert('6.Please give your rate your purchase experience?');
			return false;
			}



		return true;
		}";

$feedbackForm .="</script>";

$feedbackForm .="<div align='center'> ";

$feedbackForm .="<form name='custform' action='" .$welcomePath. "' method='post' id='form' >
			<fieldset>
			<table id='table' align='center'>	
				<tr><td align='center'> <img id='image' src='http://assets1.craftsvilla.com/banner-craftsvilla/craftsvilla_logo_marketplace_300x60.jpg' align='center'/> </td></tr>
				<tr><td><h2 align='center' style='color:brown'> Customer Purchase Survey </h2></td></tr>
				<tr><td><p id='p1' align='left'>You recently ordered <b>" .strip_tags($pname). "</b> from <b>" .strip_tags($vname). "</b> at <b>Craftsvilla</b>. We would love to get your feedback so that we can improve and serve you better.</p> <br>	</td></tr>
			
				<tr><td> <p class='p'> 1. How would you rate the price of the product? </p> </td></tr><br>
				<tr><td>

						<input type='hidden' value='" .$incrementid. " name='ship' />
						<!--<input type='hidden' value='".$pid. "' name='pid'>-->
						<input type='hidden' value=" .$rdcv." name='rand' />

						<input type='radio' name='q1' id='price1' value='0' /><label>Very Expensive</label>
						<input type='radio' name='q1' id='price2' value='1' /><label>Expensive</label>
						<input type='radio' name='q1' id='price3' value='2' /><label>OK</label>
						<input type='radio' name='q1' id='price4' value='3' /><label>Good Value<label>
						<input type='radio' name='q1' id='price5' value='4' /><label>Excellent Value </label><br><br></td></tr>
				<tr><td> <p class='p'>2. How would you rate the quality of the product?</p></td></tr>
				<tr><td>
					<input type='radio' name='q2' id='quality1' value='0' /><label>Very poor</label>
					<input type='radio' name='q2' id='quality2' value='1' /><label>Poor</label>
					<input type='radio' name='q2' id='quality3' value='2' /><label>OK</label>
					<input type='radio' name='q2' id='quality4' value='3' /><label>High quality</label>
					<input type='radio' name='q2' id='quality5' value='4' /><label>Excellent quality</label><br><br>
				</td></tr>
				<tr><td><p class='p'>3. How was the sizing of the product (for apparel)?</p></td></tr>
				<tr><td>
					<input type='radio' name='q3' id='sizing1' value='0' /><label>Very poor fit</label>
					<input type='radio' name='q3' id='sizing2' value='1' /><label>Poor fit</label>
					<input type='radio' name='q3' id='sizing3' value='2' /><label>OK</label>
					<input type='radio' name='q3' id='sizing4' value='3' /><label>Good fit</label>
					<input type='radio' name='q3' id='sizing5' value='4' /><label>Excellent fit</label><br><br>
				</td></tr>
				<tr><td><p class='p'>4. How was the quality of the packaging?</p></td></tr>
				<tr><td>
					<input type='radio' name='q4' id='packaging1' value='0' /><label>Very poor</label>
					<input type='radio' name='q4' id='packaging2' value='1' /><label>Poor</label>
					<input type='radio' name='q4' id='packaging3' value='2' /><label>OK</label>
					<input type='radio' name='q4' id='packaging4' value='3' /><label>High quality</label>
					<input type='radio' name='q4' id='packaging5' value='4' /><label>Excellent quality</label><br><br>
				</td></tr>
				<tr><td><p class='p'>5. How satisfied were you with the delivery time for your product?</p></td></tr>
				<tr><td>
					<input type='radio' name='q5' id='satisfied1' value='0' /><label>Very unsatisfied</label>
					<input type='radio' name='q5' id='satisfied2' value='1' /><label>Unsatisfied</label>
					<input type='radio' name='q5' id='satisfied3' value='2' /><label>OK<label>
					<input type='radio' name='q5' id='satisfied4' value='3' /><label>Satisfied</label>
					<input type='radio' name='q5' id='satisfied5' value='4' /><label>Very satisfied</label><br><br>
				</td></tr>
				<tr><td><p class='p'>6. Overall how would you rate your purchase experience?</p></td></tr>
				<tr><td>
					<input type='radio' name='q6' id='experience1' value='0' /><label>Very Poor</label>
					<input type='radio' name='q6' id='experience2' value='1' /><label>Poor</label>
					<input type='radio' name='q6' id='experience3' value='2' /><label>OK</label>
					<input type='radio' name='q6' id='experience4' value='3' /><label>Good</label>
					<input type='radio' name='q6' id='experience5' value='4' /><label>Excellent</label><br><br><br>
				</td></tr>
				<tr><td align='center'>
					<input type='submit' name='submit' value='Submit Form' onclick='return validate()'/><br/><br/>
				</td></tr>";
			$feedbackForm .="</table></fieldset></form></div>"; 

 

			echo $feedbackForm;
			exit;
         
 
 }else{
   echo 'No feedback found.';
}

		
}
	
public function customersurveycaptureAction()
{
	
   $eid = Mage::app()->getRequest()->getParam('shipid');
   $ref_n = Mage::app()->getRequest()->getParam('ref_n');
      
     if(!is_numeric($eid) || !is_numeric($ref_n)) exit;

         $hlp1 = Mage::helper('generalcheck');
         $statsconn3 = $hlp1->getStatsdbconnection();
     $cseDetails="SELECT * FROM craftsvilla_auth where shipment_id='".$eid."' and ref_id='".$ref_n."' and auth_ref='CSF'";
    
$cseDetailsRes=mysql_query($cseDetails,$statsconn3);

      $cseRes=mysql_fetch_array($cseDetailsRes);
mysql_close($statsconn3);
   // print_r($cseRes);exit;

   $ship_id= $cseRes['shipment_id'];
   $rand_n= $cseRes['ref_id'];


//echo $eid = $ship_id;
//echo $ref_n = $rand_n;

if(($eid == $ship_id) && ($ref_n == $rand_n)){

		if(isset($_POST['submit']))
		{

			$q1=$_POST['q1'];
			$q2=$_POST['q2'];
			$q3=$_POST['q3'];
			$q4=$_POST['q4'];
			$q5=$_POST['q5'];
			$q6=$_POST['q6'];
	
         $hlp1 = Mage::helper('generalcheck');
         $statsconn4 = $hlp1->getStatsdbconnection();
			$shipmentId=$eid;
			//$var .= $ship.'&nbsp;';	
			
			$insertSuveryQuery="INSERT INTO customer_survey(shipment_id,Q1,Q2,Q3,Q4,Q5,Q6) VALUES ('$shipmentId','$q1','$q2','$q3','$q4','$q5','$q6')";
			$res=mysql_query($insertSuveryQuery,$statsconn4);
         
         mysql_close($statsconn4);
 
   
  

}
 echo "<p align='center'>Thank you for your feedback.<br>Craftsvilla.com</p>";
}else{

   echo 'No Feedback Found.';
}


}


public function customerprepaidtrackdetailsAction() {
		$id = $this->getRequest()->getParam('id');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment');
		$shipmentData = $shipment->load($id);
		//echo '<pre>'; print_r($shipmentData); exit;
		$shipmentid = $shipmentData->getIncrementId();
		$orderId = $shipmentData->getOrderId();
		$entityId = $shipmentData->getEntityId();
		$status = $shipmentData->getUdropshipStatus();
		$_order = $shipmentData->getOrder();
		//$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
		$_items = $shipment->getAllItems();
		//echo '<pre>'; print_r($_items); exit;
		$currencysym = Mage::app()->getLocale()->currency($_order->getBaseCurrencyCode())->getSymbol();
		
		$trackingQuery = "SELECT * FROM `sales_flat_shipment_track` WHERE `parent_id` = '".$entityId."'";
		$trackingQueryRes = $read->query($trackingQuery)->fetch(); //print_r($trackingQueryRes); exit;
		$trackNumber = $trackingQueryRes['number']; 
		
		$shipmentDetails = '';
		$shipmentDetails .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				//echo $_item['product_id'];exit;
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
				//print_r($product); exit;
			try{				
			$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
			   }
			catch(Exception $e){}
			$shipmentDetails .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipmentid."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$shipmentDetails .= "</table>";
//print_r($shipmentDetails); exit;
		
		$customerData = Mage::getModel('sales/order')->load($orderId); 
		$orderEmail = $customerData->getCustomerEmail();
		$orderid = $customerData->getEntityId();
		$firstname = $customerData->getCustomerFirstname();
		
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		
		$returncouriername = mysql_escape_string($this->getRequest()->getParam('returncouriername')); 
		$returntracknumber = mysql_escape_string($this->getRequest()->getParam('returntracknumber'));
		
		if($returntracknumber == $trackNumber) {  
		//echo 'eerrrrr'; exit;
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addError('Please enter the TrackingNumber and CourierName by which you are returning the product to the Seller.');
		return;
		
		}
		//echo 'succcccccc'; exit;
		$trackdetails .= '<table border="1">
			 <tr><td>CourierName</td><td>'.$returncouriername.'</td></tr>
			 <tr><td>AwbNumber</td><td>'.$returntracknumber.'</td></tr>
			 </table>';
			 
		
		$duplicateCheckQuery = "SELECT * FROM `customerreturn` WHERE `shipment_id` = '".$shipmentid."'";
		$duplicateCheckQueryRes = $read->query($duplicateCheckQuery)->fetch();
		//echo '<pre>'; print_r($duplicateCheckQueryRes); exit;
		if($duplicateCheckQueryRes) { 
		
		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addError('Duplicate ShipmentID. Return Track Details Are already submitted for shipment:'.$shipmentData->getIncrementId());
		} else {

		$customerReturnInsertQuery = "INSERT INTO `customerreturn`(`shipment_id`, `trackingcode`, `couriername`, `status`, `created_at`, `update_at`) VALUES ($shipmentid,'$returntracknumber','$returncouriername',$status,Now(),Now())";  
		$customerReturnInsertQueryRes = $write->query($customerReturnInsertQuery);
		

 		Mage::helper('udropship')->addShipmentComment($shipmentData, ('Customer has submitted Return Track Details with status Dispute Raised'));
 		$shipmentData->save();
		
		
		$read->closeConnection();
		$write->closeConnection();
		
							$templateId ='disputeraisedprepaid_track_email_to_customer';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Successfully submitted Return Track details for shipment.'.$shipmentid;
							$vars = Array( 'shipmentid'=>$shipmentid,
										   'firstname'=>$firstname,
										   'shipmentDetails'=>$shipmentDetails,
										   'returntrackdetails'=>$trackdetails,
										   );
							//print_r($vars); exit;
							$_email->setTemplateSubject($mailSubject)
							 	->setReplyTo($sender['email'])
								->sendTransactional($templateId, $sender,$orderEmail,'', $vars);
								echo "email sent successfully to your email";
							
							$templateIdVendor ='disputeraisedprepaid_track_email_to_vendor';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$_email = Mage::getModel('core/email_template');
							$mailSubjectVendor = 'Customer DisputeRaised with Return Track details for shipment.'.$shipmentid;
							$vars = Array( 'shipmentid'=>$shipmentid,
										   'vendorName'=>$vendorName,
										   'shipmentDetails'=>$shipmentDetails,
										   'returntrackdetails'=>$trackdetails,
										   );
							//print_r($vars); exit;
							$_email->setTemplateSubject($mailSubjectVendor)
							 	->setReplyTo($sender['email'])
								->sendTransactional($templateIdVendor, $sender,$vendorEmail,'', $vars);
								echo "email sent successfully to your email";
							
							

		$this->_redirect('sales/order/history/');
		Mage::getSingleton('customer/session')->addSuccess('Return Track Details Are successfully submitted for shipment:'.$shipmentData->getIncrementId().'. Our Customercare team will review your refund request. For any further queries please contact customercare@craftsvilla.com ');	
		
	}
	}
	
		
	/*public function create_captchaimageAction()
	{
		
		session_start();
		$md5_hash = md5(rand(0,999)); 
    //We don't need a 32 character long string so we trim it down to 5 
    $security_code = substr($md5_hash, 15, 5); 

    //Set the session to store the security code
  
   $_SESSION["catchacod_code159"] = $security_code;
    //Set the image width and height 
    $width = 100; 
    $height = 20;  

    //Create the image resource 
    $image = ImageCreate($width, $height);  

    //We are making three colors, white, black and gray 
    $white = ImageColorAllocate($image, 255, 255, 255); 
    $black = ImageColorAllocate($image, 0, 0, 0); 
    $grey = ImageColorAllocate($image, 204, 204, 204); 

    //Make the background black 
    ImageFill($image, 0, 0, $black); 

    //Add randomly generated string in white to the image
    ImageString($image, 3, 30, 3, $security_code, $white); 

    //Throw in some lines to make it a little bit harder for any bots to break 
    ImageRectangle($image,0,0,$width-5,$height-5,$grey); 
    imageline($image, 0, $height/6, $width, $height/6, $grey); 
    imageline($image, $width/6, 0, $width/6, $height, $grey); 
 
    //Tell the browser what kind of file is come in 
    header("Content-Type: image/jpeg"); 

    //Output the newly created image in jpeg format 
    ImageJpeg($image); 
    
    //Free up resources
    ImageDestroy($image); 
	}*/


	

}
