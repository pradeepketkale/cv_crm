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

class AW_Helpdeskultimate_Block_Adminhtml_Widget_Grid_Column_Renderer_Lock extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function _getValue(Varien_Object $row)
    {
        $text = parent::_getValue($row);
        if (is_null($this->__getPreviousText($text)) || is_null($this->__getDatetime($text))) {
            return $text;
        }
        $title = $this->__getPreviousText($text) . $this->__getDatetime($text);
        $lockElement = "";
        if ($title) {
            $lockElement = "<span title='" . $title . "'>" . $this->__('Locked') . "</span>";
        }
        return $lockElement;
    }

    private function __getDatetime($text) {
        preg_match("/at (.*)$/", $text, $matches);
        if (strlen($matches[1]) != 0) {
            $format = Mage::app()->getLocale()->getDateTimeFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
            $datetime = Mage::app()->getLocale()->date($matches[1], Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            return $datetime;
        }
        else {
            return null;
        }
    }

    private function __getPreviousText($text) {
        preg_match("/^.*at /", $text, $matches);
        if (isset($matches[0]) && strlen($matches[0]) != 0) {
            return $matches[0];
        }
        else {
            return null;
        }
    }
}
