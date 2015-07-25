<?php 
class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Renderer_Report extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$getData = $row->getData();
		$reportfile=$getData['errorreport'];
		$bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
		$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv');
		$pathreport = Mage::getBaseUrl('media') .'errorcsv'. '/';
		$errorcsvpath = $pathreport.$reportfile;
	    return '<a href="'.$errorcsvpath.'">'.$reportfile.'</a>';
    }
    
    
}
?>