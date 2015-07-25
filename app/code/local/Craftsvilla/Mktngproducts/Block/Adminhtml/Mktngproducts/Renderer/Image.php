<?php
class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
        //print_r($row); exit;
        $modelData = $row->getData();
        $sku = $modelData['product_sku']; 
        $connread = Mage::getSingleton('core/resource')->getConnection('core_read');
        $productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
		$productQueryRes = $connread->query($productQuery)->fetch();
		$connread->closeConnection();
		$productId = $productQueryRes['entity_id']; 
		$product = Mage::helper('catalog/product')->loadnew($productId);
		//echo '<pre>'; print_r($product); exit;
		$productUrl = $product->getProductUrl();
		try 
		{
			$image="<img src='".Mage::helper('catalog/image')->init($product,'image')->resize(112, 112)."' alt='' width='90' 					border='0'style='float:left; border:2px solid #ccc; margin:5px 20px 20px;' />"; 
		}
		catch(Exception $e)
		{ 
			$image="No Image";
		}
        
        $html = '';
        $html .= '<a href="'.$productUrl.'" target="_blank">'.$image.'</a>';
        //$html .=  $this->getColumn()->getInlineCss();
        //$html .= '<br/><p>'.$row->getData($this->getColumn()->getIndex()).'</p>';
        return $html;

    }
}
