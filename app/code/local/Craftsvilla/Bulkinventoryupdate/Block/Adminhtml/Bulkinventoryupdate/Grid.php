<?php

class Craftsvilla_Bulkinventoryupdate_Block_Adminhtml_Bulkinventoryupdate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
	   $this->setId('bulkinventoryupdateGrid');
      $this->setDefaultSort('bulkinventoryupdateid');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
	  
      $this->addColumn('bulkinventoryupdateid', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('bulkinventoryupdateId'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'bulkinventoryupdateid',
      ));
	  $this->addColumn('uploaded', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('Uploaded'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'uploaded',
      ));

      $this->addColumn('filename', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('FileName'),
          'align'     =>'left',
          'index'     => 'filename',
		  'type'     => 'text',
		 ));
    
	 $this->addColumn('status', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('Status'),
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
          ),
      ));
	   $this->addColumn('vendor', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('Vendor'),
          'align'     =>'left',
          'index'     => 'vendor',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	 
	  $this->addColumn('totalproducts', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('Total Products'),
          'align'     =>'left',
          'index'     => 'totalproducts',
      ));
	  
	   $this->addColumn('filenameurl', array(
          'header'    => Mage::helper('bulkinventoryupdate')->__('File Name Url'),
          'align'     =>'left',
          'index'     => 'filenameurl',
		  'type' => 'text',
      ));
	 	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('bulkinventoryupdateid');
        $this->getMassactionBlock()->setFormFieldName('bulkinventoryupdate');

        $this->getMassactionBlock()->addItem('inventoryupload', array(
             'label'    => Mage::helper('bulkinventoryupdate')->__('Update'),
             'url'      => $this->getUrl('*/*/inventoryupload'),
               ));
		        $statuses = Mage::getSingleton('bulkuploadcsv/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('bulkinventoryupdate')->__('Change Status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('bulkinventoryupdate')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

       
		 $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('bulkinventoryupdate')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('bulkinventoryupdate')->__('Are you sure?')
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
