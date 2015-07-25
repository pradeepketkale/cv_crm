<?php 

class Gharpay_Block_Standard_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		$this->setTemplate('gharpay/standard/form.phtml');
		parent::_construct();
	}
}