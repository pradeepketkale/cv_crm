<?php

class Craftsvilla_Agentpayout_Adminhtml_AgentpayoutController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('agentpayout/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Agentpayout Details'), Mage::helper('adminhtml')->__('Agentpayout Details'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('agentpayout/agentpayout')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('agentpayout_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('agentpayout/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Agentpayout Details'), Mage::helper('adminhtml')->__('Agentpayout Details'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Agentpayout'), Mage::helper('adminhtml')->__('Agentpayout '));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('agentpayout/adminhtml_agentpayout_edit'))
				->_addLeft($this->getLayout()->createBlock('agentpayout/adminhtml_agentpayout_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agentpayout')->__('Payout details does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('agentpayout/agentpayout');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getAgentpayoutCreatedTime == NULL || $model->getAgentpayoutUpdateTime() == NULL) {
					$model->setAgentpayoutCreatedTime(now())
						->setAgentpayoutUpdateTime(now());
				} else {
					$model->setAgentpayoutUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agentpayout')->__('Payout was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agentpayout')->__('Unable to find payout to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('agentpayout/agentpayout');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Payout was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $agentpayoutIds = $this->getRequest()->getParam('agentpayout');
        if(!is_array($agentpayoutIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select payoutid(s)'));
        } else {
            try {
                foreach ($agentpayoutIds as $agentpayoutId) {
                    $agentpayout = Mage::getModel('agentpayout/agentpayout')->load($agentpayoutId);
                    $agentpayout->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($agentpayoutIds)
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
        $agentpayoutIds = $this->getRequest()->getParam('agentpayout');
        if(!is_array($agentpayoutIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Payouts(s)'));
        } else {
            try {
                foreach ($agentpayoutIds as $agentpayoutId) {
                    $agentpayout = Mage::getSingleton('agentpayout/agentpayout')
                        ->load($agentpayoutId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($agentpayoutIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

	public function agentreportAction()
		{
			
		$selected_date_val = $this->getRequest()->getParam('selected_date');
		$dateOpen = date('Ymd',strtotime($selected_date_val));
 
    	$agentpayout = Mage::getModel('agentpayout/agentpayout')->getCollection();
      	$agentpayout->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount','agent_id','order_id'))
      			->joinLeft('sales_flat_order_payment', 'a.order_id = sales_flat_order_payment.parent_id','method')
				->where('(main_table.agentpayout_status=0 AND a.udropship_status = 1 AND `sales_flat_order_payment`.method IN ("secureebs_standard","purchaseorder","payucheckout_shared","paypal_standard","free")) OR (main_table.agentpayout_status=0 AND a.udropship_status = 7 AND `sales_flat_order_payment`.method = "cashondelivery")')// a.udropship_status IN (1,15,17)
				->where('a.updated_at <= DATE_SUB(NOW(),INTERVAL 15 DAY)');// a.udropship_status IN (1,15,17)
      	
      	//echo "Query:".$agentpayout->getSelect()->__toString();
		//exit();
      			
      	$agentpayout_arr = $agentpayout->getData();
    	$filename = "AgentReport"."_".$selected_date_val;
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
		
    	foreach($agentpayout_arr as $agentpayout_val)
	    {
			$agents = Mage::helper('uagent')->getAgent($agentpayout_val['agent_id']);
			if($agentpayout_val['agent_id'] != '')
			{
				$agentId = $agentpayout_val['agent_id'];
				
				//$agent_amount = (($total_amount+$itemised_total_shippingcost-$baseDiscountAmount)*($commission_percentage-$discountAgentCoupon)/100);
				$agent_amount = $agentpayout_val['payment_amount'];		
			
					
				for($m =0; $m < sizeof($fieldlist); $m++) {
					$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "Debit Account Number")
		    		{
		    			$output .= '710607028';
		    		}
		    		
		    		if($fieldvalue == "Value Date")
		    		{
		    			$output .= $dateOpen;
		    		}
		    		
		    		if($fieldvalue == "Customer Reference No")
		    		{
		    			$output .= $agentpayout_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Beneficiary Name")
		    		{
		    			$output .= $agents->getCheckPayTo();
		    		}
		    			
		    		if($fieldvalue == "Payment Type")
		    		{
		    			$output .= 'NEFT';
		    		}
		    			
		    		if($fieldvalue == "Bene Account Number")
		    		{
		    			$output .= "'".$agents->getBankAccountNumber();
		    		}
		    			
		    		if($fieldvalue == "Bank Code")
		    		{
		    			$output .= $agents->getBankIfscCode();
		    		}
		    			
		    		if($fieldvalue == "Account type")
		    		{
		    			$output .= '2';
		    		}
		    			
		    		if($fieldvalue == "Amount")
		    		{
		    			$output .= str_replace(',','',number_format($agent_amount,2));
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $agentpayout_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',substr(strtoupper($agents->getAgentName()),0,30)));		    		}
		    		
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
		    			$output .= $agents->getEmail();
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
		    		/*else {
		    			$output .= "\n";
			    		$output .= '710607788';
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['shipment_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_created_at'];
			    		$output .= ",";
			    		$output .= 'NEFTKRIBHA001';
			    		$output .= ",";
			    		$output .= 'Kribha Handicrafts Pvt Ltd';
			    		$output .= ",";
			    		$output .= 'Neft';
			    		$output .= ",";
			    		$output .= '0710607028';
			    		$output .= ",";
			    		$output .= 'CITI0100000';
			    		$output .= ",";
			    		$output .= $gen_random_number;
			    		$output .= ",";
			    		$output .= $kribha_amount;
			    		$output .= ",";
			    		$output .= "CORRESPONDING COMMISSION";
			    		$output .= ",";
			    		$output .= $utr;
			    		$output .= ",";
		    		}*/
		    	
				}
		    	$output .= "\n";
				
    			
			}
	    }
		
    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    $i++;
	
		}
}