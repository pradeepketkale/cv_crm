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

class AW_Helpdeskultimate_Model_Data_Parser_Text extends AW_Helpdeskultimate_Model_Data_Parser_Abstract {
    /**
     * They expect this function to do something but its truly excessive
     */
    public function clear() {
        $__text = htmlspecialchars($this->getText());
        $this->setText($__text);
        return $this;
    }

    /**
     * They expect this function to do something but its truly excessive
     */
    public function convertToQuoteAsHtml() {
        $text = $this->clear()->getText();
        $text = $this->__microparseBlockquotes(html_entity_decode($text));
        //wrap line in <p></p>
        $text = preg_replace("/([^\n\r$]+)[^>][\n\r$]+/", "<p>$1</p>", $text);
        //remove \n \r
        $text = preg_replace("/([\n\r$]+)/", "", $text);
        $text = "<blockquote>" . $text . "</blockquote>";
        $this->setText($text);
        return $this;
    }

    /**
     * They expect this function to do something but its truly excessive
     */
    public function convertToQuoteAsText() {
        //$text = strip_tags($this->getText());
        $text = preg_replace("/(([^\n\r]*)([\n\r]+))/", ">$0", $this->getText() . "\n\r");
        $this->setText($text);
        return $this;
    }

    public function convertToQuote() {
        return $this->convertToQuoteAsText();
    }

    /**
     *
     */
    public function prepareToDisplay($isStripAttributes = true) {
        $__raw = $this->clear()->getText();
        $__raw = $this->__microparseBlockquotes(html_entity_decode($__raw));
        $this->__pool = $__raw;
        $this->__inserts = array();

        $__changesCount = 1;
        while ($__changesCount > 0) {
            $__changesCount = 0;
            $__changesCount += $this->__microparseLinks();
        }

       /* $__changesCount = 1;
        while ($__changesCount > 0) {
            $__changesCount = 0;
            $__changesCount += $this->__microparseBoldtext();
            $__changesCount += $this->__microparseItalictext();
            $__changesCount += $this->__microparseUnderlinedtext();
        }*/

        $__changesCount = 1;
        while ($__changesCount > 0) {
            $__changesCount = 0;
            $__changesCount += $this->__microparseNewlinesymbols();
        }

        // $__lines[$__key] = preg_replace('/<(http|https):\/\/([\w\.]*\w+\.)([\w-]{3})([\w\/\.\?\&-]*)>/', '<a href="$1://$2$3$4"><img alt="&raquo;" src="" /></a>', $__lines[$__key]);
        // $__lines[$__key] = preg_replace('/\*(\w[\w]*\w)\*/', '<b>$1</b>', $__lines[$__key]);
        // $__lines[$__key] = preg_replace('/\/(\w[\w]*\w)\//', '<i>$1</i>', $__lines[$__key]);
        // $__lines[$__key] = preg_replace('/_(\w[\w]*\w)_/', '<u>$1</u>', $__lines[$__key]);

        $__point = 0;
        $__text = '';
        foreach ($this->__inserts as $__position => $__contentItems) {
            $__text .= substr($this->__pool, $__point, $__position - $__point);
            foreach ($__contentItems as $__item) $__text .= $__item;
            $__point = $__position;
        }
        $__text .= substr($this->__pool, $__point, strlen($this->__pool));
        //convert #AAA-11111 to link to ticket

        $smartText = Mage::helper('helpdeskultimate/parser')->getSmartText($__text);
        $this->setText($smartText);
        return $this;
    }

    /**
     *
     */
    private $__pool;
    private $__inserts;

    /**
     *
     */
    private function __removeFromPool($index, $count) {
        $this->__pool = substr_replace($this->__pool, '', $index, $count);
        foreach ($this->__inserts as $__position => $__contentItems) {
            if ($__position > $index) {
                if (!isset($this->__inserts[$__position - $count])) $this->__inserts[$__position - $count] = array();
                foreach ($this->__inserts[$__position] as $__item) {
                    array_push($this->__inserts[$__position - $count], $__item);
                }
                unset($this->__inserts[$__position]);
            }
        }
        ksort($this->__inserts);
    }

    /**
     *
     */
    private function __insertIntoPool($position, $content) {
        if (!isset($this->__inserts[$position])) $this->__inserts[$position] = array();
        array_push($this->__inserts[$position], $content);
    }

    /**
     *
     */
    private function __microparseLinks() {
        $__changesCount = 0;
        while (preg_match('/<(http|https):\/\/([\w\.]*\w+\.)([\w-]{3})([\w\/\.\?\&-]*)>/U', $this->__pool, $__matches, PREG_OFFSET_CAPTURE)) {
            $__changesCount++;
            $this->__removeFromPool($__matches[0][1], strlen($__matches[0][0]));
            $this->__insertIntoPool($__matches[0][1], '<a href="' . $__matches[1][0] . '://' . $__matches[2][0] . $__matches[3][0] . $__matches[4][0] . '">' . $__matches[1][0] . '://' . $__matches[2][0] . $__matches[3][0] . $__matches[4][0] . '</a>');
        }
        return $__changesCount;
    }

    /**
     *
     */
    private function __microparseBoldtext() {
        $__changesCount = 0;
        while (preg_match('|(\*)([\w][^\n]*[\w])(\*)|U', $this->__pool, $__matches, PREG_OFFSET_CAPTURE)) {
            $__changesCount++;
            $this->__removeFromPool($__matches[1][1], 1);
            $this->__insertIntoPool($__matches[1][1], '<b>');
            $this->__removeFromPool($__matches[3][1] - 1, 1);
            $this->__insertIntoPool($__matches[3][1] - 1, '</b>');
        }
        return $__changesCount;
    }

    /**
     *
     */
    private function __microparseItalictext() {
        $__changesCount = 0;
        while (preg_match('|(\/)([\w][^\n]*[\w])(\/)|U', $this->__pool, $__matches, PREG_OFFSET_CAPTURE)) {
            $__changesCount++;
            $this->__removeFromPool($__matches[1][1], 1);
            $this->__insertIntoPool($__matches[1][1], '<i>');
            $this->__removeFromPool($__matches[3][1] - 1, 1);
            $this->__insertIntoPool($__matches[3][1] - 1, '</i>');
        }
        return $__changesCount;
    }

    /**
     *
     */
    private function __microparseUnderlinedtext() {
        $__changesCount = 0;
        while (preg_match('|(_)([\w][^\n]*[\w])(_)|U', $this->__pool, $__matches, PREG_OFFSET_CAPTURE)) {
            $__changesCount++;
            $this->__removeFromPool($__matches[1][1], 1);
            $this->__insertIntoPool($__matches[1][1], '<u>');
            $this->__removeFromPool($__matches[3][1] - 1, 1);
            $this->__insertIntoPool($__matches[3][1] - 1, '</u>');
        }
        return $__changesCount;
    }

    /**
     *
     */
    private function __microparseNewlinesymbols() {
        $__changesCount = 0;
        while (preg_match('|(\n)|U', $this->__pool, $__matches, PREG_OFFSET_CAPTURE)) {
            $__changesCount++;
            $this->__removeFromPool($__matches[1][1], 1);
            $this->__insertIntoPool($__matches[1][1], "<br />\n");
        }
        return $__changesCount;
    }

    private function __microparseBlockquotes($text) {
        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $__depth = 15;
        $__exit = false;
        while (!$__exit && $__depth > 0) {
            $__exit = true;
            $__depth--;
            //inline fragmentation
            preg_match_all("/([^\n\r]*)[\n\r]{0,2}/", $text, $matches, PREG_OFFSET_CAPTURE);

            //found blockquotes
            $replacementArray = array();
            $_currentQuote = null;
            $_currentQuotedText = "";
            foreach($matches[1] as $quote) {
                preg_match("/^[\d]*(&gt;)(.*)/", $quote[0], $isFounded, PREG_OFFSET_CAPTURE);
                if (!isset($isFounded[2]) || is_null($isFounded[2])) {
                    if (!is_null($_currentQuote)) {
                        $__exit = false;
                        $replacementArray[] = array('from' => $_currentQuote, 'to' => $quote[1], 'text' => $_currentQuotedText);
                        $_currentQuote = null;
                        $_currentQuotedText = "";
                    }
                }
                else {
                    if (is_null($_currentQuote)) {
                        $_currentQuote = $quote[1];
                    }
                    if (isset($isFounded[2])) {
                        $_currentQuotedText .= $isFounded[2][0] . "\n\r";
                    }
                    else {
                        $_currentQuotedText .= "\n\r";
                    }
                }
            }

            //create new line with blockquote tag
            $__newText = '';
            $__offset = 0;
            foreach ($replacementArray as $rule) {
                $__newText .= substr($text, $__offset, $rule['from'] - $__offset);
                $__newText .= "<blockquote>\n" . $rule['text'] . "</blockquote>\n";
                $__offset = $rule['to'];
            }
            $__newText .= substr($text, $__offset);
            $text = $__newText;
            //repeat if replacementArray not empty
        }
        //remove \n from tag end
        $text = preg_replace("/(blockquote>\n)/", "blockquote>", $text);
        return $text;
    }
}
