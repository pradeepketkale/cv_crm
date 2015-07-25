<?php

class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('mktngproductsGrid');
      $this->setDefaultSort('mktngproducts_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mktngproducts/mktngproducts')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      
      $this->addColumn('mktngproducts_id', array(
          'header'    => Mage::helper('mktngproducts')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'mktngproducts_id',
      ));
	  $this->addColumn('image', array(
          'header'    => Mage::helper('mktngproducts')->__('Product Image'),
          'align'     =>'left',
          'width'     => '20px',
          'index'     => 'image',
          'renderer'  => 'mktngproducts/adminhtml_mktngproducts_renderer_image',
          'attr1'     => 'value1'
      ));
      $this->addColumn('product_sku', array(
          'header'    => Mage::helper('mktngproducts')->__('Sku'),
          'align'     =>'left',
          'width'     => '20',
          'index'     => 'product_sku', //MTEST11517294030
          'renderer'  => 'mktngproducts/adminhtml_mktngproducts_renderer_sku',
          
      ));
      $this->addColumn('created_at', array(
          'header'    => Mage::helper('mktngproducts')->__('Date'),
          'align'     =>'left',
          'index'     => 'created_at',
      )); 
      /*$this->addColumn('product_url', array(
          'header'    => Mage::helper('mktngproducts')->__('Product URL'),
          'align'     =>'left',
          'index'     => 'product_url',
      ));*/
      $this->addColumn('vendor_name', array(
          'header'    => Mage::helper('mktngproducts')->__('Vdr'),
          'align'     =>'left',
          'index'     => 'vendor_name',
          'sortable'  => 'true',
          'renderer'  => 'mktngproducts/adminhtml_mktngproducts_renderer_vendorurl'
          ));
      
	  $this->addColumn('sale_one_day', array(
			'header'    => Mage::helper('mktngproducts')->__('Sale24'),
			'width'     => '150px',
			'index'     => 'sale_one_day',
			'sortable'  => 'true',
			'type' => 'number',       
      ));
	  $this->addColumn('sale_seven_days', array(
			'header'    => Mage::helper('mktngproducts')->__('Sale7'),
			'width'     => '150px',
			'index'     => 'sale_seven_days',
			'sortable'  => 'true',
			'type' => 'number',
      ));
      $this->addColumn('sale_thirty_days', array(
			'header'    => Mage::helper('mktngproducts')->__('Sale30'),
			'width'     => '150px',
			'index'     => 'sale_thirty_days',
			'sortable'  => 'true',
			'type' => 'number',
      ));
      $this->addColumn('fb_post_id', array(
			'header'    => Mage::helper('mktngproducts')->__('Pst'),
			'width'     => '10px',
			'index'     => 'fb_post_id',
			'renderer'  => 'mktngproducts/adminhtml_mktngproducts_renderer_fbpostid'
      ));
      $this->addColumn('fb_likes', array(
			'header'    => Mage::helper('mktngproducts')->__('LIKES'),
			'width'     => '150px',
			'index'     => 'fb_likes',
			'sortable'  => 'true',
			'type' => 'number',
      )); 
      $this->addColumn('fb_comments', array(
			'header'    => Mage::helper('mktngproducts')->__('COMMENTS'),
			'width'     => '150px',
			'index'     => 'fb_comments',
			'sortable'  => 'true',
			'type' => 'number',
      ));
      /*$this->addColumn('fb_shares', array(
			'header'    => Mage::helper('mktngproducts')->__('SHARES'),
			'width'     => '150px',
			'index'     => 'fb_shares',
			'sortable'  => 'true',
			'type' => 'number',
      ));*/
      $this->addColumn('product_inventory', array(
			'header'    => Mage::helper('mktngproducts')->__('Qty'),
			'width'     => '150px',
			'index'     => 'product_inventory',
			'sortable'  => 'true',
			'type' => 'number',
      ));
      $this->addColumn('refund_percentage', array(
			'header'    => Mage::helper('mktngproducts')->__('Refund%'),
			'width'     => '150px',
			'index'     => 'refund_percentage',
			'sortable'  => 'true',
			'type' => 'number',
      ));
      $this->addColumn('total_shipments', array(
			'header'    => Mage::helper('mktngproducts')->__('Sn'),
			'width'     => '150px',
			'index'     => 'total_shipments',
			'sortable'  => 'true',
      ));
      $this->addColumn('dispatch_time', array(
			'header'    => Mage::helper('mktngproducts')->__('DT'),
			'width'     => '150px',
			'index'     => 'dispatch_time',
			'sortable'  => 'true',
      ));
      
      
      /*$this->addColumn('status', array(
          'header'    => Mage::helper('mktngproducts')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Updated',
              2 => 'Not Updated',
          ),
      ));*/
	  
       /* $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('mktngproducts')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('mktngproducts')->__('Edit'),
                        'url'       => array('base'=> '*//**//*edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));*/
		

		
		
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('mktngproducts')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('mktngproducts')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('mktngproducts_id');
        $this->getMassactionBlock()->setFormFieldName('mktngproducts');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mktngproducts')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mktngproducts')->__('Are you sure?')
        ));
		$this->getMassactionBlock()->addItem('promo', array(
             'label'    => Mage::helper('mktngproducts')->__('Promote'),
             'url'      => $this->getUrl('*/*/massPromote'),
       ));
		$this->getMassactionBlock()->addItem('depromo', array(
             'label'    => Mage::helper('mktngproducts')->__('DePromote'),
             'url'      => $this->getUrl('*/*/massDepromote'),
       ));

        //$statuses = Mage::getSingleton('mktngproducts/status')->getOptionArray();

       // array_unshift($statuses, array('label'=>'', 'value'=>''));
        //$this->getMassactionBlock()->addItem('status', array(
             //'label'=> Mage::helper('mktngproducts')->__('Change status'),
          //   'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            // 'additional' => array(
              //      'visibility' => array(
                //         'name' => 'status',
                  //       'type' => 'select',
                    //     'class' => 'required-entry',
                      //   'label' => Mage::helper('mktngproducts')->__('Status'),
                        // 'values' => $statuses
                    // )
           //  )
        //));
        return $this;
    }

  public function getRowUrl($row)
  {
     // return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  public function getGridUrl()
  {
          return $this->getUrl('*/*/grid', array('_current' => true));
  }

}
