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

class AW_Helpdeskultimate_Helper_Parser extends AW_Helpdeskultimate_Helper_Abstract
{

    public function isDepartmentAuthor($Message)
    {
        if ($Message instanceof AW_Helpdeskultimate_Model_Message && $Message->isDepartmentReply()) {
            return true;
        }
        elseif($Message instanceof AW_Helpdeskultimate_Model_Ticket && $Message->getCreatedBy() == 'admin') {
            return true;
        }
        return false;
    }
    
    /*
     *
     */
    public function getImageBlock($attributes)
    {
        $html = "";
        if(count($attributes) > 0) {
            $html .= '<div id="image-list-box">';
            $html .= '<h4>' . $this->__('Images') . '</h4>';
            $html .= '<ul class="image-list">';

            foreach($attributes as $_item) {
                if (isset($_item['src']) && !empty($_item['src'])) {
                    $html .= '<li>';
                        if (isset($_item['title']) && (strlen($_item['title']) > 0)) {
                            $html .= $_item['title'];
                        }
                        elseif (isset($_item['alt']) && (strlen($_item['alt']) > 0)) {
                            $html .= $_item['alt'];
                        }
                        else {
                            $html .= $this->__('Image');
                        }
                    $html .= "&nbsp;<a href=" . $_item['src'] . " title = " .$_item['title'] . " alt = " . $_item['alt'] . ">" . $_item['src'] . "</a>";
                    $html .= "</li>";
                }
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        return $html;
    }

    public function getSmartText($originText)
    {
        /* URLS replace url to link */
       // $__replacement = '<a href="http$3://$4$5">$4$5</a>';
       // $text = preg_replace('#(^|\s)(http://|http(s)://)?([\w-]+\.)?([\w-]+?\.[\w-]+?)(/|$|\b)#is', $__replacement, $originText);
       
        //https://aheadworks.fogbugz.com/default.asp?7426#77140
        $text = preg_replace('#\b(?<!http://|https://)(www\.\S+?\.\S+?)\b#is', "<a href ='http://$1'>http://$1</a>", $originText);
        

        /* Replace ticket UIDS for links to ticket*/
        $linkTemplate = "<a href='{{href}}'>{{name}}</a>";
        preg_match_all('/#[A-Z]{3}-[0-9]{5}/i', $text, $matches);
        foreach ($matches[0] as $uid) {
            $_simpleUid = str_replace('#', '', $uid);
            $ticket = Mage::getModel('helpdeskultimate/ticket')->loadByUid($_simpleUid);
            if ($ticket->hasId()) {
                $_linkText = str_replace('{{href}}', $ticket->getUrl(), $linkTemplate);
                $_linkText = str_replace('{{name}}', $uid, $_linkText);
                $text = str_replace($uid, $_linkText, $text);
            }
        }

        return $text;
    }
}