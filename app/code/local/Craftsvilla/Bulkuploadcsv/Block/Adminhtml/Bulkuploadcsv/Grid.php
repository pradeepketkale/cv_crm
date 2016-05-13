
<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('bulkuploadcsvGrid');
    $this->setDefaultSort('bulkuploadid');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setDefaultLimit(50);
    $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
    $collection = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->getCollection();
    $collection->getSelect()->limit(50);
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
	  
      $this->addColumn('bulkuploadid', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('bulkuploadId'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'bulkuploadid',
      ));
	  $this->addColumn('uploaded', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Uploaded'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'uploaded',
      ));

      $this->addColumn('filename', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('FileName'),
          'align'     =>'left',
          'index'     => 'filename',
		  'type'     => 'text',
		
	      ));
    
	 $this->addColumn('status', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Processing',
              2 => 'Completed',
			  3 => 'Submitted',
			  4 => 'Rejected',
			  5 => 'Approved',
        6 => 'Approved For Variants',
          ),
      ));
	   $this->addColumn('vendor', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Vendor'),
          'align'     =>'left',
          'index'     => 'vendor',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	  $this->addColumn('productsactiveted', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Products Uploaded'),
          'align'     =>'left',
          'index'     => 'productsactiveted',
      ));
	  $this->addColumn('productsrejected', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Products Rejected'),
          'align'     =>'left',
          'index'     => 'productsrejected',
      ));
	  $this->addColumn('totalproducts', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Total Submitted'),
          'align'     =>'left',
          'index'     => 'totalproducts',
      ));
	  $this->addColumn('errorreport', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Error Report'),
          'align'     =>'left',
          'index'     => 'errorreport',
		      'renderer'  => 'Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Renderer_Error',
		
      ));

	   $this->addColumn('filepath', array(
          'header'    => Mage::helper('bulkuploadcsv')->__('Download File'),
          'align'     =>'left',
          'index'     => 'filepath',
		      'renderer'  => 'Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Renderer_File',
		
      ));
	  
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('bulkuploadid');
        $this->getMassactionBlock()->setFormFieldName('bulkuploadcsv');

        $this->getMassactionBlock()->addItem('upload', array(
             'label'    => Mage::helper('bulkuploadcsv')->__('Upload'),
             'url'      => $this->getUrl('*/*/upload'),
               ));
			   
		 array_unshift($reject, array('label'=>'', 'value'=>''));
         $this->getMassactionBlock()->addItem('reject', array(
             'label'    => Mage::helper('bulkuploadcsv')->__('Reject'),
             'url'      => $this->getUrl('*/*/reject', array('_current'=>true)),
			 'additional' => array(
                    'visibility' => array(
                         'name' => 'reject',
                         'type' => 'text',
                         'class' => 'required-entry',
                         'label' => Mage::helper('bulkuploadcsv')->__('Reject'),
                       //  'values' => $this->getUrl('*/*/reject', array('_current'=>true)),
                     )
             )
               ));	
		
        $statuses = Mage::getSingleton('bulkuploadcsv/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('bulkuploadcsv')->__('Change Status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('bulkuploadcsv')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
		 $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('bulkuploadcsv')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('bulkuploadcsv')->__('Are you sure?')
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
