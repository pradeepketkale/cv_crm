<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Iksula_Ccavenue
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Redirect to Ccavenue
 *
 * @category    Mage
 * @package     Iksula_Ccavenue
 * @name        Iksula_Ccavenue_Block_Standard_Redirect
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Iksula_Ccavenue_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
		
        $standard = Mage::getModel('ccavenue/standard');
        $form = new Varien_Data_Form();
        
		$form->setAction($standard->getCcavenueUrl())
            ->setId('ccavenue_standard_checkout')
            ->setName('ccavenue_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
		
	    foreach ($standard->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) {
		
		if($field == 'return')
        	{
        		$returnurl=$value."?DR={DR}";
        	}

		
		if($field == 'product_price')
			{
				$amount=$value;
			
			}
			
		if($field == 'cs1')
			{
				$referenceno=$value;
			}
			
		if($field == 'f_name')
			{
				$fname=$value;
			}
			
		if($field == 's_name')
			{
				$lname=$value;
			}
			
	     if($field == 'product_name')
			{
			$desc=$value;
			}		 	
			
        if($field == 'zip')
			{
			$postalcode=$value;
			}
			
        if($field == 'street')
			{
			$street=$value;
			}
			
        if($field == 'street')
			{
			$street=$value;
			}
        if($field == 'city')
			{
			$city=$value;
			}	
        if($field == 'state')
			{
			$state=$value;
			}	
			
			
		if($field == 'Order_Id')
			{
			$order_id=$value;
			}	
		if($field == 'Redirect_Url')
			{
			$red_url=$value;
			}	
		if($field == 'Merchant_Id')
			{
			$merchant_id=$value;
			}			
			
		   $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
		   
        }

		$name=$fname." ".$lname;
		$address=$street.",".$city.",".$state;		
		$mode=Mage::getSingleton('ccavenue/config')->getTransactionMode();
		
		/*Start..
		*Craftsvilla Comment
		*Commented by suresh on 23-05-2012
		*For get base grand total
		*/
		//$amount = round($this->getOrder()->getgrand_total(), 2);
		$amount = round($this->getOrder()->getbase_grand_total(), 2);
		/*End..
		*Craftsvilla Comment
		*Commented by suresh on 23-05-2012
		*For get base grand total
		*/
		
		if($mode == '1')
		{
		$mode="TEST";
		}
		else
		{
		$mode="LIVE";
	    }
		
		//$form->addField('description', 'hidden', array('name'=>'description', 'value'=>$desc));
		//$form->addField('mode', 'hidden', array('name'=>'mode', 'value'=>$mode));
        //$form->addField('Redirect_Url', 'hidden', array('name'=>'return_url', 'value'=>$returnurl));
        //$form->addField('Order_Id', 'hidden', array('name'=>'reference_no', 'value'=>$referenceno));
		

		$acc_id = $merchant_id;
		$ord_id = $order_id;
		$red_url = $red_url;
		
		
			
		$Checksum = $this->getchecksum($acc_id,$amount,$ord_id ,$red_url,Mage::getStoreConfig('payment/ccavenue_standard/secret_key'));
	
        $form->addField('Amount', 'hidden', array('name'=>'Amount', 'value'=>$amount));
        //$form->addField('billing_cust_name', 'hidden', array('name'=>'billing_cust_name', 'value'=>$name));
        //$form->addField('billing_cust_address', 'hidden', array('name'=>'address', 'value'=>$address));
		//$form->addField('billing_cust_state', 'hidden', array('name'=>'address', 'value'=>$state));
        //$form->addField('billing_cust_notes', 'hidden', array('name'=>'Redirect_Url', 'value'=>Mage::getUrl('checkout/onepage/success')));
		$form->addField('billing_zip', 'hidden', array('name'=>'billing_zip', 'value'=>$postalcode));
		$form->addField('Checksum', 'hidden', array('name'=>'Checksum', 'value'=>$Checksum));
       
        $html = '<html><body>';
		
        $html.= $this->__('You will be redirected to CCAvenue in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("ccavenue_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
	
	
	
	public function getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
	{
		
		$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		return $adler;
	}
	
	public function verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
	{
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		
		if($adler == $CheckSum)
			return "true" ;
		else
			return "false" ;
	}
	
	public function adler32($adler , $str)
	{
		$BASE =  65521 ;
	
		$s1 = $adler & 0xffff ;
		$s2 = ($adler >> 16) & 0xffff;
		for($i = 0 ; $i < strlen($str) ; $i++)
		{
			$s1 = ($s1 + Ord($str[$i])) % $BASE ;
			$s2 = ($s2 + $s1) % $BASE ;
				//echo "s1 : $s1 <BR> s2 : $s2 <BR>";
	
		}
		return $this->leftshift($s2 , 16) + $s1;
	}
	
	public function leftshift($str , $num)
	{
	
		$str = DecBin($str);
	
		for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
			$str = "0".$str ;
	
		for($i = 0 ; $i < $num ; $i++) 
		{
			$str = $str."0";
			$str = substr($str , 1 ) ;
			//echo "str : $str <BR>";
		}
		return $this->cdec($str) ;
	}
	
	public function cdec($num)
	{
	
		for ($n = 0 ; $n < strlen($num) ; $n++)
		{
		   $temp = $num[$n] ;
		   $dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
		}
	
		return $dec;
	}
}


