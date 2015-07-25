<?php
class Craftsvilla_Ebslink_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function postAction()
		{
		echo 'Ebslink updated';
		}
}