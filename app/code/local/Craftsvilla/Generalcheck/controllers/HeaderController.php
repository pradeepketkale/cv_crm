<?php
class Craftsvilla_Generalcheck_HeaderController extends Mage_Core_Controller_Front_Action{
    
	public function getcartAction()
	{
			$baseurl = Mage::getBaseUrl();
			$html_value = '<a rel="nofollow" href="'.$baseurl.'checkout/cart/">';
            $cart = Mage::helper('checkout/cart')->getCart()->getItemsCount();
			$grandValueTotal = Mage::helper('checkout')->formatPrice(Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal());
            $html_value .= '<span class="icon icon-cart"></span><span> {'.$cart.'}&nbsp;'.$grandValueTotal.'</span> </a>';
			echo $html_value;
	}
	
	public function sellerloginviewAction()
		{
			$_session = Mage::getSingleton('udropship/session')->isLoggedIn(); 
			$baseurl = Mage::getBaseUrl();
			if ($_session != true) 
				{
				$html_value = '<p class="fleft"><a rel="nofollow" href="'.$baseurl.'sell" class="inline">Click Here to Sell</a></p>';
				}
			else{
				$html_value ='<div style="padding-top:5px;"> <span><a rel="nofollow" href="'.$baseurl.'marketplace/vendor/">Seller Dashboard</a></span> / <span><a href="'.$baseurl.'marketplace/vendor/logout'.'">'.$this->__('Logout').'</a></span></div>';	
				}
            echo $html_value;	
		}
	
	public function agentloginviewAction()
		{
			$_session = Mage::getSingleton('uagent/session')->isLoggedIn(); 
			$baseurl = Mage::getBaseUrl();
			if ($_session != true) 
				{
				$html_value = '<p class="fleft"><a rel="nofollow" href="'.$baseurl.'agent" class="inline"> | Click Here To Become an Agent</a></p>';
				}
			else{
				$html_value = '<div style="padding-top:5px;"> <span><a rel="nofollow" href="'.$baseurl.'uagent/index/index/">| Agent Dashboard</a></span> / <span><a href="'.$baseurl.'uagent/index/logout'.'">'.$this->__('Logout').'</a></span></div>';
				}
            echo $html_value;	
		}
	public function customerloginregisterviewAction()
		{
			$_session = Mage::getSingleton('customer/session')->isLoggedIn(); 
			$baseurl = Mage::getBaseUrl();
			$logoutUrl = Mage::helper('customer')->getLogoutUrl();
			$dasUrl = Mage::helper('customer')->getDashboardUrl();
			if ($_session != true) 
				{
				$html_value = '<li><a rel="nofollow" class="fancybox" href="#login">Login</a></li>
          <li><a rel="nofollow" class="fancybox em modal_link" href="#signUpForm">Register</a></li>';
				}
			else{
				$html_value = '<li style="width:140px;"><a rel="nofollow" href="'.$dasUrl.'">'.$this->__('My Account').'</a> <span style="margin:0 6px;">/</span> <a rel="nofollow" href="'.$logoutUrl.'">'.$this->__('Logout').'</a></li>';
				}
            echo $html_value;	
		}	
	
public function pageheaderviewAction()
	{
	header('Content-Type: text/html; charset=ISO-8859-15'); 
	$_sessionseller = Mage::getSingleton('udropship/session')->isLoggedIn(); 
	$baseurl = Mage::getBaseUrl();
	$html_value = '';
	 	if($_sessionseller != true) 
	{
	$html_value .= "1;";
	}
		       else
		        {
		       	$html_value .= "0;";
		          }
		         
	$_sessionagent = Mage::getSingleton('uagent/session')->isLoggedIn(); 
	  if($_sessionagent!= true)
	  {
	$html_value  .= "1;";
	  }	
	else
	 {
	   $html_value .= "0;";
	 }

	$_sessioncustomer = Mage::getSingleton('customer/session')->isLoggedIn(); 
	  if($_sessioncustomer != true)
	 {
	$html_value .= "1;";
	 }	
	else
	{
	   $html_value .= "0;";
	}

		       $cart = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	$grandValueTotal = Mage::helper('checkout')->formatPrice(Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal());
		       $html_value .= $cart.';';
		       $html_value .= strip_tags($grandValueTotal);
		       
		      echo $html_value;
	}

}
