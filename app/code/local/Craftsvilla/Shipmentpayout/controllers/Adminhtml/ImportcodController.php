<?php

class Craftsvilla_Shipmentpayout_Adminhtml_ImportcodController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('sales/shipmentpayout/importcod')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_importcod'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importcodAction()
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
            $shipmentId = $v[0];
            $utrNumber=$v[1];
            $dateCsv=$v[2];
            
            //SHIPMENT_STATUS_SHIPPED    = 1
            //SHIPMENT_STATUS_SHIPPED_CRAFTSVILLA = 15;
            //SHIPMENT_STATUS_RECEIVED_CRAFTSVILLA = 17;
            
           $date=date('Y-m-d H:i:s',strtotime($dateCsv));
           
           //$shipmentId=Mage::getModel('shipmentpayout/shipmentpayout')->loadByOrderIncrementId($orderId)->getShipmentId();
          
           //$shipmentData=Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
           
           //$shipmentStatus= $shipmentData->getUdropshipStatus();
           if($shipmentId){
                $queryShipmentUtr = "update shipmentpayout set citibank_utr='".$utrNumber."',shipmentpayout_update_time='".$date."' WHERE shipment_id = '".$shipmentId."'";
				  
                $write->query($queryShipmentUtr);
           }
           elseif($shipmentId == ''){
		   		continue;
		   }
		   else{
               $this->_getSession()->addError($this->__('Can Not Save UTR Number For Shipment Number - '.$shipmentId));
           }
        }
           $this->_getSession()->addSuccess($this->__('Shipments UTR Numbers are successfully saved!!!'));

        
    }
}
