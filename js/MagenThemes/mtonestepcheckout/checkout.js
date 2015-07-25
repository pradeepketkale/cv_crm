/******************************************************
 * @package MT OneStepCheckOut module for Magento 1.4.x.x and Magento 1.5.x.x
 * @version 1.5.2.1
 * @author http://www.magentheme.com
 * @copyright (C) 2011- MagenTheme.Com
 * @license PHP files are GNU/GPL
*******************************************************/

// onestepcheckout
var Onestepcheckout = Class.create();
Onestepcheckout.prototype = {
    initialize: function(urls){
        this.loadWaitingShippingMethod = false;
        this.loadWaitingPayment = false;
        this.loadWaitingReview = false;
        this.failureUrl = urls.failure;
        this.reloadReviewUrl = urls.reloadReview;
        this.reloadPaymentUrl = urls.reloadPayment;
        this.successUrl = urls.success;
        this.response = [];
    },
    /*
     * Redirect to failueUrl if ajax is false
     */
    ajaxFailure: function(){
        location.href = this.failureUrl;
    },
    /*
     * Process Respone of Onestepcheckout
     */
    processRespone: function(transport) {
        var response;
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
		
		if(response.review){
			//alert('Coming on top');
		}

        if (response.error){
            if (response.fields) {
                var fields = response.fields.split(',');
                for (var i=0;i<fields.length;i++) {
                    var field = null;
                    if (field == $(fields[i])) {
                        Validation.ajaxError(field, response.error);
                    }
                }
                return;
            } else {
		alert(Translator.translate(response.error));
                return;
	    }
	}

	if(response.coupon) {
	    if(response.coupon == 'remove') {
		$('coupon_code').value = '';
		$('coupon_code').removeAttribute('disabled');
		$('icon-coupon-add-remove').className = 'check';
		$('icon-coupon-add-remove').title = Translator.translate('Apply Coupon');
		review.couponCode = 0;
	    } else {
		$('coupon_code').setAttribute('disabled', 'disabled');
		$('icon-coupon-add-remove').className = 'remove';
		$('icon-coupon-add-remove').title = Translator.translate('Remove');
		review.couponCode = 1;
	    }
	}

	if(response.totalQty) {
	    $$('.links .top-link-cart')[0].title = 'My Cart (' + response.totalQty + ' items)';
	    $$('.links .top-link-cart')[0].innerHTML = 'My Cart (' + response.totalQty + ' items)';
	}

        this.response = response;
        if(response.shippingMethod) {
            this.updateShippingMethod();
        } else {
            if(response.payment) {
                this.updatePayment();
            } else {
                this.updateReview();
            }
        }
    },
    /*
     * Set show or hide for loader Shipping Method
     */
    setLoadWaitingShippingMethod: function(flag) {
        this.loadWaitingShippingMethod = flag;
        if(flag == true) {
            Element.show('onestepcheckout-shipping-method-ajax-loader');
            Element.hide('checkout-shipping-method-load');
        } else {
            Element.hide('onestepcheckout-shipping-method-ajax-loader');
            Element.show('checkout-shipping-method-load');
        }
    },
    /*
     * Set hide for loader Shipping Method
     */
    resetLoadWaitingShippingMethod: function() {
        this.setLoadWaitingShippingMethod(false);
    },
    /*
     * Update Shipping Method
     */
    updateShippingMethod: function() {
        $('checkout-shipping-method-load').update(this.response.shippingMethod);
        this.resetLoadWaitingShippingMethod();
        if($$('#checkout-shipping-method-load .no-display input').length != 0) {
            if($$('#checkout-shipping-method-load .no-display input')[0].checked == true) {
                shippingMethod.saveShippingMethod();
            }
        } else {
			var flagReloadPayment = false;
			$$('#checkout-shipping-method-load input').each(function(element){
				if(element.checked == true) {
					flagReloadPayment = true;
				}
			});
	    
            if(this.response.payment && flagReloadPayment == false) {
                this.reloadPayment();
            } else {
				shippingMethod.saveShippingMethod();
			}
        }
    },
    /*
     * Set show or hide for loader Payment Method
     */
    setLoadWaitingPayment: function(flag) {
        this.loadWaitingPayment = flag;
        if(flag == true) {
            Element.show('onestepcheckout-payment-ajax-loader');
            Element.hide('checkout-payment-method-load');
        } else {
            Element.hide('onestepcheckout-payment-ajax-loader');
            Element.show('checkout-payment-method-load');
        }
    },
    /*
     * Set hide for loader Payment Method
     */
    resetLoadWaitingPayment: function() {
        this.setLoadWaitingPayment(false);
    },
    /*
     * Update Payment Method
     */
    updatePayment: function() {
        $('checkout-payment-method-load').update(this.response.payment);
        this.resetLoadWaitingPayment();
        payment.switchMethod(payment.currentMethod);
        if($$('#checkout-payment-method-load .no-display input').length != 0) {
            if($$('#checkout-payment-method-load .no-display input')[0].checked == true) {
                payment.savePayment();
            }
        } else {
	    var flagReloadPayment = false;
	    $$('#checkout-payment-method-load input').each(function(element){
		if(element.checked == true) {
		    flagReloadPayment = true;
		}
	    });
	    if(flagReloadPayment == true) {
		payment.savePayment();
	    } else {
		this.reloadReview();
	    }
        }
    },
    /*
     * Set show or hide for loader Review
     */
    setLoadWaitingReview: function(flag) {
        this.loadWaitingReview == flag;
        if(flag == true) {
            Element.show('onestepcheckout-review-ajax-loader');  // ya i guess thi
            Element.hide('checkout-review-load');  // but basically it is trying to show an element which application cannot find... so if we comment these elements... then it will solve the issue right? or make those elements visible
        } else {
            Element.hide('onestepcheckout-review-ajax-loader');
            Element.show('checkout-review-load');
        }
    },
    /*
     * Set hide for loader Review// locked again!yes// put the elements and then check.. this might be because u r breaking on error
     */
	
	 
    resetLoadWaitingReview: function() {
        this.setLoadWaitingReview(false);
    },
    /*
     * Update Review
     */
    updateReview: function() {
        //$('checkout-review-load').update(this.response.review);
		if(typeof this.response.review != 'undefined' && this.response.review && this.response.review.length > 10){
			//alert('Am i even coming here'+this.response.review);
			$('checkout-review-div').update(this.response.review);
			//$$('.order-summary-container').update(this.response.review);
		}
		//$('checkout-review-table').update('from update review');
        this.resetLoadWaitingReview();
        if(this.response.success) {
            location.href = this.successUrl;
        }
    },
    /*
     * Reload review
     */
    reloadReview:function() {
        this.setLoadWaitingReview(true);
        var request = new Ajax.Request(
                    this.reloadReviewUrl,
                    {
                        method:'post',
                        onComplete: this.resetLoadWaitingReview,
                        onSuccess: this.processRespone.bind(this),
                        onFailure: this.ajaxFailure.bind(this)
                    }
                );
    },
    /*
     * Reload Payment
     */
    reloadPayment: function() {
        this.setLoadWaitingPayment(true);
        var request = new Ajax.Request(
                    this.reloadPaymentUrl,
                    {
                        method:'post',
                        onComplete: this.resetLoadWaitingPayment,
                        onSuccess: this.processRespone.bind(this),
                        onFailure: this.ajaxFailure.bind(this)
                    }
                );
    }
}

//login
var Login = Class.create();
Login.prototype = {
    initialize: function(loginUrl, width, height) {
        this.width = width;
        this.height = height;
        this.loginUrl = loginUrl;
        this.loadWaitingLogin = false;
        this.response = [];
        if(typeof(window.innerHeight) == 'number') {
            this.heightBody = Math.round(window.innerHeight);
            this.widthBody = Math.round(window.innerWidth);
        } else if(document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight )) {
            this.heightBody = Math.round(document.documentElement.clientHeight);
            this.widthBody = Math.round(document.documentElement.clientWidth);
        } else if (document.body && ( document.body.clientWidth || document.body.clientHeight )) {
			this.heightBody = Math.round(document.body.clientHeight);
			this.widthBody = Math.round(document.body.clientWidth);
		}
    },
    /*
     * Show tool tip Login
     */
    show: function() {
        $('tool-tip-login').setStyle({
            opacity: 0,
            visibility: 'visible'
        });
        new Effect.Opacity(
            'tool-tip-login',
            {
                duration:.9,
                from:0,
                to:0.8,
                afterFinish: this.setStyle()
            }
        );
        Element.show('tool-tip-login-form');
		$('login-email').focus();
    },
    /*
     * Hide tool tip Login
     */
    hide: function() {
        new Effect.Opacity(
            'tool-tip-login',
            {
                duration:.9,
                from:0.8,
                to:0,
		afterFinish: function() {
		    $('tool-tip-login').setStyle({
			opacity: 0,
			visibility: 'hidden'
		    })
		}
            }
        );
	Element.hide('tool-tip-login-form');
    },
    /*
    * Ajax request Login action
    */
    login: function() {
        var username = $('login-email').value;
        var password = $('login-password').value;
        this.setLoadWaitingLogin(true);
        var request = new Ajax.Request(
                    this.loginUrl,
                    {
                        parameters: { username: username, password: password },
                        method:'post',
                        onComplete: this.resetLoadWaitingLogin.bind(this),
                        onSuccess: this.processRespone.bind(this),
                        onFailure: onestepcheckout.ajaxFailure.bind(this)
                    }
                );
    },
    /*
     * Process response JSON
     */
    processRespone: function(transport) {
        var response;
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if(response.error) {
            $('onestepcheckout-error-message').update(response.error);
            this.resetLoadWaitingLogin();
        } else {
	    window.location.reload(true);
        }
    },
    /*
     * Set ajax load waiting
     */
    setLoadWaitingLogin: function(flag) {
        this.loadWaitingLogin = flag;
        if(flag) {
            Element.show('onstepcheckout-login');
            Element.hide('onestepcheckout-login-form');
        } else {
            Element.hide('onstepcheckout-login');
            Element.show('onestepcheckout-login-form');
        }
    },
    /*
     * Reset load waiting
     */
    resetLoadWaitingLogin: function() {
        this.setLoadWaitingLogin(false);
    },
    /*
     * Set style of login tool tip form
     */
    setStyle: function() {
        var top = Math.round((this.heightBody - this.height)/2);
		var left = Math.round((this.widthBody - this.width)/2);
        $('tool-tip-login-form').setStyle({
            width: this.width + 'px',
            height: this.height + 'px',
            left: left + 'px',
            top: top + 'px'
        })
    }
}

// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function(useBilling, saveCountryUrl, switchMethodUrl, addressUrl) {
        this.useBilling = useBilling;
        this.saveCountryUrl = saveCountryUrl;
        this.switchMethodUrl = switchMethodUrl;
        this.addressUrl = addressUrl;
    },
    /*
     * Enable Shipping Address
     */
    enalbleShippingAddress: function () {
        this.setStepNumber();
        if($('billing:use_for_shipping_yes').checked == true) {
            Element.show('shipping-address-form');
            this.useBilling = false;
            if($('shipping-address-select')) {
				if($('shipping-address-select').value) {
					shipping.setAddress($('shipping-address-select').value);
				} else {
					shipping.saveCountry();
				}
            } else {
                shipping.saveCountry();
            }
        } else {
            Element.hide('shipping-address-form');
            this.useBilling = true;
            this.saveCountry();
        }
    },
    /*
     * Save Postcode & countryId to checkout/cart
     */
    saveCountry: function() {
        var countryId = $('billing:country_id').value;
        var postcode = '';
	if($('billing:postcode') != undefined) {
	    postcode = $('billing:postcode').value;
	}
	
	if(countryId == '') {
	    alert(Translator.translate('Whooop.... You didn\'t select your country. Please select your country'));
	    $('billing:country_id').value = this.currentCountry;
	    return;
	}
	
        if(this.useBilling == 1) {
	    if($('onestepcheckout-shipping-method-ajax-loader')) {
		onestepcheckout.setLoadWaitingShippingMethod(true);
	    } else {
		onestepcheckout.setLoadWaitingPayment(true);
	    }
	    if($('billing:region_id') != undefined) {
		if($('billing:region_id').style.display == 'none') {
		    var request = new Ajax.Request(
			this.saveCountryUrl,
			{
			    parameters: { country_id: countryId, postcode: postcode, region : $('billing:region').value, use_for: 'billing' },
			    method:'post',
			    onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
			    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
			    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		    );
		} else {
		    var request = new Ajax.Request(
			this.saveCountryUrl,
			{
			    parameters: { country_id: countryId, postcode: postcode, region_id : $('billing:region_id').value, use_for: 'billing' },
			    method:'post',
			    onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
			    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
			    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		    );
		}
	    } else {
		var request = new Ajax.Request(
			this.saveCountryUrl,
			{
			    parameters: { country_id: countryId, postcode: postcode, use_for: 'billing' },
			    method:'post',
			    onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
			    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
			    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		    );
	    }
        } else {
            onestepcheckout.setLoadWaitingPayment(true);
	    if($('billing:region_id') != undefined) {
		if($('billing:region_id').style.display == 'none') {
		    var request = new Ajax.Request(
			    this.saveCountryUrl,
			    {
				    parameters: { country_id: countryId, postcode: postcode, region : $('billing:region').value, use_for: 'shipping' },
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
		    );
		} else {
		    var request = new Ajax.Request(
			    this.saveCountryUrl,
			    {
				    parameters: { country_id: countryId, postcode: postcode, region_id : $('billing:region_id').value, use_for: 'shipping' },
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
		    );
		}
	    } else {
		var request = new Ajax.Request(
			    this.saveCountryUrl,
			    {
				    parameters: { country_id: countryId, postcode: postcode, use_for: 'shipping' },
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
			);
	    }
        }
    },
    /*
     * Show password and confirm password
     */
    register: function() {
        var param = '';
        if($('billing:register').checked == true && $('billing:register').value == 1) {
            Element.show('register-customer-password');
            param = 'register';
        } else {
            Element.hide('register-customer-password');
            param = 'guest';
        }

        if(param) {
            var request = new Ajax.Request(
                        this.switchMethodUrl,
                        {
                            parameters: { method: param },
                            method:'post',
                            onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
                        }
                    );
        }
    },
    /*
     * Ajax save addressId to order
     */
    setAddress: function(addressId){
		//alert(addressId);return false;
		if (addressId) {
		request = new Ajax.Request(
                this.addressUrl+addressId,
                {
                    method:'get',
                    onSuccess: this.fillForm.bindAsEventListener(this),
                    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
                }
            );
        }
    },
    /*
     * Show billing address form
     */
    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
    },
    /*
     * Reset value of select address
     */
    resetSelectedAddress: function(){
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },
    /*
     * Replace value of select address to form
     */
    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        } else {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(review.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                if(elementValues[fieldName] != undefined && elementValues[fieldName]) {
                    arrElements[elemIndex].value = elementValues[fieldName];
                }
            }
        }
        this.saveCountry();
    },
    /*
     * Set step of onestepcheckout
     */
    setStepNumber: function() {
        steps = $$('#step-number');
        for(var i=0; i<steps.length;i++) {
            if(steps[i].className != 'step-1' && steps[i].className != 'step-review') {
                if($('billing:use_for_shipping_yes').checked == true) {
                    if(steps[i].className != 'shipping') {
                        steps[i].removeClassName('step-'+i);
                    }
                    steps[i].addClassName('step-'+(i+1));
                } else {
                    if(steps[i].className != 'step-2') {
                        steps[i].addClassName('step-'+i);
                    }
                    steps[i].removeClassName('step-'+(i+1));
                }
            }
        }
    }
}

// shipping
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(saveCountryUrl, addressUrl){
        this.saveCountryUrl = saveCountryUrl;
        this.addressUrl = addressUrl;
    },
     /*
     * Save Postcode & countryId to checkout/cart
     */
    saveCountry: function() {
        if(billing.useBilling == 0) {
            var countryId = $('shipping:country_id').value;
            var postcode = $('shipping:postcode').value;
	    
	    if(countryId == '') {
		alert(Translator.translate('Whooop.... You didn\'t select your country. Please select your country'));
		$('shipping:country_id').value = this.currentCountry;
		return;
	    }
	    
	    onestepcheckout.setLoadWaitingShippingMethod(true);
	    if($('shipping:region_id') != undefined) {
		if($('shipping:region_id').style.display == 'none') {
		    var request = new Ajax.Request(
			    this.saveCountryUrl,
			    {
				    parameters: { country_id: countryId, postcode: postcode, region : $('shipping:region').value },
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
		    );
		} else {
		    var request = new Ajax.Request(
			    this.saveCountryUrl,
			    {
				    parameters: { country_id: countryId, postcode: postcode, region_id: $('shipping:region_id').value },
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
		    );
		}
	    } else {
		var request = new Ajax.Request(
			this.saveCountryUrl,
			{
				parameters: { country_id: countryId, postcode: postcode },
				method:'post',
				onComplete: onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),
				onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		);
	    }
        }
    },
    /*
     * Set addressId to Order
     */
    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                    {
                        method:'get',
                        onSuccess: this.fillForm.bindAsEventListener(this),
                        onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
                    }
            );
        }
    },
    /*
    * Show shipping address form
    */
    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },
    /*
     * Reset select address value
     */
    resetSelectedAddress: function(){
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },
    /*
    * Set billing address to shipping address
    */
    setSameAsBilling: function(flag) {
        $('shipping:same_as_billing').checked = flag;
        if (flag) {
            this.syncWithBilling();
        }
    },
    /*
     * Sync value with billing address
     */
    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(review.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },
    /*
     * Replace select address to shipping form
     */
    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        } else {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(review.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                if(elementValues[fieldName] != undefined && elementValues[fieldName]) {
                    arrElements[elemIndex].value = elementValues[fieldName];
                }
            }
        }
        this.saveCountry();
    },
    /*
     * Set region value
     */
    setRegionValue: function(){
        $('shipping:region').value = $('billing:region').value;
    }
}

// shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function(saveUrl, isReloadPayment){
        this.saveUrl = saveUrl;
		this.isReloadPayment = isReloadPayment;
    },
    /*
     * Ajax save shipping method
     */
    saveShippingMethod:function() {
        var methods = document.getElementsByName('shipping_method');
        var value = 'udropship_Per Item Shipping'; // Setting Default Shipping method
        for(var i=0;i<methods.length;i++) {
            if(methods[i].checked) {
                value = methods[i].value ? methods[i].value: 'udropship_Per Item Shipping';
            }
        }

        if(value != '') {
	    if(this.isReloadPayment == 1) {
		onestepcheckout.setLoadWaitingPayment(true);
	    }
            var request = new Ajax.Request(
                    this.saveUrl,
                    {
                        parameters: { shipping_method: value},
                        method:'post',
                        onComplete: onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),
                        onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
                        onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
                    }
                );
        }
    }
}

// payment
var Payment = Class.create();
Payment.prototype = {
    beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    initialize: function(saveUrl, isUseAjax){
        this.saveUrl = saveUrl;
	this.isUseAjax = isUseAjax;
    },
    /*
     * Ajax save payment method
     */
    savePayment: function() {
        var methods = document.getElementsByName('payment[method]');
        value = '';
        for(var i=0;i<methods.length;i++) {
            if(methods[i].checked) {
                value = methods[i].value;
            }
        }
	if(this.isUseAjax) {
	    if(value != '') {
		    onestepcheckout.setLoadWaitingReview(true);
		var request = new Ajax.Request(
			this.saveUrl,
			{
			    parameters: { method: value},
			    method:'post',
			    onComplete: onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout),
			    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
			    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		    );
	    }
	}
    },
    /*
     * Switch method
     */
    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.select('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.select('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
        }
        this.currentMethod = method;
    },
    /*
     * Init tool tip what is this
     */
    initWhatIsCvvListeners: function(){
        $$('.cvv-what-is-this').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });
    }
}

//review
var Review = Class.create();
Review.prototype = {
    initialize: function(form, saveCouponUrl, couponCode, updateQtyUrl, saveUrl, agreementsForm){
        this.form = form;
	this.saveCouponUrl = saveCouponUrl;
	this.couponCode = couponCode;
	this.updateQtyUrl = updateQtyUrl;
        this.saveUrl = saveUrl;
        this.agreementsForm = agreementsForm;
        this.onestepcheckourForm = new VarienForm(this.form);
    },
    /*
     * Ajax save coupon
     */
    coupon: function() {
		var coupon_code = $('coupon_code').value;
		if($('coupon_code').value == '') {
		    alert(Translator.translate('Please insert coupon code into text box.'));
		    return;
		}
		if(this.couponCode == 1) {
		    onestepcheckout.setLoadWaitingReview(true);
		    var request = new Ajax.Request(
			    this.saveCouponUrl,
			    {
				    parameters: { coupon_code: coupon_code,remove: 1},
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
			);
		} else {
		    onestepcheckout.setLoadWaitingReview(true);
		    var request = new Ajax.Request(
			    this.saveCouponUrl,
			    {
				    parameters: { coupon_code: coupon_code},
				    method:'post',
				    onComplete: onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout),
				    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
				    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			    }
		    );
		}
    },
    /*
     * Update Qty
     */
    updateQty: function() {
	    var params = new Hash();
    $$('#checkout-review-table-wrapper .qty').each(function(element){
	params.set(element.name ,element.value);
    });

	    onestepcheckout.setLoadWaitingReview(true);
	    var request = new Ajax.Request(
			this.updateQtyUrl, {
			    method:'post',
			    parameters:params.toObject(),
							    onComplete: function(){
								    if(billing.useBilling == 1) {
									    billing.saveCountry();
								    } else {
									    shipping.saveCountry();
								    }
							    }.bind(this),
							    onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
							    onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
			}
		);
    },
    /*
     * Ajax save order
     */
    save: function() {
       // var validator = new Validation(this.form);
       // if(validator.validate()) {
            onestepcheckout.setLoadWaitingReview(true);
            var params = Form.serialize(this.form);
            if (this.agreementsForm) {
                params += '&'+Form.serialize(this.agreementsForm);
            }
            params.save = true;
            var request = new Ajax.Request(
                            this.saveUrl, {
                                method:'post',
                                parameters:params,
                                onComplete: function(){
										$$('.btn-checkout').invoke('show');
										$$('.ajaxloadermtosc').invoke('hide');
										onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout)
									},
                                onSuccess: onestepcheckout.processRespone.bind(onestepcheckout),
                                onFailure: onestepcheckout.ajaxFailure.bind(onestepcheckout)
                            }
                    );
        //}
    }
}

//Agreement
var Agreements = Class.create();
Agreements.prototype = {
    initialize: function(resizeSpeed){
	this.duration = ((11-resizeSpeed)*0.15);
    },

    scrollShow: function(id) {
	new Effect.toggle(id,
			'blind',
			{
			    duration : this.duration
			}
	    );
    }
}

// New functions by Harpreet Singh

	//for payment/methods.phtml
	/*

						function reloadReview3(){
							jQuery('body').addClass('opacity');	
							jQuery.post('<?php echo $this->getUrl('mtonestepcheckout/index/reloadReviewh') ?>', '', function(data){
								//alert(data.review)
								jQuery('.order-summary-container').html(data.new);
								jQuery('body').removeClass('opacity');	
							}, 'json')
						}
						
						//for onestepcheckout.phtml
							function reloadReview-onestep(){
								$j.post('<?php echo $this->getUrl('mtonestepcheckout/index/reloadReviewh') ?>', '', function(data){
									//alert(data.review)
									$j('.order-summary-container').html(data.new);
								}, 'json')
							}

	*/

	// for sidebar/methods.phtml
		function deleteProduct(productId, obj, url1, url2){
	
		//var x = confirm('<?php echo $this->__('Are you sure you would like to remove this item from the shopping cart?') ?>');
		//if(x == true){
			jQuery('body').addClass('opacity');
			jQuery.post(url1, {id:productId}, function(data){
				if(data == 'success'){
					jQuery(obj).parent().parent().remove();
					reloadReview(url2);
					//jQuery('body').removeClass('opacity');
				} else{
					alert('Error: Please try again!');
					jQuery('body').removeClass('opacity');
				}
			});
		//} else{
		//	return false;
	//	}
	}
	
	function reloadReview(reviewurl){
	try
		{	
			$j('body').addClass('opacity');
			$j.post(reviewurl, '', function(data){
				//alert(data.newreview);
				if(data){
					if(data.newreview.length > 10){
						$j('#checkout-review-div').html(data.newreview);
						//$j('.order-summary-container').html(data.newreview);
						$j('body').removeClass('opacity');
					}
				}else{
					location.reload();
				}
			},'json');
		}
		catch(err)
		{
			location.reload();
		}
	}
	
	
		function checkpostalcode(postalcheckurl){
			var isChecked = $j('input.shipping-check').attr('checked')?true:false;
			var postcode;
			var country;
			if(isChecked){
				postcode = $j('input[name="shipping[postcode]"]').val();
				country = $j('select[name="shipping[country_id]"] option:selected').val();
			} else{
				postcode = $j('input[name="billing[postcode]"]').val();
				country = $j('select[name="billing[country_id]"] option:selected').val();
			}
			$j.post(postalcheckurl, {postcode:postcode}, function(data){
				if(data.service == 1){
					$j('.btn-checkout').removeClass('displaynone');
					if(data.cod == 0){
						hideCOD();
						//alert('COD is not available for this address!')
					} else if(data.cod == 1){
						if(country == 'IN'){
							showCOD();
						} else{
							hideCOD();
						}
					}
				} else if(data.service == 0){
					$j('.btn-checkout').addClass('displaynone');
					alert('Error: Postal Code is not serviceable.');
				} else if(data.service == 2){
					//$j('.btn-checkout').addClass('displaynone');
					//alert('Please enter a valid Postal code.');
				}
				/* if(data.service == 0){
					alert('Error: Postal Code is not serviceable.');
				} */
			}, 'json');
		}
		
		function showCOD(){
				$j('.meth-cod-dd').show();
				$j('.meth-cod-dd').removeClass('displaynone');
				$j('.meth-cod-dd').css('display', 'block!important');
				$j('#payment_form_cashondelivery').show();
		}
		function hideCOD(){
				$j('.meth-cod-dd').hide();
				$j('.meth-cod-dd').addClass('displaynone');
				$j('.meth-cod-dd').css('display', 'none!important');
				$j('#payment_form_cashondelivery').hide();
		}