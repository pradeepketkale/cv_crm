<?php
class Mage_Checkout_Block_Onepage_Payment extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData('payment', array(
            'label'     => $this->__('Choose Payment Method Below And Click Place Order Button'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (float)$this->getQuote()->getBaseGrandTotal();
    }
}
