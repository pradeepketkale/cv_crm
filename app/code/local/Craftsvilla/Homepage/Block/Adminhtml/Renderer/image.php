<?php 
class Craftsvilla_Homepage_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		
		$getData = $row->getData();
		$prid= $getData['product_id'];
        $product = Mage::getModel('catalog/product')->load($prid);
		
		$childId = $getData['product_id'];
		$purl = 'catalog/product/view/id/'.$childId;
	     //return Mage::getBaseUrl().$purl;exit;
        //return 
		return '<a href="'.Mage::getBaseUrl().$purl.'" target="_blank"><img src="'.Mage::helper('catalog/image')->init($product, 'image')->resize(75, 75).'" alt=""  border="0"/></a>';
		
    }
    
    
}
?>