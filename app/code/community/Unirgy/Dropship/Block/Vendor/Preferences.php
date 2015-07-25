<?php

class Unirgy_Dropship_Block_Vendor_Preferences extends Mage_Core_Block_Template
{
    public function getFieldsets()
    {
        $hlp = Mage::helper('udropship');

        $visible = Mage::getStoreConfig('udropship/vendor/visible_preferences');
        $visible = $visible ? explode(',', $visible) : false;

        $fieldsets = array();
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code=>$node) {
            $fieldsets[$code] = array(
                'position' => (int)$node->position,
                'legend' => (string)$node->legend,
            );
        }
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                continue;
            }
            if ($visible && !in_array($code, $visible)) {
                continue;
            }
            $type = $node->type ? (string)$node->type : 'text';
            $type = ($type == 'wysiwyg' && !$hlp->isWysiwygAllowed()) ? 'textarea' : $type;

            $field = array(
                'position' => (int)$node->position,
                'type' => $type,
                'name' => $node->name ? (string)$node->name : $code,
                'label' => (string)$node->label,
                'class' => (string)$node->class,
                'note' => (string)$node->note,
            );
            switch ($type) {
            case 'select': case 'multiselect':
                $source = Mage::getSingleton($node->source_model ? (string)$node->source_model : 'udropship/source');
                if (is_callable(array($source, 'setPath'))) {
                    $source->setPath($node->source ? (string)$node->source : $code);
                }
                $field['options'] = $source->toOptionArray();
                break;
            }
            $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
        }

        $fieldsets['account'] = array(
            'position' => 0,
            'legend' => 'Account Information',
            'fields' => array(
                'vendor_name' => array(
                    'position' => 1,
                    'name' => 'vendor_name',
                    'type' => 'text',
                    'label' => 'Vendor Name',
                ),
                'vendor_attn' => array(
                    'position' => 2,
                    'name' => 'vendor_attn',
                    'type' => 'text',
                    'label' => 'Attention To',
                ),
                'email' => array(
                    'position' => 3,
                    'name' => 'email',
                    'type' => 'text',
                    'label' => 'Email Address / Login',
                ),
                'password' => array(
                    'position' => 4,
                    'name' => 'password',
                    'type' => 'text',
                    'label' => 'Login Password',
                ),
                'telephone' => array(
                    'position' => 5,
                    'name' => 'telephone',
                    'type' => 'text',
                    'label' => 'Telephone',
                ),
            ),
        );

        uasort($fieldsets, array($hlp, 'usortByPosition'));
        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            uasort($v['fields'], array($hlp, 'usortByPosition'));
        }

        return $fieldsets;
    }
}