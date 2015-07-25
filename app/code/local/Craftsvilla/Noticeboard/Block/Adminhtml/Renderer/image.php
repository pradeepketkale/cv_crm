<?php 
class Craftsvilla_Noticeboard_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		
		$getData = $row->getData();
		$primg= $getData['image'];
		$filepath = Mage::getBaseUrl('media'). 'noticeimages'.'/';
		$filenamepath = $filepath.$primg;
		if($primg)
		{
	    return '<a href="'.$filenamepath.'" target="_blank"><img src="'.$filenamepath.'" width="90" height="50"/></a>';
		}
    }
    
    
}
?>