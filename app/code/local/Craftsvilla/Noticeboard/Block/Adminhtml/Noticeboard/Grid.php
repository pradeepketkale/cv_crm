
<?php

class Craftsvilla_Noticeboard_Block_Adminhtml_Noticeboard_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
	   $this->setId('noticeboardGrid');
      $this->setDefaultSort('noticeid');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('noticeboard/noticeboard')->getCollection();
	  $this->setCollection($collection);
	  return parent::_prepareCollection();
	  return $this;
  }

  protected function _prepareColumns()
  {
	  
      $this->addColumn('noticeid', array(
          'header'    => Mage::helper('noticeboard')->__('noticeId'),
          'align'     =>'right',
          'width'     => '20px',
          'index'     => 'noticeid',
      ));
	  
	    $this->addColumn('created', array(
          'header'    => Mage::helper('noticeboard')->__('Created'),
          'align'     =>'right',
          'width'     => '80px',
          'index'     => 'created',
      ));
	  
	  $this->addColumn('content', array(
          'header'    => Mage::helper('noticeboard')->__('Content'),
          'align'     =>'right',
          'width'     => '650px',
          'index'     => 'content',
      ));
     
	  
      $this->addColumn('type',array(
          'header'    => Mage::helper('noticeboard')->__('Type'),
          'align'     =>'left',
		  'width'     => '40px',
          'index'     => 'type',
		  'type'      => 'options',
		  'value' => 3,
          'options'   => array(3 => 'Admin',4 => 'Seller', 5=> 'Customer')
		  
	      ));
      $this->addColumn('image', array(
          'header'    => Mage::helper('noticeboard')->__('Image'),
          'align'     =>'left',
          'index'     => 'image',
		  'type' => 'text',
		 // 'renderer'  => new Craftsvilla_Noticeboard_Block_Adminhtml_Renderer_Image,
		  'width'     => '75px',
		  'height' => '80px',
		  'align' => 'center',
      ));

	  
	 $this->addColumn('status', array(
          'header'    => Mage::helper('noticeboard')->__('Status'),
          'align'     => 'left',
          'width'     => '100px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Approved',
              2 => 'Not Approved',
		   ),
      ));
	  
	    
	 
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('noticeid');
        $this->getMassactionBlock()->setFormFieldName('noticeboard');

      /*  $this->getMassactionBlock()->addItem('upload', array(
             'label'    => Mage::helper('noticeboard')->__('Upload'),*/
            // 'url'      => $this->getUrl('*/*/upload'),
          //     ));
			   
				
        $statuses = Mage::getSingleton('noticeboard/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('noticeboard')->__('Change Status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('noticeboard')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
		 $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('noticeboard')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('noticeboard')->__('Are you sure?')
        ));

        return $this;
    }
	
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

  public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}
}
