<?php

class Craftsvilla_Shipmentpayout_Adminhtml_ImportlogisticController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('report/shipmentpayout/importlogistic')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_importlogistic'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importlogisticAction()
    {
		
		 if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmentlogistic_file']['tmp_name'])) {
            try {
				 //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importPayTypeFile($_FILES['import_shipmentlogistic_file']['tmp_name']);
				
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
            1   => $this->__('Logistic Charge')
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
             $logisticcharge=$v[1];
		//	$shipment = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection()->getData();
			$shipmentlogistic = "update shipmentpayout set intshipingcost='".$logisticcharge."' WHERE shipment_id = '".$shipmentId."'";  
            $write->query($shipmentlogistic);
			
					 
		}
           $this->_getSession()->addSuccess($this->__('Logistic Charge successfully saved!!!'));
        
    }
	
}
