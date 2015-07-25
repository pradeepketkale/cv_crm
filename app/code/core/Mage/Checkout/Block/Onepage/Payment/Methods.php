<?php
class Mage_Checkout_Block_Onepage_Payment_Methods extends Mage_Payment_Block_Form_Container
{
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Check and prepare payment method model
     *
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        if (!$method || !$method->canUseCheckout()) {
            return false;
        }
        return parent::_canUseMethod($method);
    }

    /**
     * Retrieve code of current payment method
     *
     * @return mixed
     */
    public function getSelectedMethodCode()
    {
        if ($method = $this->getQuote()->getPayment()->getMethod()) {
            return $method;
        }
        return false;
    }

    /**
     * Payment method form html getter
     * @param Mage_Payment_Model_Method_Abstract $method
     */
    public function getPaymentMethodFormHtml(Mage_Payment_Model_Method_Abstract $method)
    {
         return $this->getChildHtml('payment.method.' . $method->getCode());
    }

    /**
     * Return method title for payment selection page
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     */
    public function getMethodTitle(Mage_Payment_Model_Method_Abstract $method)
    {
        $form = $this->getChild('payment.method.' . $method->getCode());
        if ($form && $form->hasMethodTitle()) {
            return $form->getMethodTitle();
        }
        return $method->getTitle();
    }

    /**
     * Payment method additional label part getter
     * @param Mage_Payment_Model_Method_Abstract $method
     */
    public function getMethodLabelAfterHtml(Mage_Payment_Model_Method_Abstract $method)
    {
        if ($form = $this->getChild('payment.method.' . $method->getCode())) {
            return $form->getMethodLabelAfterHtml();
        }
    }

	public function codpincodecheck($pincode)
	{
	//$pincode = $_POST['pincode'];
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery'); 
		if($delhivery=='1')
		{
			
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Delhivery%'";
		
		}
		else
		{
			
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Aramex%'";
		
		}
		$rquery = $read->query($pincodeQuery)->fetch();
		  $cod = $rquery['is_cod'];
		  
		if($cod=='0')
		  {
			return true;
		  }
		   else
		  {
			return false;
		  }
						      
		   return false;
							
	}
}
