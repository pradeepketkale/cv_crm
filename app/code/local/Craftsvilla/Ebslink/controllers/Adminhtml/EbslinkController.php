<?php

class Craftsvilla_Ebslink_Adminhtml_EbslinkController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('ebslink/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Ebslink Deatils'), Mage::helper('adminhtml')->__('Ebslink Details'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
			
	}

	/**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ebslink/adminhtml_ebslink_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ebslink/ebslink')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data)) {
				$model->setData($data);
				
			}

			Mage::register('ebslink_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('ebslink/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('ebslink/adminhtml_ebslink_edit'))
				->_addLeft($this->getLayout()->createBlock('ebslink/adminhtml_ebslink_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ebslink')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		$data = $this->getRequest()->getPost();
		
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}
	  		
	  		$orderid = Mage::getModel('sales/order')->load($data['order_no'],'increment_id')->getId();
			$model = Mage::getModel('ebslink/ebslink');		
			
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'))
				->setOrderId($orderid)
				->setComment($this->getRequest()->getParam('comment'));
				
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				//added By dileswar for call another action EBSLINK
				/*$this->sendemailAction($data['order_no']);*/

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ebslink')->__('Ebslink was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
		
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ebslink')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
	// here I have to write the email Actionnnnnnnnnnn Dileswar
	public function sendemailAction($orderId)
		{
		$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
		$order = $read->query("SELECT sfo.`entity_id` as entity_id,es.order_no,sfo.`customer_firstname` as custfirstname,es.`ebslinkurl` as ebslinkurl,sfo.`customer_email` as email,sfoa.`telephone`,sfo.`order_currency_code`,sfo.`grand_total` as grandtotal FROM `sales_flat_order` as sfo
					  			LEFT JOIN `ebslink` as es
								ON es.`order_no` = sfo.`increment_id`
								LEFT JOIN `sales_flat_order_address` as sfoa
								ON sfoa.`parent_id` = sfo.`entity_id`
								WHERE es.`order_no`='".$orderId."'")->fetch();
			
			$_orderData = $order;
			$namecust = $_orderData['custfirstname'];
			$email = $_orderData['email'];
			$currency = Mage::app()->getLocale()->currency($_orderData['order_currency_code'])->getSymbol();
			$grandtotal = $_orderData['grandtotal'];
			$ebslinkurl =  $_orderData['ebslinkurl'];
			$entityid = $_orderData['entity_id'];
			$_customerTelephone = $_orderData['telephone'];
				
			$orderDataItem = $read->query("SELECT * from `sales_flat_order_item` as sfoi,`sales_flat_order` as sfo WHERE sfoi.`order_id` = sfo.`entity_id` and sfo.`increment_id` = '".$orderId."'")->fetchAll();
				foreach($orderDataItem as $_orderDataitem)
					{	
					$productname = $_orderDataitem['name'];
					$sku =  $_orderDataitem['sku'];
					$subtotal =  $_orderDataitem['base_row_total_incl_tax'];
					$qty = $_orderDataitem['qty_ordered'];
					}
		//sms to customer
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to customer
		$customerMessage = 'Payment Failed for Your Order on Craftsvilla.com. Payment Link Sent to Your Email Id#'.$email.'. Please Try Again to Pay for Your Pending Order - Thanks (Craftsvilla)';
		$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
				$av1 = 'Need Lower Shipping Charges';
					$av2 = 'Looking For COD';
					$av3 = 'Will Pay Later';
					$av4 = 'Need Better Pricing';
					$av5 = 'Not Interested';
					$av6 = 'Need Faster Delivery';
					$av7 = 'Need Details on Return/Refund Policy';
					$av8 = 'Need More Details on Products';
					
				//$action1 = 'http://local.craftsvilla.com/doejofinal/paymentresn.php?q='.$orderId;
				$urlactionday1 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av1;
				$urlactionday2 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av2;
				$urlactionday3 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av3;
				$urlactionday4 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av4;
				$urlactionday5 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av5;
				$urlactionday6 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av6;
				$urlactionday7 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av7;
				$urlactionday8 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av8;
				
		// Ebs Link url to Customer....		
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'ebslinks_email_template';
			$mailSubject = 'Action Required: Payment Link for Your Pending Order on Craftsvilla.com!';
			$sender = Array('name'  => 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
			$translate  = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$_email = Mage::getModel('core/email_template');
			$button = '<a href ="'.$urlactionday1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Lower Shipping Charges"> Need Lower Shipping Charges</button></a>';
			$button .= '<a href ="'.$urlactionday2.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="1" > Looking For COD</button></a>';
			$button .= '<a href ="'.$urlactionday3.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Will Pay Later" > Will Pay Later</button></a>';
			$button .= '<a href ="'.$urlactionday4.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Better Pricing" > Need Better Pricing</button></a>';
			$button .= '<a href ="'.$urlactionday5.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Not Interested" > Not Interested</button></a>';
			$button .= '<a href ="'.$urlactionday6.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Faster Delivery" > Need Faster Delivery</button></a>';
			$button .= '<a href ="'.$urlactionday7.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Details on Return/Refund Policy" > Need Details on Return/Refund Policy</button></a>';
			$button .= '<a href ="'.$urlactionday8.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="" > Need More Details on Products</button></a>';
			$vars = Array( 'entity_id' => $entityid ,'custfirstname' =>$namecust,'order' => $order,'orderno' =>$orderId,'email'=>$email,'name' => $productname,'grandtotal' =>$currency.$grandtotal,'ebslinkurl' =>$ebslinkurl,'base_row_total_incl_tax' =>$subtotal,'sku' =>$sku, 'qty_ordered'=>$qty, 'action' =>$button,
					);
				/*$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, 'gsonar8@gmail.com', $namecust, $vars, $storeId);*/
			
			
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, $email, $namecust, $vars, $storeId);
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, "manoj@craftsvilla.com", $namecust, $vars, $storeId);

			$translate->setTranslateInline(true);							
		}
		
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('ebslink/ebslink');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $ebslinkIds = $this->getRequest()->getParam('ebslink');
        if(!is_array($ebslinkIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ebslinkIds as $ebslinkId) {
                    $ebslink = Mage::getModel('ebslink/ebslink')->load($ebslinkId);
                    $ebslink->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ebslinkIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $ebslinkIds = $this->getRequest()->getParam('ebslink');
        if(!is_array($ebslinkIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ebslinkIds as $ebslinkId) {
                    $ebslink = Mage::getSingleton('ebslink/ebslink')
                        ->load($ebslinkId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($ebslinkIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function massCommentsAction()
    {
        $ebslinkIds = $this->getRequest()->getParam('ebslink');
        if(!is_array($ebslinkIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ebslinkIds as $ebslinkId) {
                    $ebslink = Mage::getSingleton('ebslink/ebslink')
                        ->load($ebslinkId)
                        ->setComment($this->getRequest()->getParam('comment'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($ebslinkIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

// For resend email For EBSlink....	
	public function resendemailAction(){
		
		$data = $this->getRequest()->getPost();
		$ebsOrderID = $data['ebslink'][0];
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$getEbsId = $read->query("select `order_no` from `ebslink` WHERE `ebslink_id` = '".$ebsOrderID."'")->fetch();
			$this->sendemailAction($getEbsId['order_no']);
			$this->_getSession()->addSuccess(
            $this->__('Email has resent successfully '));
	$this->_redirect('*/*/index');		
	}
	
	
	
}
