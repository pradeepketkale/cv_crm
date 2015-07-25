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

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 * @version    1.0
 */

class AW_Helpdeskultimate_Model_Data_Parser_Html extends AW_Helpdeskultimate_Model_Data_Parser_Abstract{

    const PERMITTED_TAGS = "<ul><ol><li><blockquote><strong><table><thead><tfoot><tbody><tr><td><hr><sub><sup><code><img><br><b><i><u><a><p><em><span>";
    const RP_GET_IMG = "/<img[^>]*>/i";
    const RP_GET_ATTR = "/(src|alt|title)[^\"\']*(\"|\')[^\"\']*(\"|\')/";
    const NEW_IMG = '<div class="message-image">Image</div>';

    public function clear(){
        //TODO: replace strip_tags to method, which will replace unpermitted tags to htmlentities
        $_text = strip_tags($this->getText(), self::PERMITTED_TAGS);
        $this->setText($_text);
        $this->__stripUnclosedTags();
        return $this;
    }

    /**
     * Formats quotes to HTML
     * @return string
     */
    public function formatQuotes(){
        return $this->getText();
    }

    /**
     * Returns content w/o quotes
     * @return string
     */
    public function convertToQuoteAsHtml(){
        $this->clear();
        $text = "<blockquote>" . $this->getText() . "</blockquote>";
        $this->setText($text);
        return $this;
    }

    public function convertToQuoteAsText(){
        $this->clear();
        $text = strip_tags($this->getText());
        $text = preg_replace("/(([^\n\r]*)([\n\r]+))/", ">$0", $text . "\n\r");
        $this->setText($text);
        return $this;
	}

    public function convertToQuote(){
        return $this->convertToQuoteAsHtml();
    }

    /**
     * Prepares text to display
     * @return string
     */
    public function prepareToDisplay($isStripAttributes = true){
        $this->clear();
        $_imageAttributes = $this->__microparseImages();

        foreach( $_imageAttributes as $__keyOfImage => $__image){
            $attributesAsArray = array();
            foreach($__image as $__attr){
                $result = $this->__microparseAttributes($__attr);
                $attributesAsArray[$result[0]] = $result[1];
            }
            $_imageAttributes[$__keyOfImage] = $attributesAsArray;
        }
        $_text = $this->__replaceImages();
        $this->setText($_text);
        if ($isStripAttributes) {
            $this->__stripTagAttributes();
        }
        
        $html = Mage::helper('helpdeskultimate/parser')->getSmartText($this->getText());
        $__imagesBlock = Mage::helper('helpdeskultimate/parser')->getImageBlock($_imageAttributes);
        $html .= $__imagesBlock;
        $this->setText($html);
        
        return $this;
    }

    private function __microparseImages($_text = null){
        if (is_null($_text)){
            $_text = $this->getText();
        }
        $_images = $this->__getImages($_text);
        $_resultArrayOfImages = array();
        foreach ($_images as $__key => $__item){
            while(preg_match(self::RP_GET_ATTR, $__item[0], $_res)){
                $_resultArrayOfImages[$__key][] = $_res[0];
                $__item[0] = str_replace($_res[0], "", $__item[0]);
            }
        }
        return $_resultArrayOfImages;
    }

    private function __microparseAttributes($_attr){
        $_keyPattern = '/([\w]*)=/';
        $_valuePattern = '/(\"|\')([^\"\']*)(\"|\')/';
        preg_match($_keyPattern, $_attr, $__keyOfArray);
        preg_match($_valuePattern, $_attr, $__valueOfArray);
        $__keyOfArray = $__keyOfArray[1];
        $__valueOfArray = trim($__valueOfArray[2]);
        return array($__keyOfArray, $__valueOfArray);
    }

    private function __replaceImages($_text = null){
        if (is_null($_text)){
            $_text = $this->getText();
        }
        return preg_replace(self::RP_GET_IMG, self::NEW_IMG, $_text);
    }

    private function __getImages($_text){
        $_images = array();
        preg_match_all(self::RP_GET_IMG, $_text, $_images, PREG_OFFSET_CAPTURE);
        $_images = $_images[0];
        return $_images;
    }


    /*If permitted tag does not has closing tag, then this tag will been removed*/
    private function __stripUnclosedTags()
    {
        if (!defined('OPEN_TAG_FLAG')) {
            define('OPEN_TAG_FLAG', 'open');
        }
        if (!defined('CLOSE_TAG_FLAG')) {
            define('CLOSE_TAG_FLAG', 'close');
        }
        $text = $this->getText();
        $tags = array();
        preg_match_all('/<[^>]+>/', self::PERMITTED_TAGS, $tags);
        foreach($tags[0] as $__tag) {
            if ($__tag == '<img>' || $__tag == '<br>' || $__tag == '<hr>') {
                continue;
            }
            //Find open and close tags
            $__openingTags = array();
            $__openTagPattern = '/' . substr($__tag, 0, strlen($__tag) -1) . '[\s>]/';
            preg_match_all($__openTagPattern, $text, $__openingTags, PREG_OFFSET_CAPTURE);
            $__closingTags = array();
            $__closeTagPattern = '/<\/' . substr($__tag, 1) . '/';
            preg_match_all($__closeTagPattern, $text, $__closingTags, PREG_OFFSET_CAPTURE);

            //the following code combine matches in map array
            $__tagMap = array();
            foreach($__openingTags[0] as $tagPos) {
                $__tagMap[$tagPos[1]] = OPEN_TAG_FLAG;
            }
            foreach($__closingTags[0] as $tagPos) {
                $__tagMap[$tagPos[1]] = CLOSE_TAG_FLAG;
            }
            ksort($__tagMap);
            /*
             * $__tagMap = array(pos_in_text => tag_type = OPEN_TAG_FLAG|CLOSE_TAG_FLAG);
             * this array is sorted by key(pos_in_text) in ascending order.
             */
            //the following part remove valid tags $__tagMap
            $__exit = false;
            while (!$__exit) {
                $__exit = true;
                $__openTagKey = null;
                foreach($__tagMap as $currentKey => $value) {
                    if ($value == OPEN_TAG_FLAG) {
                        $__openTagKey = $currentKey;
                    }
                    elseif($value == CLOSE_TAG_FLAG) {
                        if (!is_null($__openTagKey) && isset($__tagMap[$__openTagKey])) {
                            unset($__tagMap[$__openTagKey]);
                            unset($__tagMap[$currentKey]);
                            $__openTagKey = null;
                            $__exit = false;
                        }
                    }
                    //If script here then error in $__tagMap array
                }
            }

            /*
             * Now $__tagMap contain only invalid HTML tags
             */

            //The following code remove invalid tags from text
            $__newText = '';
            $__offset = 0;
            foreach ($__tagMap as $__pos => $__tagType) {
                $__newText .= substr($text, $__offset, $__pos - $__offset);
                $__offset = strpos($text, '>', $__pos) + 1;
            }
            $__newText .= substr($text, $__offset);
            $text = $__newText;
        }
        $this->setText($text);
        return $this;
    }


    /* strip attributes from all tags but not 'div' and 'a' tag*/
    private function __stripTagAttributes()
    {
        $_text = preg_replace("/<([bce-z][a-z]+)[^>]*?(\/?)>/i", "<$1$2>", $this->getText());
        $this->setText($_text);
        return $this;
    }

}