<?php
class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Renderer_Sku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
     
    public function render(Varien_Object $row)
    {
        $modelData = $row->getData();
        //print_r($modelData); exit;
        $sku = $modelData['product_sku'];  
        
        $skuSplit = str_split($sku,4);  
			$skuLength = count($skuSplit);
			$realSku = '';
			for($i = 0; $i<$skuLength; $i++) {
			$realSku .= $skuSplit[$i].'</br>';
			
			}
			//print_r($realSku); exit;
        
        return $realSku;

    }
}
