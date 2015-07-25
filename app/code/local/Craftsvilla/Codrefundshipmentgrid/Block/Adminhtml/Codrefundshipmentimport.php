<?php
class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentimport extends Mage_Adminhtml_Block_Widget
{
  public function __construct()
    {
        parent::__construct();
        $this->setTemplate('codrfundimport/import/codrefundimport.phtml');
    }
}
