<?php
class Craftsvilla_Wholesale_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Wholesale"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "wholesale" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("wholesale", array(
                "label" => $this->__("Wholesale"),
                "wholesale" => $this->__("Wholesale")
		   ));

      $this->renderLayout(); 
	    
	}

	public function PostAction()
	{
		/*echo "<pre>";
		print_r($_POST);
		exit();*/

		$name = $this->getRequest()->getParam('name');
		$email = $this->getRequest()->getParam('email');
		$phone = $this->getRequest()->getParam('phone');
		$quantity = $this->getRequest()->getParam('quantity');
		$price = $this->getRequest()->getParam('offer_price');
		$custom = $this->getRequest()->getParam('custom');
		$day = $this->getRequest()->getParam('day');
		$month = $this->getRequest()->getParam('month');
		$year = $this->getRequest()->getParam('year');
		$expectedAt=$day.'-'.$month.'-'.$year;
		$expectedDate=date('Y-m-d',strtotime($expectedAt));
		
		//echo $expectedDate;exit;
		$date = date('d-m-Y',strtotime($_POST['created_date']));
		$comment = $this->getRequest()->getParam('comments');
		$gotourl = $this->getRequest()->getParam('gotourl');
		$productid = $this->getRequest()->getParam('productid');
		$vendorid = $this->getRequest()->getParam('vendorid');
		$productname = $this->getRequest()->getParam('productname');
		$productimage = $this->getRequest()->getParam('productimage');
		$producturl = $this->getRequest()->getParam('producturl');
		$sku = $this->getRequest()->getParam('sku');
		$vendorname = $this->getRequest()->getParam('vendorname');
		$status = '1';
		// added by dileswar on 20-11-2012
		$vendoremail = $this->getRequest()->getParam('vendoremail');
		$vendortelephone = $this->getRequest()->getParam('vendortelephone');
		$status = '1';// 1 indicates "Open"
		//$gotourl = Mage::getBaseUrl().$producturl;
	//insert the data on wholesale table
			
		$postData = $this->getRequest()->getPost();
        $model = Mage::getModel('wholesale/wholesale');
        $model->setWholesaleId($this->getRequest()->getParam('wholesale_id'))
            ->setProductid($postData['productid'])
			->setVendorid($postData['vendorid'])
			->setVendorname($postData['vendorname'])
			->setVendoremail($postData['vendoremail'])
			->setProductname($postData['productname'])
            ->setName($postData['name'])
			->setEmail($postData['email'])
			->setPhone($postData['phone'])
			->setQuantity($postData['quantity'])
			->setOffer_price($postData['offer_price'])
			->setCustom($postData['custom'])
			->setCreatedDate(now())
			->setExpectedDate($expectedDate)
			->setSku($sku)
			->setStatus($status)
			->setComments($postData['comments'])
<<<<<<< .mine
            ->save();
        		
=======
            ->setStatus($status)
			->save();
        	
>>>>>>> .r1612
		
		
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'wholesale_email_template';
		$templatevndId = 'wholesale_vendor_email_template';
		$translate  = Mage::getSingleton('core/translate');
		$mailSubject = 'You Have Received a New Wholesale Request From!';
		$mailvndSubject = 'Response For Your Wholesale Enquiry !';
		//$cc = 'wholesale@craftsvilla.com';
		$sender = Array('name'  => 'Craftsvilla Wholesale',
		'email' => 'wholesale@craftsvilla.com');
			
		//$translate  = Mage::getSingleton('core/translate');
		//$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
		$data = '';
		try {
			if(empty($data)){
				$vars = array('name' => $name,'email' => $email,'phone' => $phone,'quantity' => $quantity,'offer_price' => $price,'custom' => $custom,'day' => $day,'month' => $month,'year' => $year,'productname' => $productname,'producturl' => $producturl,'comments' => $comment);
				/*echo "<pre>";
				print_r($vars);*/
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
						->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
				$translate->setTranslateInline(true);
				$_email->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$cc,$vars,$storeId);
    			$translate->setTranslateInline(true);			
			//Added by dileswar on dated 20-11-2012 for send email to Vendor	
				$vars2 = array('name' => $name,'quantity' => $quantity,'offer_price' => $price,'custom' => $custom,'day' => $day,'month' => $month,'year' => $year,'productname' => $productname, 'producturl' => $producturl,'comments' => $comment);
				$_email->setTemplateSubject($mailvndSubject)->sendTransactional($templatevndId,$sender,$vendoremail,$vars2,$storeId);
    			$translate->setTranslateInline(true);			
				}
							
			//Mage::getSingleton('core/session')->addSuccess('Thank You for your Interest. Someone from our team will get back to you soon.');
			//$this->_redirect($gotourl);
			echo "wholesale submit!";
			}
			
			catch (Exception $e) {
				echo $e;
				if(empty($data)){
				$vars = array('name' => $name,'email' => $email,'phone' => $phone,'quantity' => $quantity,'offer_price' => $price,'custom' => $custom,'day' => $day,'month' => $month,'year' => $year,'productname' => $productname,'producturl' => $producturl);
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
						->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
						}	
			echo $e;
			
			Mage::getSingleton('core/session')->addError('Unable to send.');
			//$this->_redirect($gotourl);
			}
		
  }
<<<<<<< .mine
  
 }=======
 
  }>>>>>>> .r1612
