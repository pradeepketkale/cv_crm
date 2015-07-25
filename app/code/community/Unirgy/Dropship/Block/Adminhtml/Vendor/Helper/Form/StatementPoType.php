<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_StatementPoType extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .= '
<script type="text/javascript">
var switchStatementPoStatusSelect = function() {
	for (i=0; i<$("statement_po_type").options.length; i++) {
		var statusSel = $("statement_"+$("statement_po_type").options[i].value+"_status")
		if (statusSel) {
    		if (statusSel.id == "statement_"+$("statement_po_type").value+"_status") {
    			statusSel.up("tr").show()
    			statusSel.enable()
    		} else {
    			statusSel.up("tr").hide()
    			statusSel.disable()
    		}
		}
	}
}
$("statement_po_type").observe("change", switchStatementPoStatusSelect)
document.observe("dom:loaded", switchStatementPoStatusSelect)
</script>        	
        ';
        return $html;
    }
}

