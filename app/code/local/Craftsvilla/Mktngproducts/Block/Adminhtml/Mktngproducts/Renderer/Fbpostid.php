<?php
class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Renderer_Fbpostid extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
     
   
    public function render(Varien_Object $row)
    {
        $modelData = $row->getData();
        //print_r($modelData); exit;
        $sku = $modelData['product_sku']; 
        $postid = $modelData['fb_post_id'];  
        $postId = str_split($postid,4);  
			$postidlen = count($postId);
			$realPostid = '';
			for($i = 0; $i<$postidlen; $i++) {
			$realPostid .= $postId[$i].'</br>';
			
			}
			//print_r($realPostid); exit;
        
        
        $postid1 = 'https://www.facebook.com/craftsvilla/posts/'.$postid;
        
        $html = '';
       $html .= '<a href="'.$postid1.'" target="_blank">'.$realPostid.'</a>';
        return $html;

    }
}
