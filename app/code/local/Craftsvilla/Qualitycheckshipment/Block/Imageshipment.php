<?php 
class Craftsvilla_Qualitycheckshipment_Block_Imageshipment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
try{
     $value='<img src="'.Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166).'" width="166" height="166" />';
}catch(Exception $e){
echo 'No Image';
}
}
 
return $value;
}
}

?>
