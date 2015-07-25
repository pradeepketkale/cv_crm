<?php

class Craftsvilla_Agentpayout_Adminhtml_ImportpaytypeController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Constructor
     */
    protected function _construct()
    {        
        $this->setUsedModuleName('Agentpayout');
    }

    /**
     * Main action : show import form
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/agentpayout/importpaytype')
            ->_addContent($this->getLayout()->createBlock('agentpayout/adminhtml_import_formpaytype'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importagentpaytypeAction()
    {
		if ($this->getRequest()->isPost() && !empty($_FILES['import_agentpaytype_file']['tmp_name'])) {
            try {
                $this->_importPayTypeFile($_FILES['import_agentpaytype_file']['tmp_name']);
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Invalid file upload attempt'));
            }
        }
        else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Importation logic
     * @param string $fileName
    
     */
    protected function _importPayTypeFile($fileName)
    {
        /**
         * File handling
         **/
        ini_set('auto_detect_line_endings', true);
        $csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /**
         * File expected fields
         */
        $expectedCsvFields  = array(
            0   => $this->__('Shipment Id'),
            1   => $this->__('Payment Amount'),
			//2   => $this->__('Commission Amount'),
            2   => $this->__('Payment Type'),
            3   => $this->__('Date')
        );
        /**
         * $k is line number
         * $v is line content array
         */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $i = 0;
		foreach ($csvData as $k => $v) {
			if($i < 500)
			{
            /**
             * End of file has more than one empty lines
             */
            if (count($v) <= 1 && !strlen($v[0])) {
                continue;
            }

            /**
             * Check that the number of fields is not lower than expected
             */
            if (count($v) < count($expectedCsvFields)) {
                $this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
                continue;
            }

            /**
             * Get fields content
			 extra column 'Commision' added By Dileswar on dated 03-04-2013
             */
            $shipmentId = $v[0];
            $paymentAmount=$v[1];
			$paymentType=$v[2];
            $dateCsv=$v[3];
			$date=date('Y-m-d ',strtotime($dateCsv));
			$date1=date('d-m-Y ',strtotime($dateCsv));
           
            /*$queryShipmentAgent = "update agentpayout set payment_amount='".$paymentAmount."',comment='".$paymentType."',agentpayout_update_time='".$date."',agentpayout_status='1' WHERE shipment_id = '".$shipmentId."'";  
            $write->query($queryShipmentAgent);*/
			
			
			
			$read = Mage::getSingleton('core/resource')->getConnection('agentpayout_read');
			$querysmsemail = "SELECT agent.`agent_id`,sfs.`increment_id` as shipment_id,agent.`email` as email,agent.`telephone`,agent.`agent_name` as agent_name,`closing_balance` FROM `uagent` as agent,sales_flat_shipment as sfs where sfs.`agent_id` = agent.`agent_id` and sfs.`increment_id` = '".$shipmentId."'";
			$sql = $read->fetchAll($querysmsemail);
			$smsTelephone = $sql[0]['telephone'];
			$smsEmail = $sql[0]['email'];
			$agentName = $sql[0]['agent_name'];
			$agentId = $sql[0]['agent_id'];
			//for adding closing balanace

			$closingbalance = $sql[0]['closing_balance'];
			
			if(($paymentAmount+$closingbalance) >= 0)
				{
					$closingbalance = $closingbalance - $paymentAmount;
					$queryShipmentAgent = "update agentpayout set payment_amount='".$paymentAmount."',comment='".$paymentType."',agentpayout_update_time='".$date."',agentpayout_status='1' WHERE shipment_id = '".$shipmentId."'";  
		            $write->query($queryShipmentAgent);
					
					$queyAgent = "update `uagent` set `closing_balance` = '".$closingbalance."' WHERE `agent_id` = '".$agentId."'";
					$write->query($queyAgent);	
				}
			$storeId = Mage::app()->getStore()->getId();
		 	$templatePayid = 'shipmentpayments_to_agents';
         	$sender = Array('name'  => 'Craftsvilla Finance',
				'email' => 'finance@craftsvilla.com');
		 
		 	$varSms = Array('shipmentId' =>	$shipmentId,
						'paymentAmount' => $paymentAmount,
						'paymentType' => $paymentType,
						'date' => $date1,
						'agentName' => $agentName,
			);
		
		$emailPayout = Mage::getModel('core/email_template')->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId));
		$emailPayout->sendTransactional($templatePayid, $sender, $smsEmail,$agentName,$varSms, $storeId);		
		
	//// Added For SMS To Vendor by Dileswar On Dated (08-02-2013)//////    --------------------//////////////	
		
			$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
			$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
			$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
			$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to Vendor
		$customerMessage = 'Craftsvilla Agent: Deposited in Your Bank Amount Rs.'.$paymentAmount.' For Shipment# '.$shipmentId.', On Date'.$date1.' Via '.$paymentType.'.';
		$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$smsTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);			
     	
	}else
		{ 
		break;
		}
	  $i++;
		}
		$this->_getSession()->addSuccess($this->__($shipmentId.'Payment Amount And Payment Types are successfully saved & SMS & Email sent to respective Agents!!!'));
	}
   }

