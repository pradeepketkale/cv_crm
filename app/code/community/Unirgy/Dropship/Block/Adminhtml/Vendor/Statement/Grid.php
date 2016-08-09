<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statementGrid');
        $this->setDefaultSort('vendor_statement_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('statement_filter');

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udropship/vendor_statement')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');
        $baseUrl = $this->getUrl();

        $this->addColumn('vendor_statement_id', array(
            'header'    => $hlp->__('ID'),
            'index'     => 'vendor_statement_id',
            'width'     => 10,
            'type'      => 'number',
        ));

        $this->addColumn('created_at', array(
            'header'    => $hlp->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        $this->addColumn('statement_id', array(
            'header'    => $hlp->__('Statement ID'),
            'index'     => 'statement_id',
        ));

        $this->addColumn('vendor_id', array(
            'header' => $hlp->__('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('statement_period', array(
            'header' => $hlp->__('Period'),
            'index' => 'statement_period',
        ));

        $this->addColumn('total_orders', array(
            'header'    => $hlp->__('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ));

        $this->addColumn('total_payout', array(
            'header'    => $hlp->__('Total Payment'),
            'index'     => 'total_payout',
            'type'      => 'price',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
        ));

        if ($hlp->isUdpayoutActive()) {
            $this->addColumn('total_paid', array(
                'header'    => $hlp->__('Total Paid'),
                'index'     => 'total_paid',
                'type'      => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            ));
            $this->addColumn('total_due', array(
                'header'    => $hlp->__('Total Due'),
                'index'     => 'total_due',
                'type'      => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            ));
        }

        $this->addColumn('email_sent', array(
            'header' => $hlp->__('Sent'),
            'index' => 'email_sent',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendor_statement_id');
        $this->getMassactionBlock()->setFormFieldName('statement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('udropship')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('udropship')->__('Deleting selected statement(s). Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('refresh', array(
             'label'=> Mage::helper('udropship')->__('Refresh'),
             'url'  => $this->getUrl('*/*/massRefresh', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('download', array(
             'label'=> Mage::helper('udropship')->__('Download/Print'),
             'url'  => $this->getUrl('*/*/massDownload', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('downloadLogistic', array(
             'label'=> Mage::helper('udropship')->__('Download Logistic'),
             'url'  => $this->getUrl('*/*/massDownloadLogistic', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('email', array(
             'label'=> Mage::helper('udropship')->__('Send Emails'),
             'url'  => $this->getUrl('*/*/massEmail', array('_current'=>true)),
             'confirm' => Mage::helper('udropship')->__('Emailing selected statement(s) to vendors. Are you sure?')
        ));

		// Added By Dileswar   On Dated 14-02-2013//////////////////////////////////////////
		$_months = array();
		$year = Mage::app()->getLocale()->date()->get(Zend_date::YEAR);
		$monthNum = Mage::app()->getLocale()->date()->get(Zend_date::MONTH);
        for ($i = 0; $i < 12; $i++) {
			$_monthNum = ($monthNum+12 -$i) % 12;
			if ($_monthNum == 0) $_monthNum = 12;

			if ($monthNum <= $i)
			{
				$_year = $year - 1;
			}
			else{
				$_year = $year;
			}
           $_months[$i] = Mage::app()->getLocale()->date(mktime(null,null,null,$_monthNum))->get(Zend_date::MONTH_NAME).' '.$_year;
		}

		$this->getMassactionBlock()->addItem('statementRdr1',  array(
             'label'=> Mage::helper('udropship')->__('StatementRdr1'),
             'url'  => $this->getUrl('*/*/statementRdr1', array('_current'=>true)),
			 'additional' => array(
									'visibility' =>array(
															'name'   => 'selected_month',
															'label'  => $this->__('Select Month'),
															'title'  => $this->__('Select Month'),
															//'image'  => $this->getSkinUrl('images/grid-cal.gif'),
															//'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
															'values'       => $_months,
															'class'     => 'required-entry',
															'required'  => true,
															'type' => 'select',
														 ),

													 )

        ));

		$this->getMassactionBlock()->addItem('statementOrdr',  array(
             'label'=> Mage::helper('udropship')->__('StatementOrdr'),
             'url'  => $this->getUrl('*/*/statementOrdr', array('_current'=>true)),
			 'additional' => array(
									'visibility' =>array(
															'name'   => 'selected_month',
															'label'  => $this->__('Select Month'),
															'title'  => $this->__('Select Month'),
															//'image'  => $this->getSkinUrl('images/grid-cal.gif'),
															//'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
															'values'       => $_months,
															'class'     => 'required-entry',
															'required'  => true,
															'type' => 'select',
														 ),

													 )

        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
