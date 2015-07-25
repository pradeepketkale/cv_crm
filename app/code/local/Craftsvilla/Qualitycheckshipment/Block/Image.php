<?php 
class Craftsvilla_Qualitycheckshipment_Block_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{   
 
public function render(Varien_Object $row)
{
//echo '<pre>';
//print_r($row);
$productId =  $row->getProductId();
$product = Mage::getModel('catalog/product')->load($productId);
$value = '<img src="">';
if($product->getImage()!= '')
{
     $value='<img src="'.Mage::helper('catalog/image')->init($product, 'image')->resize(150, 150).'" width="150" height="150" />';
}
 
return $value;
}
}

?>
