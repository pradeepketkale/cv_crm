<?php

class Craftsvilla_Codrefundshipmentgrid_Adminhtml_CodrefundshipmentimportController extends Mage_Adminhtml_Controller_Action
{
     
    /**
     * Constructor
     */
    protected function _construct()
    {        
        $this->setUsedModuleName('codrefundshipmentgrid');
    }

    /**
     * Main action : show import form
     */
    public function indexAction()
    {
        $this->loadLayout()
           // ->_setActiveMenu('sales/codrefundshipmentgrid/codrefundimport')
            ->_addContent($this->getLayout()->createBlock('codrefundshipmentgrid/adminhtml_codrefundshipmentimport'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function codrefundimportAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_codrefundshipment_file']['tmp_name'])) {
            try {
                //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importcodrefundFile($_FILES['import_codrefundshipment_file']['tmp_name']);
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
    protected function _importcodrefundFile($fileName)
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
            0   => $this->__('Shipment Id')
           );

       
        /**
         * $k is line number
         * $v is line content array
         */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
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
           
            
            //SHIPMENT_STATUS_SHIPPED    = 1
            //SHIPMENT_STATUS_SHIPPED_CRAFTSVILLA = 15;
            //SHIPMENT_STATUS_RECEIVED_CRAFTSVILLA = 17;
            
          
           
           //$shipmentId=Mage::getModel('shipmentpayout/shipmentpayout')->loadByOrderIncrementId($orderId)->getShipmentId();
          
           //$shipmentData=Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
           
           //$shipmentStatus= $shipmentData->getUdropshipStatus();
           if($shipmentId){
               $queryShipmentcod = "select `order_id`, `udropship_status` from `sales_flat_shipment` where `increment_id` = '".$shipmentId."'";
				$result = $read->query($queryShipmentcod)->fetch();
				$orderid = $result['order_id'];
				$udropshipstatus = $result['udropship_status'];
				$method = "select `method` from `sales_flat_order_payment` where `parent_id` = '".$orderid."'";
				$methodres = $read->query($method)->fetch();
				$codmethod = $methodres['method'];
				$codrefundshipment = "update sales_flat_shipment set udropship_status= 12 WHERE `increment_id` = '".$shipmentId."'";  
               			$write->query($codrefundshipment);
		               	$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
               			Mage::helper('udropship')->addShipmentComment($shipment,$this->__('Refunded by System'));
		               	$shipment->save();
				if($codmethod=='cashondelivery')
				{
				   //if($udropshipstatus==23)
				   //{
				    //$codrefundshipment = "update sales_flat_shipment set udropship_status= 12 WHERE `increment_id` = '".$shipmentId."'";  
               			//$write->query($codrefundshipment);
               		 	//$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
               		        //Mage::helper('udropship')->addShipmentComment($shipment,$this->__('Refunded by System'));
               		 	//$shipment->save();
               		 	//}
				}
				else
				{
				   $this->_getSession()->addError($this->__($shipmentId.' is not a COD shipment'));
				}
           }
           elseif($shipmentId == ''){
		   		continue;
		   }
		   else{
               $this->_getSession()->addError($this->__($shipmentId.' is not a COD shipment'));
           }
        }
           $this->_getSession()->addSuccess($this->__('COD Refund Shipments are imported successfully saved!!!'));

        
    }
}
