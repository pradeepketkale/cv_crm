<?php
class Marketplace_Peritemshippingcost_Model_Quote_Item extends Mage_Sales_Model_Quote_Item
{
  public function calcRowTotal()
  {
      parent::calcRowTotal();
      $product = $this->getProduct();
      $product->load($product->getId());

	$qty        = $this->getTotalQty();
/*	$total      = $this->getCalculationPrice()*$qty;
	$baseTotal  = $this->getBaseCalculationPrice()*$qty;*/


      // This is where I add the Setup Fee, more than one fee can be added
      // here if necessary
//      $baseTotal = ($this->getBaseRowTotal() + $product->getShippingcost())*$qty;
      $baseTotal = ($this->getCalculationPrice() + $product->getShippingcost())*$qty;
      $total = $this->getStore()->convertPrice($baseTotal);
      $this->setRowTotal($this->getStore()->roundPrice($total));
      $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
      return $this;
  }
}
