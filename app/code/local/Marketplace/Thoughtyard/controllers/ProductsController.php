<?php
class Marketplace_Thoughtyard_ProductsController extends Mage_Core_Controller_Front_Action
{

  public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->loadLayout()->renderLayout();
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}
