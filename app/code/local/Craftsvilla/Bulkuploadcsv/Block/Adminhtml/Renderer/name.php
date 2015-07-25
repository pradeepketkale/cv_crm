<?php 
class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Renderer_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		
		$getData = $row->getData();
		$filename = $getData['filename'];
		$bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
		$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv');
		$filepath = Mage::getBaseUrl('media') .'vendorcsv'.'/';
		$filenamepath = $filepath.$filename;
	    return '<a href="'.$filenamepath.'">'.$filename.'</a>';
    }
    
    
}
?>