<?php
/**
 * Shipment Tracking module
 * Author : Saurabh Sharma*/
class Craftsvilla_Shipmentpayout_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('sales/shipmentpayout/import')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_form'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmenttracking_file']['tmp_name'])) {
            try {
                //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importShipmenttrackingFile($_FILES['import_shipmenttracking_file']['tmp_name']);
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
     * @param string $trackingTitle
     */
    protected function _importShipmenttrackingFile($fileName)
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
            0   => $this->__('Order Id'),
            1   => $this->__('Title'),
            2   =>$this->__('Courier Name'),
            3   => $this->__('Tracking Number')
        );

       
        /**
         * $k is line number
         * $v is line content array
         */
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
            $orderId = $v[0];
            $trackingTitle=$v[1];
            $courierCode=$v[2];
            $trackingNumber = $v[3];
            
           $shipment=Mage::getModel('sales/order_shipment')->loadByIncrementId($orderId);
           if($shipment ==0){
               $this->_getSession()->addError($this->__('You have entered wrong shipment Id %s. So can not create tracking number', $orderId));
                return 0;
           }
           $track = Mage::getModel('sales/order_shipment_track')
                        ->setNumber($trackingNumber)
                        ->setCourierName($courierCode)
                        ->setTitle($trackingTitle);
            $shipment->addTrack($track);
			if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
				
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
				}
				else
				{
					$this->_getSession()->addSuccess($this->__('%s Shipment Status already shipped but tracking number updated!!!',$orderId));
				}

        
        /**
         * Comment handling
         */
            $shipment->addComment('Shipped');

            $shipment->save();  
		 $shipment->sendEmail();
				$shipment->setEmailSent(true);
            
        }
        $this->_getSession()->addSuccess($this->__('Shipments are successfully saved!!!'));

    }
}
