<?php
class Mage_avenues_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('avenues/standard/form.phtml');
        parent::_construct();
    }
}