jQuery(document).ready(

	function() {
		
		process_shop_search();
		
	}	
);

function process_shop_search() {
	
	jQuery('#coupon-search').html('');
	document.getElementById('fancybox-overlay').style.display = 'block';
	window.scrollTo(0, 0);
	jQuery.ajax({
		url : "http://www.craftsvilla.com/shopcoupon/coupon/coupongetvalues",
		cache : false,
		success : function(response_shop) {
		    var myObject_shop = eval('(' + response_shop + ')');
			var html_value = myObject_shop.cache;
			jQuery('#coupon-search').html(html_value);
			document.getElementById('fancybox-overlay').style.display = 'none';
			window.scrollTo(0, 0);
			return false;
		}	
	});	
}




