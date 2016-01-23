<?php

class Craftsvilla_Codrefundshipmentgrid_Adminhtml_CodrefundshipmentgridController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('codrefundshipmentgrid/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Codrefundshipmentgrid Details'), Mage::helper('adminhtml')->__('Codrefundshipmentgrid Details'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('codrefundshipmentgrid/adminhtml_codrefundshipmentgrid_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('codrefundshipmentgrid_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('codrefundshipmentgrid/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('codrefundshipmentgrid'), Mage::helper('adminhtml')->__('codrefundshipmentgrid'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('codrefundshipmentgrid'), Mage::helper('adminhtml')->__('codrefundshipmentgrid'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('codrefundshipmentgrid/adminhtml_codrefundshipmentgrid_edit'))
				->_addLeft($this->getLayout()->createBlock('codrefundshipmentgrid/adminhtml_codrefundshipmentgrid_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codrefundshipmentgrid')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
 public function massModifyAction() {
 
		$connwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connread = Mage::getSingleton('core/resource')->getConnection('core_read');
 		$codrefundshipmentgridIds = $this->getRequest()->getParam('codrefundshipmentgrid');
 		
 		//print_r($codrefundshipmentgridIds); exit;
 		$idGrid = $codrefundshipmentgridIds[0];
 		$setShipmentId = $this->getRequest()->getParam('shipmentId'); 
 		$setcustName = mysql_escape_string($this->getRequest()->getParam('custName'));
 		$setaccountNumber = mysql_escape_string($this->getRequest()->getParam('accountNumber'));
 		$setifscCode = str_replace(' ','',strtoupper(mysql_escape_string($this->getRequest()->getParam('ifscCode'))));
 		
 		$setamount = $this->getRequest()->getParam('amount');
 		
 		$codrefundshipmentgridData = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->load($idGrid)->getData();
 		//print_r($codrefundshipmentgridData); exit;
 		
 		$getshipmentId = $codrefundshipmentgridData['shipment_id'];
 		$getCustName = mysql_escape_string($codrefundshipmentgridData['cust_name']);
 		$getaccountno = mysql_escape_string($codrefundshipmentgridData['accountno']);
 		$getifsccode = mysql_escape_string($codrefundshipmentgridData['ifsccode']);
 		$getpaymentamount1 = $codrefundshipmentgridData['paymentamount'];
 		$getpaymentamount = $getpaymentamount1+100;
 		
 		$duplicateQuery = "SELECT * FROM `codrefundshipmentgrid` WHERE `shipment_id` = '".$setShipmentId."'";
 		$duplicateQueryRes = $connread->query($duplicateQuery)->fetch();
 		
 		$sfsquery = "SELECT * FROM `sales_flat_shipment` WHERE `increment_id` = '".$setShipmentId."'";
 		$sfsqueryResult = $connread->query($sfsquery)->fetch();
 		
 		if($sfsqueryResult) {
 		if($setShipmentId)
 			{
 				//To check duplicate shipmentID
 				if($duplicateQueryRes)
 				{
 				 	if($setShipmentId == $getshipmentId)
 				 	{
 				 		$lastShipmentId = $setShipmentId;
 				 	}
 				 	else 
 				 	{
 						$this->_redirect('*/*/');
						Mage::getSingleton('adminhtml/session')->addError("Shipment ID already exist");
 						$lastShipmentId = $getshipmentId;
 						return;	
 					}	 
 				}	
 				else 
 				{
 					$lastShipmentId = $setShipmentId;
 				}

 			}
 			else
 			{
 				$lastShipmentId = $getshipmentId;
 			}
 		}
 		else
 		{
 			$lastShipmentId = $getshipmentId;
 			Mage::getSingleton('adminhtml/session')->addError("Shipment ID does not exist");
 			$this->_redirect('*/*/');
 			return;
 		}
 		
 			
 			if($setcustName)
 			{
 				//To check specialcharacters replace with space in accountholder name
 			$lastcustName = preg_replace('/[^a-zA-Z0-9]+/',' ',$setcustName); 
 			}
	 		else
	 		{
	 			$lastcustName = $getCustName;
	 		}
	 		
	 		if($setaccountNumber)
 			{
 				
 			$lastaccountNumber = $setaccountNumber; 
 			}
	 		else
	 		{
	 			$lastaccountNumber = $getaccountno;
	 		}
	 		
	 		if($setifscCode)
 			{
 				//To check first four characters are alphabets and fifth character is 0 only in ifscCode field
 			
 			$firstFourChar = substr($setifscCode,0,4);
			$fifthChar = $setifscCode[4];  
			for($i = 0; $i < strlen($firstFourChar); $i++) 
			{
			
				if((!preg_match("/^[A-Z]*$/",$firstFourChar[$i])) || ($fifthChar != '0')) 
				{ 
					$this->_redirect('*/*/');
					Mage::getSingleton('adminhtml/session')->addError('Please enter First 4 Characters of IFSC code are alphabets followed by zero');
					return;
				}
				 
			}
				//To check length of ifsccode field not to exceed 11 
				$ifscLength = strlen($setifscCode);
				if($ifscLength < 11) 
				{
					$this->_redirect('*/*/');
					Mage::getSingleton('adminhtml/session')->addError('Please enter valid 11 digit IFSC code');
					return;
				}
				$lastifscCode = $setifscCode; 
 			}
			else
			{
			 	$lastifscCode = $getifsccode;
			}
 		
 			if($setamount)	
 			{
 				//To check only numeric values into price 
		 	if(!preg_match("/^[0-9]*$/",$setamount)) 
				{
					$this->_redirect('*/*/');
					Mage::getSingleton('adminhtml/session')->addError('Please enter valid Payment Amount of numeric digits.');
					return;
				}
		
				$lastamount = $setamount;		
 			
		 	} 
		 	else 
		 	{
		 		$lastamount = $getpaymentamount;		
		 	}

$updateCodQuery = "UPDATE `codrefundshipmentgrid` SET `shipment_id`= '".$lastShipmentId."'  ,`cust_name`='".$lastcustName."',`accountno`='".$lastaccountNumber."',`ifsccode`='".$lastifscCode."',`paymentamount`='".$lastamount."',`created_time`=now(),`update_time`=now() WHERE `codrefundshipmentgrid_id` = '".$idGrid."'"; //exit;
	 $connwrite->query($updateCodQuery);
	 $this->_redirect('*/*/index');
	 Mage::getSingleton('adminhtml/session')->addSuccess("CodRefund Details are modified successfully");
	
 }
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		$user = Mage::getSingleton('admin/session'); 
		$userFirstname = $user->getUser()->getFirstname();	
		$connread = Mage::getSingleton('core/resource')->getConnection('core_read');
		if ($data = $this->getRequest()->getPost()) {
			
			$refundedamount = $data['paymentamount'];
			$shipmentIncId = $data['shipment_id'];
			$bankholderName1 = mysql_escape_string($data['cust_name']);
			$ifscCode = str_replace(' ','',strtoupper(mysql_escape_string($this->getRequest()->getParam('ifsccode'))));
			
			$bankholderName = preg_replace('/[^a-zA-Z0-9]+/',' ',$bankholderName1); 
			
			$firstFourChar = substr($ifscCode,0,4);
			$fifthChar = $ifscCode[4];  
		for($i = 0; $i < strlen($firstFourChar); $i++) 
		{
			
			if((!preg_match("/^[A-Z]*$/",$firstFourChar[$i])) || ($fifthChar != '0')) 
			{ 
				$this->_redirect('*/*/');
				Mage::getSingleton('adminhtml/session')->addError('Please enter First 4 Characters of IFSC code are alphabets followed by zero for Shipment.'.$shipmentIncId);
				return;
			}
			 
		}
		
		$ifscLength = strlen($ifscCode);
		if($ifscLength < 11) 
		{
			$this->_redirect('*/*/');
			Mage::getSingleton('adminhtml/session')->addError('Please enter valid 11 digit IFSC code for Shipment.'.$shipmentIncId);
			return;
		}
		
		if(!preg_match("/^[0-9]*$/",$refundedamount)) 
		{
			$this->_redirect('*/*/');
			Mage::getSingleton('adminhtml/session')->addError('Please enter valid Payment Amount of numeric digits for Shipment.'.$shipmentIncId);
			return;
		}
		
			//$read = Mage::getSingleton('core/resource')->getConnection('couponrequest_read');
			$sfsquery = "SELECT * FROM `sales_flat_shipment` WHERE `increment_id` = '".$shipmentIncId."'";
			$resultquery = $connread->query($sfsquery)->fetch();
			if($resultquery)
			{
			
			
			$duplicateQuery = "SELECT * FROM `codrefundshipmentgrid` WHERE `shipment_id` = '".$shipmentIncId."'";
			$duplicateQueryRes = $connread->query($duplicateQuery)->fetch();
			if($duplicateQueryRes) 
			{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codrefundshipmentgrid')->__('Duplicate ShipmentID.'));
				$this->_redirect('*/*/');
				return;
			}
			else 
			{
				$model = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid');	
				//print_r($model); exit;	
				$model->setData($data)
					->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->setCustName($bankholderName);
				$model->setIfsccode($ifscCode);
				$model->save();
				$commentText = "Status has changes to Refund Todo & Amount is :Rs  ".$refundedamount." done by ".$userFirstname;
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
				$queryUpdate = "update shipmentpayout set `refundtodo`='".$refundedamount."' WHERE shipment_id = '".$shipmentIncId."'";
				$write->query($queryUpdate);
				
				$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncId);
				$shipment->setUdropshipStatus(23);
				Mage::helper('udropship')->addShipmentComment($shipment,$commentText);
                $shipment->save();
				
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codrefundshipmentgrid')->__('Shipment was successfully saved & The shipmentno."'.$shipmentIncId.'" status has changes to refund todo'));
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
          
         } 
         
         else{
				
				Mage::getSingleton('adminhtml/session')->addError($this->__('Shipment Id doesnot exist'));
				$this->_redirect('*/*/');
				return;
			}
         
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codrefundshipmentgrid')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid');
				 
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
        $codrefundshipmentgridIds = $this->getRequest()->getParam('codrefundshipmentgrid');
        if(!is_array($codrefundshipmentgridIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($codrefundshipmentgridIds as $codrefundshipmentgridId) {
                    $codrefundshipmentgrid = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->load($codrefundshipmentgridId);
                    $codrefundshipmentgrid->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($codrefundshipmentgridIds)
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
        $codrefundshipmentgridIds = $this->getRequest()->getParam('codrefundshipmentgrid');
		
        if(!is_array($codrefundshipmentgridIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($codrefundshipmentgridIds as $codrefundshipmentgridId) {
				
					$payOutstatus = $this->getRequest()->getParam('payout_status');
					$codrefundshipmentId1 = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->load($codrefundshipmentgridId)->getShipmentId();
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
					$queryUpdateStatus = "update shipmentpayout set `shipmentpayout_status` = '".$payOutstatus."' WHERE `shipment_id` = '".$codrefundshipmentId1."'";
					$write->query($queryUpdateStatus);
                    //$codrefundshipmentgrid = Mage::getSingleton('codrefundshipmentgrid/codrefundshipmentgrid')
                      //  ->load($codrefundshipmentgridId)
                        //->setStatus($this->getRequest()->getParam('status'))
                        //->setIsMassupdate(true)
                        //->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($codrefundshipmentId1))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function codrefundshipuploadAction()
		{
			
		$selected_date_val = $this->getRequest()->getParam('selected_date');
		$dateOpen = date('Ymd',strtotime($selected_date_val));
 		$shipmentpayout_report1 = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->getCollection();      	
		$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id')
      			->where('a.udropship_status = "23"');      	
      	//echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		//exit();
		$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "CODshipmentReport"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("Debit Account Number","Value Date","Customer Reference No","Beneficiary Name","Payment Type","Bene Account Number","Bank Code","Account type","Amount","Payment Details 1","Payment Details 2","Payment Details 3","Payment Details 4","Payable Location Code *","Payable Location Name *","Print Location Code *","Print Location Name *","Beneficiary Address 1","Beneficiary Address 2","Beneficiary Address 3","Beneficiary Address 4","Delivery Method","Cheque Number","Bene E-mail ID","Instrument Detail 1","Instrument Detail 2");
    	
		$numfields = sizeof($fieldlist);
		$i = 1;
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
		/*echo "<pre>";
		print_r($shipmentpayout_report1_arr);
		exit();*/
		
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
			$total_amount = $shipmentpayout_report1_val['paymentamount'];
			$shipmentId = $shipmentpayout_report1_val['shipment_id'];
			$accountNo = "'".$shipmentpayout_report1_val['accountno'];
			$beneficiaryName = $shipmentpayout_report1_val['cust_name'];
			$ifsccode = $shipmentpayout_report1_val['ifsccode'];
				for($m =0; $m < sizeof($fieldlist); $m++) {
					$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "Debit Account Number")
		    		{
		    			//$output .= '710607028';
		    			$output .= '712097019';
		    		}
		    		
		    		if($fieldvalue == "Value Date")
		    		{
		    			$output .= $dateOpen;
		    		}
		    		
		    		if($fieldvalue == "Customer Reference No")
		    		{
		    			$output .= $shipmentId;
		    		}
		    		
		    		if($fieldvalue == "Beneficiary Name")
		    		{
		    			$output .= $beneficiaryName;
		    		}
		    			
		    		if($fieldvalue == "Payment Type")
		    		{
		    			$output .= 'EFT';
		    		}
		    			
		    		if($fieldvalue == "Bene Account Number")
		    		{
		    			$output .= "'".$accountNo;
		    		}
		    			
		    		if($fieldvalue == "Bank Code")
		    		{
		    			$output .= $ifsccode;
		    		}
		    			
		    		if($fieldvalue == "Account type")
		    		{
		    			$output .= '2';
		    		}
		    			
		    		if($fieldvalue == "Amount")
		    		{
		    			$output .= str_replace(',','',number_format($total_amount,2));
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $shipmentId;
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',substr(strtoupper($beneficiaryName),0,30)));		    		}
		    		
		    		if($fieldvalue == "Payment Details 3")
		    		{
		    			$output .= "";
		    		}
		    			
		    		if($fieldvalue == "Payment Details 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 3")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Delivery Method")
		    		{
		    				$output .= "";
		    		}
					if($fieldvalue == "Cheque Number")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Bene E-mail ID")
		    		{
		    			$output .= 'customercare@craftsvilla.com';
		    		}
					if($fieldvalue == "Instrument Detail 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Instrument Detail 2")
		    		{
		    			$output .= "";
		    		}
					
					
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    	
				}
		    	$output .= "\n";
				
    		//}
	    }
		
    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    $i++;
	}
}
