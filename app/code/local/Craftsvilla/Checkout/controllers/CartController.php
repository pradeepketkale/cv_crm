<?php 
require_once("Mage/Checkout/controllers/CartController.php");

class Craftsvilla_Checkout_CartController extends Mage_Checkout_CartController
{
	public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }
        
       if($this->_getCart()->getQuote()->getAppliedRuleIds()!='' && $this->_getCart()->getQuote()->getCouponCode()==''){
           $this->_getSession()->addError(
                        $this->__('You can not combine this coupon with any other promotions.', Mage::helper('core')->htmlEscape($couponCode))
                    ); 
           $this->_goBack();
           return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');

	$couponcodeSub = strtoupper(substr($couponCode,0,3));
		if($couponcodeSub == 'NCA'){
			$couponCode = 'NCA1111';
		}


        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
}
