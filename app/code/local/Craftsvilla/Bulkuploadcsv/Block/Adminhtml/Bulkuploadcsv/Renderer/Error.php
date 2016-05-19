<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Renderer_Error extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {
    $value =  $row->getData($this->getColumn()->getIndex());
    if(!empty($value)){
    	return "<a href ='http://mediaserverclean.craftsvilla.com.s3.amazonaws.com/".$value."?t=".time()."'>Download Report</a>"; 	
    }
    else{
    	return "No Report";
    }
    
  }

  
}
