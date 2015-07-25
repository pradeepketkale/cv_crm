<?php

class Craftsvilla_Seocontent_Block_Adminhtml_Seocontent_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('seocontentGrid');
      $this->setDefaultSort('seocontent_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('seocontent/seocontent')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('seocontent_id', array(
          'header'    => Mage::helper('seocontent')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'seocontent_id',
      ));

      $this->addColumn('content', array(
          'header'    => Mage::helper('seocontent')->__('Category Content'),
          'align'     =>'left',
          'index'     => 'content',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('seocontent')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('category', array(
          'header'    => Mage::helper('seocontent')->__('Category'),
          'align'     => 'left',
          'width'     => '150px',
          'index'     => 'category',
          'type'      => 'options',
          'options'   => array(
              74 => 'Sarees',
			  6 => 'Jewellery',
			  33 => 'Necklaces',
			  54 => 'Anklets',
			  55 => 'Bracelets n Bangles',
			 214 => 'Rings',
             248 => 'Pendants',
             34 => 'Earrings',
			 358 => 'Banarasi Sarees',
             359 => 'Bandhani Sarees',
             360 => 'Chiffon Sarees',
             361 => 'Cotton Sarees',
             362 => 'Cotton Silk Sarees',
             363 => 'Designer Sarees',
             365 => 'Georgette Sarees',
             366 => 'Handwoven Sarees',
             367 => 'Heavy Work Sarees',
             368 => 'Jacquard Sarees',
			 369 => 'Kanchivaram Sarees',
			 370 => 'Leheriya Sarees',
			 371 => 'Bollywood Sarees',
			 372 => 'Net Sarees',
			 373 => 'Satin Sarees',
			 374 => 'Silk Sarees',
			 375 => 'Wedding Sarees',
			 781 => 'Ragini Sarees',			
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('seocontent')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('seocontent')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('seocontent')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('seocontent')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('seocontent_id');
        $this->getMassactionBlock()->setFormFieldName('seocontent');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('seocontent')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('seocontent')->__('Are you sure?')
        ));

        /*$statuses = Mage::getSingleton('seocontent/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('seocontent')->__('Change status'),*/
            // 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('seocontent')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}