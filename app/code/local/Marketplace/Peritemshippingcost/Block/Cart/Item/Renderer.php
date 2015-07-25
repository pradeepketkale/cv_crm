<?php
class Marketplace_Peritemshippingcost_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    public function getLoadedProduct()
    {
        return $this->getProduct()->load($this->getProduct()->getId());
    }
    public function getShippingcost()
    {
        return $this->getLoadedProduct()->getShippingcost();
    }
}
