<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Renderer_Error extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {
    $value =  $row->getData($this->getColumn()->getIndex());
    return "<a href ='".$value."'>Download Report</a>"; 
  }

  
}
