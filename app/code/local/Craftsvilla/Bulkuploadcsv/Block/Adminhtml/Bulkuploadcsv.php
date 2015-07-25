<?php
class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_bulkuploadcsv';
    $this->_blockGroup = 'bulkuploadcsv';
    $this->_headerText = Mage::helper('bulkuploadcsv')->__('BulkUpload');
    $this->_addButtonLabel = Mage::helper('bulkuploadcsv')->__('Add Csv');
    parent::__construct();
  }
}