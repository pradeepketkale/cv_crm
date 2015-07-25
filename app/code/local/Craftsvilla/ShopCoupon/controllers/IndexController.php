<?php


class Craftsvilla_ShopCoupon_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Discount Coupons Of Craftsvilla Sellers"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("shopcoupon", array(
                "label" => $this->__("ShopCoupon"),
                "title" => $this->__("ShopCoupon")
		   ));

      $this->renderLayout(); 
	  
    }
	
	}
