<?php

class Mage_Adminhtml_Block_Dashboard_Searches_Top extends Mage_Adminhtml_Block_Dashboard_Grid
{
    protected $_collection;

    public function __construct()
    {
        parent::__construct();
        $this->setId('topSearchGrid');
    }

    protected function _prepareCollection()
    {
       // Commented By Dileswar On dated 02-04-2013 For Stopping Xml Connect searc results
	   
	   /* $this->_collection = Mage::getModel('catalogsearch/query')
            ->getResourceCollection();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = $this->getRequest()->getParam('store');
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else {
            $storeIds = '';
        }

        $this->_collection
            ->setPopularQueryFilter($storeIds);

        $this->setCollection($this->_collection);*/

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('search_query', array(
            'header'    => $this->__('Search Term'),
            'sortable'  => false,
            'index'     => 'query_text',
            'renderer'  => 'adminhtml/dashboard_searches_renderer_searchquery',
        ));

        $this->addColumn('num_results', array(
            'header'    => $this->__('Results'),
            'sortable'  => false,
            'index'     => 'num_results',
            'type'      => 'number'
        ));

        $this->addColumn('popularity', array(
            'header'    => $this->__('Number of Uses'),
            'sortable'  => false,
            'index'     => 'popularity',
            'type'      => 'number'
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_search/edit', array('id'=>$row->getId()));
    }
}
