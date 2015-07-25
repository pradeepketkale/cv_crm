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

/**
 * The Onepage Shipping Method Available block
 */
class OnePica_AvaTax_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	/**
	 * Overriding parent to insert session message block if an address has been validated.
	 *
	 * @return string
	 */
	protected function _toHtml ()
	{
		$additional = '';
		if ($this->getAddress()->getAddressNormalized()) {
			$notice = Mage::getSingleton('avatax/config')->getConfig('onepage_normalize_message');
			if ($notice) {
				Mage::getSingleton('avatax/session')->addNotice($notice);
				$additional = $this->getMessagesBlock()->getGroupedHtml();
			}
		}
		return $additional . parent::_toHtml();
	}
}
