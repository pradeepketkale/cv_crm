<?php
class Craftsvilla_SearchResults_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      /*echo "<pre>";
      print_r($_POST);
      exit();*/

     //Below Line added By Dileswar & manoj sir on dated 14-06-2013
      $searchQuery = ucwords($this->getRequest()->getParam('q'));$searchQuery = ucwords($this->getRequest()->getParam('q'));
	  $this->loadLayout();   
	 // $this->getLayout()->getBlock("head")->setTitle($this->__("Search"));
      $this->getLayout()->getBlock("head")->setTitle($this->__("Buy Online ".$searchQuery.' | Craftsvilla.com | Buy Unique Jewellery, Sarees, Salwar, Kurtis and Handicrafts Online'));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Search"),
                "title" => $this->__("Search")
		   ));

      $this->renderLayout(); 
	  
    }
}
