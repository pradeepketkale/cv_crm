<?php

class Craftsvilla_Shipmentpayout_Adminhtml_ImportcodutrtrackingController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('sales/shipmentpayout/importcodutrtracking')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_importcodutrtracking'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importcodutrtrackingAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmentutr_file']['tmp_name'])) {
            try {
                //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importUtrFile($_FILES['import_shipmentutr_file']['tmp_name']);
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
    protected function _importUtrFile($fileName)
    {
        /**
         * File handling
         **/
        ini_set('auto_detect_line_endings', true);
        $csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
        //var_dump($csvData);exit;
        /**
         * File expected fields
         */
        $expectedCsvFields  = array(
            0   => $this->__('Shipment Id'),
            1   => $this->__('UTR Number'),
            2   => $this->__('Date')
        );

       
        /**
         * $k is line number
         * $v is line content array
         */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
	    $read  = Mage::getSingleton('core/resource')->getConnection('core_read');

        $filename = "shipementcodupload_".date("Ymd");
        $filePathOfCsv = Mage::getBaseDir('media').DS.'misreport'.DS.$filename.'.txt';
        $fp=fopen($filePathOfCsv,'a');
        $strHead = "Tracking Id,Shipment Id\n";
        fputs($fp, $strHead);
        fclose($fp);

        foreach ($csvData as $k => $v) {

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
             */
            $trackingId = $v[0];
            $utrNumber=$v[1];
            $dateCsv=$v[2];
	    $logistic=$v[3];
	// echo "<pre>"; print_r($trackingId); exit;
            //SHIPMENT_STATUS_SHIPPED    = 1
            //SHIPMENT_STATUS_SHIPPED_CRAFTSVILLA = 15;
            //SHIPMENT_STATUS_RECEIVED_CRAFTSVILLA = 17;

           $trackingIdQuery= $read->query("select  parent_id from sales_flat_shipment_track where number = '".$trackingId."' ");
	   $trackingIdFetch = $trackingIdQuery->fetch();
	   $trackingIdResult = $trackingIdFetch['parent_id'];
	   
	  
	   $shipmentRetrieve = $read->query("select  increment_id from sales_flat_shipment where entity_id = '".$trackingIdResult."' ");
	   $shipmentFetch = $shipmentRetrieve->fetch();
	   $shipmentResult = $shipmentFetch['increment_id'];
	    //echo "<pre>"; print_r($shipmentResult); exit;
           $date=date('Y-m-d H:i:s',strtotime($dateCsv));
          //  echo "<pre>"; print_r($shipmentResult); exit;
           //$shipmentId=Mage::getModel('shipmentpayout/shipmentpayout')->loadByOrderIncrementId($orderId)->getShipmentId();
          
           //$shipmentData=Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
           
           //$shipmentStatus= $shipmentData->getUdropshipStatus();
           /*Test for Debugging Shipment Id's */
           
           $fp=fopen($filePathOfCsv,'a');
           fputs($fp, $trackingId.",".$shipmentResult."\n");
           fclose($fp);

           if($shipmentResult){
                $queryShipmentUtr = "update shipmentpayout set citibank_utr='".$utrNumber."',shipmentpayout_update_time='".$date."'  , intshipingcost='".$logistic."' WHERE shipment_id = '".$shipmentResult."'";		  
                $write->query($queryShipmentUtr);
		// echo "<pre>"; print_r($write->query($queryShipmentUtr)); exit;	
           }
           elseif($shipmentResult == ''){
		   		continue;
		   }
		   else{
               $this->_getSession()->addError($this->__('Can Not Save UTR Number For Shipment Number - '.$shipmentResult));
           }
        }
           $this->_getSession()->addSuccess($this->__('Shipments UTR Numbers are successfully saved!!!'));

        
    }
}
