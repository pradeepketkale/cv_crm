<?php

class Unirgy_Dropship_Block_Vendor_Product_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('product.grid.toolbar')) {
            $toolbar->setCollection($this->getProductCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    
    protected function _applyRequestFilters($collection)
    {
        $r = Mage::app()->getRequest();
        $param = $r->getParam('filter_sku');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('sku', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_name');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('name', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_stock_status');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where($this->_getStockField('status').'=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_from');
        if (!is_null($param) && $param!=='') {
            //$collection->addAttributeToFilter('_stock_qty', array('gteq'=>$param));
            $collection->getSelect()->where($this->_getStockField('qty').'>=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_to');
        if (!is_null($param) && $param!=='') {
            //$collection->addAttributeToFilter('_stock_qty', array('lteq'=>$param));
            $collection->getSelect()->where($this->_getStockField('qty').'<=?', $param);
        }
        return $this;
    }
    
    protected function _getStockField($type)
    {
        $v = Mage::getSingleton('udropship/session')->getVendor();
        if (!$v || !$v->getId()) {
            $isLocalVendor = 0;
        } else {
            $isLocalVendor = intval($v->getId()==Mage::getStoreConfig('udropship/vendor/local_vendor'));
        }
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            switch ($type) {
                case 'qty':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null, cisi.qty, uvp.stock_qty)');
                case 'status':
                    return new Zend_Db_Expr("IF(uvp.vendor_product_id is null or $isLocalVendor, cisi.is_in_stock, null)");
            }
        } else {
            switch ($type) {
                case 'qty':
                    return 'cisi.qty';
                case 'status':
                    return 'cisi.is_in_stock';
            }
        }
    }

    public function getProductCollection()
    {
        if (!$this->_collection) {
            $v = Mage::getSingleton('udropship/session')->getVendor();
            if (!$v || !$v->getId()) {
                return array();
            }
            $r = Mage::app()->getRequest();
            $res = Mage::getSingleton('core/resource');
            #$read = $res->getConnection('catalog_product');
            $stockTable = $res->getTableName('cataloginventory/stock_item');
            $oldStoreId = Mage::app()->getStore()->getId();
            Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
            $collection = Mage::getModel('catalog/product')->getCollection()
                //->addAttributeToFilter('udropship_vendor', $v->getId())
                ->addAttributeToFilter('type_id', 'simple')
                ->addAttributeToSelect(array('sku', 'name'/*, 'cost'*/))
            ;
            $conn = $collection->getConnection();
            $collection->addAttributeToFilter('entity_id', array('in'=>array_keys($v->getAssociatedProducts())));
            $collection->getSelect()->join(
                array('cisi' => $stockTable), 
                $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
                array('_stock_status'=>$this->_getStockField('status'))
            );
            if (Mage::helper('udropship')->isUdmultiAvailable()) {
                $collection->getSelect()->joinLeft(
                    array('uvp' => $res->getTableName('udropship/vendor_product')), 
                    $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $v->getId()), 
                    array('_stock_qty'=>$this->_getStockField('qty'), 'vendor_sku'=>'uvp.vendor_sku', 'vendor_cost'=>'uvp.vendor_cost')
                );
                //$collection->getSelect()->columns(array('_stock_qty'=>'IFNULL(uvp.stock_qty,cisi.qty'));
            } else {
                $collection->getSelect()->columns(array('_stock_qty'=>$this->_getStockField('qty')));
            }
            Mage::app()->getStore()->setId($oldStoreId);

            $this->_applyRequestFilters($collection);
            
            #Mage::getModel('cataloginventory/stock')->addItemsToProducts($collection);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
}