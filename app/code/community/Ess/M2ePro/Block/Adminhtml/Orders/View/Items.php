<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_Items extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_orderModel = null;
    protected $_accountMode = 0;

    public function __construct($attributes)
    {
        parent::__construct($attributes);

        // Initialization block
        //------------------------------
        $this->setId('ebayOrdersItemsGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        //------------------------------

        if (isset($attributes['order'])) {
            $this->_orderModel = $attributes['order'];
        }
        
        if (isset($attributes['accountMode'])) {
            $this->_accountMode = $attributes['accountMode'];
        }
    }

    protected function _prepareCollection()
    {
        $collection = $this->_orderModel->getOrderItemsCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProduct')
        ));

        $this->addColumn('original_price', array(
            'header'    => Mage::helper('M2ePro')->__('Original Price'),
            'align'     => 'left',
            'width'     => '80px',
            'frame_callback' => array($this, 'callbackColumnOriginalPrice')
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('Qty'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'qty_purchased'
        ));

        $this->addColumn('row_total', array(
            'header'    => Mage::helper('M2ePro')->__('Row Total'),
            'align'     => 'left',
            'width'     => '80px',
            'frame_callback' => array($this, 'callbackColumnRowTotal')
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $returnString = '<b>'.Mage::helper('M2ePro')->escapeHtml($row->getItemTitle()).'</b><br />';

        if ($variations = $row->getVariations()) {
            if (is_string($variations)) {
                $variations = unserialize($variations);
            }

            foreach ($variations as $variationName => $variationValue) {
                $returnString .= '<span style="font-weight: bold; font-style: italic; padding-left: 10px;">' . Mage::helper('M2ePro')->escapeHtml($variationName) . ': </span>';
                $returnString .= Mage::helper('M2ePro')->escapeHtml($variationValue) . '<br />';
            }
        }

        $accountMode = $this->_accountMode ? $this->_accountMode : 0;
        $eBayItemUrl = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($row->getItemId(), $accountMode);

        $returnString .= '<a href="'.$eBayItemUrl.'" target="_blank">'.Mage::helper('M2ePro')->__('View on eBay').'</a>';

        if ($productId = $row->getProductId()) {
            $returnString .= ' | <a href="'.$this->getUrl('adminhtml/catalog_product/edit/id/'.$productId).'" target="_blank">'.Mage::helper('M2ePro')->__('View').'</a>';
        }
        
        return $returnString;
    }

    public function callbackColumnOriginalPrice($value, $row, $column, $isExport)
    {
        $productId = $row->getProductId();
        $formattedPrice = '0';

        if ($productId && $product = Mage::getModel('catalog/product')->load($productId)) {
            $formattedPrice = $product->getFormatedPrice();
        }

       return $formattedPrice;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($row->getCurrency(), $row->getPrice());
    }

    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($row->getCurrency(), ($row->getQtyPurchased()*$row->getPrice()));
    }    

    public function getRowUrl($row)
    {
        return '';
    }
}