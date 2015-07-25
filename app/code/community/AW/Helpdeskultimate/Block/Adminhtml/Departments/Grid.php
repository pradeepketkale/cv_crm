<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @version    2.9.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('departmentsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setNoFilterMassactionColumn(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/department')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
                                    'header' => $this->__('ID'),
                                    'align' => 'right',
                                    'width' => '50px',
                                    'index' => 'id',
                               ));
        $this->addColumn('enabled', array(
                                         'header'  => $this->__('Active'),
                                         'align'   => 'right',
                                         'width'   => '50px',
                                         'index'   => 'enabled',
                                         'type'    => 'options',
                                         'options' => array(
                                             0 => $this->__('No'),
                                             1 => $this->__('Yes')
                                         )
                                    ));
        $this->addColumn('visibility', array(
                                            'header'  => $this->__('Visibility'),
                                            'align'   => 'right',
                                            'width'   => '50px',
                                            'index'   => 'visibility',
                                            'type'    => 'options',
                                            'options' => Mage::getModel('helpdeskultimate/source_visibility')->toOptionArray()
                                       ));

        $this->addColumn('name', array(
                                      'header' => $this->__('Title'),
                                      'align'  => 'left',
                                      'width'  => '200px',
                                      'index'  => 'name'
                                 ));
        $this->addColumn('email', array(
                                       'header' => $this->__('Email'),
                                       'align' => 'left',
                                       'width' => '200px',
                                       'index' => 'contact'
                                  ));


        $this->addColumn('action',
                         array(
                              'header'  => $this->__('Action'),
                              'width'   => '100',
                              'type'    => 'action',
                              'getter'  => 'getId',
                              'actions' => array(
                                  array(
                                      'caption' => $this->__('Delete'),
                                      'url'     => array('base' => '*/*/delete'),
                                      'field'   => 'id'
                                  ),
                                  array(
                                      'caption' => $this->__('Edit'),
                                      'url'     => array('base' => '*/*/edit'),
                                      'field'   => 'id'
                                  )
                              ),
                              'filter'    => false,
                              'sortable'  => false,
                              'index'     => 'stores',
                              'is_system' => true,
                         ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('departments');
        $this->getMassactionBlock()->setFormFieldName('departments');

        $this->getMassactionBlock()->addItem('delete', array(
                                                            'label'   => $this->__('Delete'),
                                                            'url'     => $this->getUrl('*/*/massDelete'),
                                                            'confirm' => $this->__('Are you sure?')
                                                       ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/departments/edit/', array('id' => $row->getId()));
    }

}
