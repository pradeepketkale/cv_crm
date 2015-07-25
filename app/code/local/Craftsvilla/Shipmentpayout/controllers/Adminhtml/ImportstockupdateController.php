<?php
class Craftsvilla_Shipmentpayout_Adminhtml_ImportstockupdateController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('catalog/shipmentpayout/importstockupdate')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_formstockupdate'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importstockupdateAction()
    {
		
        if ($this->getRequest()->isPost() && !empty($_FILES['import_stockupdate_file']['tmp_name'])) {
            try {
          
                $this->_importStockupdateFile($_FILES['import_stockupdate_file']['tmp_name']);
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
    protected function _importStockupdateFile($fileName)
    {
        /**
         * File handling
         **/
        ini_set('auto_detect_line_endings', true);
        $csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		//echo '<pre>';
		//print_r($csvData);
        /**
         * File expected fields
         */
        $expectedCsvFields  = array(
            0   => $this->__('sku'),
            1   => $this->__('qty'),
            );

       
        /**
         * $k is line number
         * $v is line content array
         */
		foreach ($csvData as $k => $v) 
				{
					/**
					 * End of file has more than one empty lines
					 */
					if (count($v) <= 1 && !strlen($v[0])) 
						{
						continue;
						}
		
					/**
					 * Check that the number of fields is not lower than expected
					 */
					
					if (count($v) < count($expectedCsvFields)) 
						{
						$this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
						continue;
						}
		
					/**
					 * Get fields content
					 */
					$sku = $v[0];
					$qty= $v[1];
			
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
			if($product)
			{
	 		$productId = $product->getId();
	 		$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	 		$stockItemId = $stockItem->getId();
	 		$stockItem['is_in_stock'] = 1;
	 		$stock = array();
	  
					 if (!$stockItemId) {
					 $stockItem->setData('product_id', $product->getId());
					 $stockItem->setData('stock_id', 1);
					 $stockItem['is_in_stock'] = 0;			
					 } else {
					 $stock = $stockItem->getData();
					 }
	  
	  
	 	foreach($stock as $_stock)
	 		{
			$stockItem->setData('qty',$qty);
			}
	 //print_r($stockItem->getData());
	 $stockItem->save();
	 
	 //unset($stockItem);
	 //unset($product);
	 }
//	 echo "<br />Stock updated $sku,$qty";
		 }

   $this->_getSession()->addSuccess($this->__('Inventory Updated Successfully....!!!'));

    }
}
