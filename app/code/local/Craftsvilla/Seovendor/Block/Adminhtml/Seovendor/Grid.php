<?php

class Craftsvilla_Seovendor_Block_Adminhtml_Seovendor_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("seovendorGrid");
				$this->setDefaultSort("vendor_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("seovendor/seovendor")->getCollection();
				$collection->getSelect()->join( 
					 array('t2'=>'udropship_vendor'),
					 'main_table.vendor_id = t2.vendor_id',
					  array('t2.vendor_name')
					 );
				
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("vendor_id", array(
				"header" => Mage::helper("seovendor")->__("Vendor ID"),
				"align" =>"right",
				"width" => "50px",
			        "type" => "int",
				"index" => "vendor_id",
				));
               
            $this->addColumn("vendor_name", array(
				"header" => Mage::helper("seovendor")->__("Vendor Name"),
				"index" => "vendor_name",
				));
					
				$this->addColumn("meta_title", array(
				"header" => Mage::helper("seovendor")->__("Meta Title"),
				"index" => "meta_title",
				));
				
				$this->addColumn("meta_description", array(
				"header" => Mage::helper("seovendor")->__("Meta Description"),
				"index" => "meta_description",
				));
				
				$this->addColumn("meta_keywords", array(
				"header" => Mage::helper("seovendor")->__("Meta Keywords"),
				"index" => "meta_keywords",
				));
				
		//	$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		//	$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('vendor_id');
			$this->getMassactionBlock()->setFormFieldName('vendor_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_seovendor', array(
					 'label'=> Mage::helper('seovendor')->__('Remove Vendorseo'),
					 'url'  => $this->getUrl('*/adminhtml_seovendorbackend/massRemove'),
					 'confirm' => Mage::helper('seovendor')->__('Are you sure?')
				));
			return $this;
		}
			

}