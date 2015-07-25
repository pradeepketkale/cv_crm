function send_values(order, dir, page, redirect_url)
{
	/*alert("Order:"+order);
	alert("Dir:"+dir);
	alert("Page:"+page);
	alert("Redirect URL:"+redirect_url);*/
	//var collection = document.getElementById(ol_id).getElementsByTagName("input");
	var cat_location_value = '';
	var cat_color_value = '';
	var url_build_val = '';
	/*for (var x=0; x<collection.length; x++) {
		if (collection[x].type.toLowerCase() == "checkbox") {
			if(collection[x].checked == true)
			{
				if(cat_location_value != '')
				{
					cat_location_value += ","+collection[x].value;
				}
				else {
					cat_location_value += collection[x].value;
				}
			}	
		}
	}*/
	
	//cat_location_value = get_checkbox_values('category_location');
	
	var min_price_val = document.getElementById('minPrice').value;
	var max_price_val = document.getElementById('maxPrice').value;
	
	cat_color_value = get_checkbox_values('category_color');
	cat_location_value = get_checkbox_values('category_location');
	
	if(!min_price_val && !max_price_val)
	{
		cat_price_value = get_checkbox_values('category_price');
	}
	else if(min_price_val != '*' && max_price_val != '*')
	{
		cat_price_value = get_checkbox_values('category_price');
	}
	else
	{
		cat_price_value = '';
	}	
	
	
	if(dir != '')
	{
		url_build_val = 'dir='+dir+'&';
	}
	
	if(order != '')
	{
		url_build_val += 'order='+order;
	}	
	
	if(page != '')
	{
		if(url_build_val != '')
		{
			url_build_val += '&';
		}	
		url_build_val += 'p='+page;
	}	
	 
	if(cat_color_value != '')
	{
		if(url_build_val != '')
		{
			url_build_val += "&";
		}	
		url_build_val += "color="+cat_color_value;
	}
	
	if(cat_location_value != '')
	{
		if(url_build_val != '')
		{
			url_build_val += "&";
		}	
		url_build_val += "location="+cat_location_value;
	}
	
	if(cat_price_value != '')
	{
		if(url_build_val != '')
		{
			url_build_val += "&";
		}	
		url_build_val += "price="+cat_price_value;
	}
	
	if(min_price_val && max_price_val)
	{
		if(url_build_val != '')
		{
			url_build_val += "&";
		}
		url_build_val += "min="+min_price_val+"&max="+max_price_val;
	}	

	if(url_build_val != '')
	{
		url_build_val = "?"+url_build_val;
	}	
	else {
		url_build_val = redirect_url;
	}
	document.location.replace(url_build_val);
}

function delete_element_redirect(remove_val, dir, order, page, redirect_url, remove_value)
{
	/*alert("REMOVE:"+remove_val);
	alert("DIR:"+dir);
	alert("ORDER:"+order);
	alert("PAGE:"+page);
	alert("URL:"+redirect_url);
	alert("REMOVED VALUE:"+remove_value);*/
	get_checkbox_remove_value(remove_val,remove_value);
	send_values(order, dir, page, redirect_url);
}

function get_checkbox_values(ol_id)
{
	var collection = document.getElementById(ol_id).getElementsByTagName("input");
	var return_str = '';
	for (var x=0; x<collection.length; x++) {
		if (collection[x].type.toLowerCase() == "checkbox" && collection[x].checked == true) {
			if(return_str != '')
			{
				return_str += ","+collection[x].value;
			}
			else
			{
				return_str += collection[x].value;
			}	
			
		}
	}
	if(return_str != '')
	{
		return return_str;
	}	
	else {
		return 0;
	}
}

function get_checkbox_remove_value(ol_id, value_remove)
{
	var collection = document.getElementById(ol_id).getElementsByTagName("input");
	var return_str = '';
	for (var x=0; x<collection.length; x++) {
		if (collection[x].type.toLowerCase() == "checkbox") {
			if(value_remove == collection[x].value)
			{
				collection[x].checked = false;
			}	
		}
	}
}

function slider_disp(first_val, last_val) {
	var min_val = parseInt(first_val);
	var max_val = parseInt(parseInt(last_val)-parseInt(first_val));
	
	jQuery('.slider4').Slider(
	{
		accept : '.indicator',
		restricted: true,
		opacity: 0.8,
		onSlide : function(procx, procy, x, y) {
			price = parseInt((parseInt(parseInt(min_val) + parseInt((parseInt(max_val)-parseInt(min_val))) * procx/100))/100) * 100;
			document.getElementById(this.SliderIteration == 0 ? 'minPrice' : 'maxPrice').value = '';
			document.getElementById(this.SliderIteration == 0 ? 'minPrice' : 'maxPrice').value = price;
			if(this.SliderIteration == 0)
			{
				document.getElementById('price_min').value = price;
			}
			else {
				document.getElementById('price_max').value = price;
			}
		},
		values: [
		        [0,0],
				[55540,0]
		]
	});
}

function submit_price_range(order, dir, page, redirect_url)
{
	var min_price_val = document.getElementById('minPrice').value;
	var max_price_val = document.getElementById('maxPrice').value;
	
	if(min_price_val == '')
	{
		document.getElementById('minPrice').value = '*';
	}	
	
	if(max_price_val == '')
	{
		document.getElementById('maxPrice').value = '*';
	}
	
	send_values(order, dir, page, '', redirect_url);
}

( function( $ ) { 
    $.dequeue = function( a , b ){ 
            return $(a).dequeue(b); 
    }; 
})( jQuery ); 