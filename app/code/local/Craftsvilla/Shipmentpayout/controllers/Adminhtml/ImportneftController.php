<?php

class Craftsvilla_Shipmentpayout_Adminhtml_ImportneftController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Constructor
     */
    protected function _construct()
    {        
        $this->setUsedModuleName('Shipmentpayout');
    }

    /**
     * Main action : show import form
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/shipmentpayout/importneft')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_importneft'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importneftAction()
    {
		 if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmentneft_file']['tmp_name'])) {
            try {
				 //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
               
               $updatedNEFTdetails = $this->_importNeftFile($_FILES['import_shipmentneft_file']['tmp_name']);
				//print_r($updatedNEFTdetails); exit;
               $this->_getSession()->addSuccess($this->__('NEFT successfully saved!!!'));
                //$fname = 'merchantId.csv'; 
				//$this->_importNeftFile($fname);
				$mailarray = 'places@craftsvilla.com';
				$mailBody1 = $updatedNEFTdetails[0]; 
				$mail = Mage::getModel('core/email');
					$mail->setToName('Craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($updatedNEFTdetails[1]);
					$mail->setSubject($mailBody1);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName('Craftsvilla Places');
					$mail->setType('html');
					if($mail->send())
					{
					echo 'Email has been send successfully';
					}
					else 
					echo "error";
				
            }
            catch (Mage_Core_Exception $e) {
		        $this->_getSession()->addError($e->getMessage()); 
            }
            catch (Exception $e) {
		        $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Exception Occured: Invalid file upload attempt'));
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
    protected function _importNeftFile($fileName)
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
            0   => $this->__('Vendor Id'),
            1   => $this->__('Merchant Id City')
			);
		
        /**
         * $k is line number
         * $v is line content array
         */
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $emailBodyUpdated = '<table border="1">';
        $emailBodyUpdated .= '<tr><th>VENDOR ID</th><th>VENDOR NAME</th><th>MERCHANT ID</th></tr>';
        $emailBodyNotUpdated = '<table border="1">';
        $emailBodyNotUpdated .= '<tr><th>VENDOR ID</th><th>VENDOR NAME</th><th>MERCHANT ID</th></tr>';
        $emailBodyNotValid = '<table border="1">';
        $emailBodyNotValid .= '<tr><th>VENDOR ID</th><th>VENDOR NAME</th><th>MERCHANT ID</th></tr>';
        $counter = 0;
        $countUpdatedNEFT = 0;
		$countNotUpdatedNEFT = 0;
		$countnotValidNEFT = 0;
//print_r($csvData); exit;
        foreach ($csvData as $k => $v) 
        {

            /**
             * End of file has more than one empty lines
             */
            if($counter < 500 )
   			{
            
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
			 */
            $vendorId = $v[0];
            $merchantIdCity = mysql_escape_string($v[1]); 
            $vidLength = strlen($vendorId);  
            $merchentVendorIDcheck  = substr($merchantIdCity,-$vidLength);
            
            //To check valid MerchantID
            if($merchentVendorIDcheck == $vendorId) 
            {
            
		        $duplicateMerchantQuery = "SELECT * FROM vendor_info_craftsvilla WHERE merchant_id_city = '".$merchantIdCity."'";
				$duplicateMerchantQueryResult = $read->query($duplicateMerchantQuery)->fetchAll();
				
				//To check duplicate MerchantID
				if(!$duplicateMerchantQueryResult) {
				$countUpdatedNEFT++;
				
				$vendorNameQuery = "SELECT vendor_name FROM udropship_vendor WHERE vendor_id = '".$vendorId."'";
				$vendorNameQueryResult = $read->query($vendorNameQuery)->fetch();
				$vendorName =  mysql_escape_string($vendorNameQueryResult['vendor_name']); 
				$emailBodyUpdated .= '<tr><td>'.$vendorId.'</td><td>'.$vendorName.'</td><td>'.$merchantIdCity.'</td></tr>'; 
				
				
				$vendorIdQuery = "SELECT vendor_id FROM vendor_info_craftsvilla WHERE vendor_id = '".$vendorId."'";
				$vendorIdQueryResult = $read->query($vendorIdQuery)->fetch();
				$vendorIdRes =  mysql_escape_string($vendorIdQueryResult['vendor_id']); 
				
				if($vendorIdRes)
				{
				
					$updateVendorNeftQuery = "UPDATE `vendor_info_craftsvilla` SET merchant_id_city = '".$merchantIdCity."' WHERE vendor_id = '".$vendorId."'"; 
					$write->query($updateVendorNeftQuery);
				}
				else
				{
					$insertVendorNeftQuery = "INSERT INTO `vendor_info_craftsvilla`(`vendor_id`,  `international_order`, `merchant_id_city`) VALUES ($vendorId,1,'$merchantIdCity')"; 
					
					$write->query($insertVendorNeftQuery);
				}
				
				
				} else {
				$countNotUpdatedNEFT++;
				$vendorNameQuery = "SELECT vendor_name FROM udropship_vendor WHERE vendor_id = '".$vendorId."'";
				$vendorNameQueryResult = $read->query($vendorNameQuery)->fetch();
				$vendorName = mysql_escape_string($vendorNameQueryResult['vendor_name']); 
				$emailBodyNotUpdated .= '<tr><td>'.$vendorId.'</td><td>'.$vendorName.'</td><td>'.$merchantIdCity.'</td></tr>';
			
				}
		        
		    } else {
		        $countnotValidNEFT++;
		        $vnameQuery = "SELECT * FROM udropship_vendor WHERE vendor_id = '".$vendorId."'";
		        $vnameResult = $read->query($vnameQuery)->fetch();
		        $vname = mysql_escape_string($vnameResult['vendor_name']);
		        $emailBodyNotValid .= '<tr><td>'.$vendorId.'</td><td>'.$vname.'</td><td>'.$merchantIdCity.'</td></tr>';
		        }
		        
			
			}
			$counter++;		 
		}
		$emailBodyUpdated .= '</table>';
		$emailBodyNotUpdated .= '</table>';
		$emailBodyNotValid .= '</table>';
		$read->closeConnection();
		$write->closeConnection();
		
		$arrayMail = array();
		$totalCount = 'Total Updated NEFT:'.$countUpdatedNEFT.'  Total not updated NEFT:'.$countNotUpdatedNEFT.'  not valid NEFT:'.$countnotValidNEFT; 

		echo $messageBody = 'Updated NEFT vendors:'."<span style='display:inline-block;height:1em;width:2em;'>&nbsp;</span>".$emailBodyUpdated.'<br>'.'Not Updated NEFT vendors(Duplicate MerchantID):'.'&nbsp;&nbsp;'.$emailBodyNotUpdated.'<br>'.'Not Valid NEFT vendors(VendorID mismatch):'."<span style='display:inline-block;height:1em;width:2em;'>&nbsp;</span>".$emailBodyNotValid.'<br>'; 
		$arrayMail = array($totalCount,$messageBody);
//print_r($arrayMail); exit;
		return $arrayMail;
    }
	
}
