<?php
class Marketplace_Thoughtyard_RecentsalesController extends Mage_Core_Controller_Front_Action
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
    public function getmostrecentAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->loadLayout()->renderLayout();
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
    public function getmostsoldAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->loadLayout()->renderLayout();
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}