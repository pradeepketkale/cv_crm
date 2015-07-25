<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */


class OnePica_AvaTax_Block_Adminhtml_Tax_Class_Edit_Form extends Mage_Adminhtml_Block_Tax_Class_Edit_Form
{
	
    protected function _prepareForm()
    {
        $model  = Mage::registry('tax_class');
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $classType  = $this->getClassType();

        $this->setTitle($classType == 'CUSTOMER'
            ? Mage::helper('cms')->__('Customer Tax Class Information')
            : Mage::helper('cms')->__('Product Tax Class Information')
        );

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => $classType == 'CUSTOMER'
                ? Mage::helper('tax')->__('Customer Tax Class Information')
                : Mage::helper('tax')->__('Product Tax Class Information')
        ));

        $fieldset->addField('op_avatax_code', 'text',
            array(
                'name'  => 'op_avatax_code',
                'label' => Mage::helper('tax')->__('AvaTax Code'),
                'value' => $model->getOpAvataxCode(),
            )
        );

        $fieldset->addField('class_name', 'text',
            array(
                'name'  => 'class_name',
                'label' => Mage::helper('tax')->__('Class Name'),
                'class' => 'required-entry',
                'value' => $model->getClassName(),
                'required' => true,
            )
        );

        $fieldset->addField('class_type', 'hidden',
            array(
                'name'      => 'class_type',
                'value'     => $classType,
                'no_span'   => true
            )
        );

        if ($model->getId()) {
            $fieldset->addField('class_id', 'hidden',
                array(
                    'name'      => 'class_id',
                    'value'     => $model->getId(),
                    'no_span'   => true
                )
            );
        }

        $form->setAction($this->getUrl('*/tax_class/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
}