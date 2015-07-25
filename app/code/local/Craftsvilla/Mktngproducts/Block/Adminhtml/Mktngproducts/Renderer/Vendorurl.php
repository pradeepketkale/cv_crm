<?php
class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Renderer_Vendorurl extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
     
    public function render(Varien_Object $row)
    {
        $modelData = $row->getData();
        //print_r($modelData); exit;
        $sku = $modelData['product_sku']; 
        $connread = Mage::getSingleton('core/resource')->getConnection('core_read');
        $productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
		$productQueryRes = $connread->query($productQuery)->fetch();
		$connread->closeConnection();
		$productId = $productQueryRes['entity_id']; 
		$product = Mage::helper('catalog/product')->loadnew($productId);
		//echo '<pre>'; print_r($product); exit;
		$productUrl = $product->getProductUrl();
			$vendorId = $product->getUdropshipVendor(); 
			$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
			//echo '<pre>';print_r($vendorInfo); exit;
			$vendorName = $vendorInfo->getVendorName(); 
			$vendorSplit = str_split($vendorName,5);  
			$vendorLength = count($vendorSplit);
			$realVendor = '';
			for($i = 0; $i<$vendorLength; $i++) {
			$realVendor .= $vendorSplit[$i].'</br>';
			
			}
			//print_r($realVendor); exit;
			
			$vlink = $vendorInfo->getUrlKey();  
			//$vendorUrl = 'http://local.craftsvilla.com/'.$vlink; 
			$vendorUrl = Mage::getBaseUrl().$vlink; 
        
        $html = '';
        $html .= '<a href="'.$vendorUrl.'" target="_blank">'.$realVendor.'</a>';
        return $html;

    }
}
