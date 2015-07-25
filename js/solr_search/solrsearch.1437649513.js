jQuery(document).ready(
	function() {
	//alert(document.getElementById('search').value);
		process_search(document.getElementById('search').value, '', '0', '', '', '', '');
		//return false;
	}	
);

function process_search(search_value, search_cat, start_value, min_values, max_values, order_by, order) {
	window.scrollTo(0, 0);
	document.getElementById('fancybox-overlay').style.display = 'block';
	jQuery('#products_display').html('');
	jQuery(".pages").html('');
	search_cat = search_cat.replace(/"/gi, "");
	search_value = search_value.replace(/"/gi, "");
	
	var search_color = new Array();
	var search_country = new Array();
	var search_location = new Array();
	var search_price = new Array();
	
	jQuery('#country_div').fadeOut();
	jQuery('#cat_div').fadeOut();
	jQuery('#price_div').fadeOut();
	jQuery('#color_div').fadeOut();
	jQuery('#location_div').fadeOut();
	
	if (document.getElementById('color_ol_id') != null) {
		search_color = get_param_value('color_ol_id');
		document.getElementById('color_hidden').value = search_color;
	}

	if (document.getElementById('location_ol_id') != null) {
		search_location = get_param_value('location_ol_id');
		document.getElementById('location_hidden').value = search_location;
	}
		if (document.getElementById('country_ol_id') != null) {
		search_country = get_param_value('country_ol_id');
		document.getElementById('country_hidden').value = search_country;
	}
	if (document.getElementById('price_ol_id') != null) {
		search_price = get_param_value('price_ol_id');
		document.getElementById('price_hidden').value = search_price;
	}
	if (search_cat == '""') {
		search_cat = "";
	} else if (search_cat != '') {
		document.getElementById('category_hidden').value = search_cat;
	}
	if (!order_by) {
		order_by = '';
		document.getElementById('sort_orderby').value = 'entity_id';
	} else {
		document.getElementById('sort_orderby').value = order_by;
	}
	if (!order) {
		order = '';
		document.getElementById('sort_order').value = 'desc';
	} else {
document.getElementById('sort_order').value = order;
}
jQuery.ajax({
		url : "/solariumsearch/solrgetvalues.php",
		type : "POST",
	
		data : {
			search_val : search_value,
			search_category : search_cat,
			search_color_fecet : "'" + search_color + "'",
			search_location_fecet : "'" + search_location + "'",
			search_price_fecet : "'" + search_price + "'",
			search_country_fecet : "'" + search_country + "'",
			search_min_price : "'" + min_values + "'",
			search_max_price : "'" + max_values + "'",
			s : start_value,
			orderby : order_by,
			order : order
		},
		cache : false,
		success : function(response) {
		var myObject = eval('(' + response + ')');
		var start_value = parseInt(myObject.response.start);
		var recotd_get = 60;
		var mynew_object = myObject.response;
		var total_pages = Math.ceil(mynew_object.numFound/ recotd_get);
		var total_records = mynew_object.numFound;
		
		var total_records_disp = '';
    	if(total_records <= recotd_get)
        {
    		total_records_disp = '<strong>'+total_records+' Item(s)</strong>';
    	}
        else {
        	total_records_disp += '<div class="amount">Items <span>'+(start_value+1)+'</span> to <span>';
        	if((start_value+recotd_get)>total_records){ 
        		total_records_disp += total_records; 
        	} else{ 
        		total_records_disp += (start_value+recotd_get);
        	} 
        	total_records_disp += '</span> of <span>'+total_records+'</span> total </div>';
     	}

		var facet_object = myObject.facet_counts;
		var facet_object_fields = facet_object.facet_fields;

		if (facet_object_fields.price.length > 0) {

			var io1 = 1;
			var new_arr_val1 = {};
			for ( var il1 = 0; il1 < facet_object_fields.price.length; il1++) {
				io1++;

				if (io1 > 2) {
					new_arr_val1[parseInt(facet_object_fields.price[il1 - 1])] = parseInt(facet_object_fields.price[il1]);
					io1 = 1;
				}
			}

			var new_arr_val2 = ksort(new_arr_val1);
			var range1 = 0;
			var key_arr1 = new Array();
			key_arr1 = Object.keys(new_arr_val2);
			var first_val1 = key_arr1[0];
			var last_val1 = key_arr1[key_arr1.length - 1];

			if (!document.getElementById('initialise_price_min').value && !document.getElementById('initialise_price_max').value) {
				document.getElementById('initialise_price_min').value = first_val1;
				document.getElementById('initialise_price_max').value = last_val1;
			}
			document.getElementById('price_min').value = first_val1;
			document.getElementById('price_max').value = last_val1;
		}

		if (facet_object_fields.price.length > 0) {
			var getPriceFacetrange = get_facet_range_value(facet_object_fields.price);
		} else {
			var getPriceFacetrange = '';
		}

		/*************** pagination ************************************/

		if (start_value)
			var page = parseInt(start_value);
		else
			var page = 0;
		var tpages = total_pages;
		var adjacents = 4;

		if (page <= 0)
			page = 1;
		var out = "<ol>";
	
		// previous
        if(page==1) {
            out+= "<li></li>";
        }
        else if(((page/recotd_get)+1)==2) {
            out+= "<li><a class=\"previous i-previous\" href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '0', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Previous\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_left.png\"></a></li>";
        }
        else {
            out+= "<li><a class=\"previous i-previous\" href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', "+(page-recotd_get)+", '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_left.png\"></a></li>";
        }
           
        // first
        if(((page/recotd_get)+1)>(adjacents+1)) {
            out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '0', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\">1</a></li>";
        }
           
        // interval
        if(((page/recotd_get)+1)>(adjacents+2)) {
            out+= "<li>...</li>";
        }
           
        // pages
        var pmin = (((page/recotd_get)+1)>adjacents) ? (((page/recotd_get)+1)-adjacents) : 1;
        var pmax = (((page/recotd_get)+1)<(tpages-adjacents)) ? (((page/recotd_get)+1)+adjacents) : tpages;
        for(var i=pmin; i<=pmax; i++) {
            if(i==((page/recotd_get)+1)) {
            	out+= "<li class=\"current\">" + i + "</li>";
            }
            else if(i==1) {
            	out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '0', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\">" + i + "</a></li>";
            }
            else {
            	out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '"+((i*recotd_get)-recotd_get)+"', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\">" + i + "</a></li>";
            }
        }

        // interval
        if(((page/recotd_get)+1)<(tpages-adjacents-1)) {
        	out+= "<li>...</li>";
        }
           
        // last
        if(((page/recotd_get)+1)<(tpages-adjacents)) {
        	out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '"+((tpages*recotd_get)-recotd_get)+"', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\">" + tpages + "</a></li>";
        }
           
        // next
        if(((page/recotd_get)+1)<tpages) {
            if(page == 1)
            {
            	out+= "<li><a class=\"next i-next\" href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '"+(recotd_get)+"', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_right.png\"></a></li>";
            }
            else
            {
            	out+= "<li><a class=\"next i-next\" href=\"javascript:void(0);\" onClick=\"javascript: process_search('"+document.getElementById('search').value+"', '"+search_cat+"', '"+(page+recotd_get)+"', '"+min_values+"', '"+max_values+"', '"+order_by+"', '"+order+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_right.png\"></a></li>";
            }
        }
        else {
            out+= "<li><li>";
        }
           
        out+= "</ol>";
           
        jQuery(".pages").html(out);

		/*************** pagination ************************************/

		/************************** Shop By Div ***********************/
		var shop_by_div_value = '';

		if (search_cat != '' || search_color != '' || search_country != '' || search_location != '' || search_price != '' || min_values != '' || max_values != '') {
			if (min_values == '') {
				min_values = 0;
			}
			if (max_values == '') {
				max_values = 0;
			}
			shop_by_div_value = '<div class="currently"><p class="block-subtitle">Currently Shopping by:</p><ol>';
			if (search_cat != '' && search_cat != '""') {
				shop_by_div_value += '<li><a href="javascript: process_search(\''+ document.getElementById('search').value+ '\', \'\', \'0\', '+ min_values+ ', '+ max_values+ ');" title="Remove This Item" class="btn-remove">Remove This Item</a><span class="label">Category:</span>'+ search_cat + '</li>';
			}

			if (search_color != '') {
				if (is_array(search_color)) {
					for ( var kk = 0; kk < search_color.length; kk++) {
						shop_by_div_value += '<li><a href="javascript: dell_element_search(\'color_ol_id\', \''+ search_color[kk]+ '\', \''+ search_cat+ '\', '+ min_values+ ', '+ max_values+ ')" class="btn-remove">Remove This Item</a><span class="label">Color:'+ search_color[kk]+ '</span> </li>';
					}
				}
			}

			if (search_location != '') {
				if (is_array(search_location)) {
					for ( var ll = 0; ll < search_location.length; ll++) {
						shop_by_div_value += '<li><a href="javascript: dell_element_search(\'location_ol_id\', \''+ search_location[ll]+ '\', \''+ search_cat+ '\', '+ min_values+ ', '+ max_values+ ');" class="btn-remove">Remove This Item</a><span class="label">Location:'+ search_location[ll]+ '</span> </li>';
					}
				}
			}
			if (search_country != '') {
				if (is_array(search_country)) {
					for ( var jj = 0; jj < search_country.length; jj++) {
						shop_by_div_value += '<li><a href="javascript: dell_element_search(\'country_ol_id\', \''+ search_country[jj]+ '\', \''+ search_cat+ '\', '+ min_values+ ', '+ max_values+ ');" class="btn-remove">Remove This Item</a><span class="label">Location:'+ search_country[jj]+ '</span> </li>';
					}
				}
			}

			if (search_price != '') {
				if (is_array(search_price)) {
					for ( var mm = 0; mm < search_price.length; mm++) {
						shop_by_div_value += '<li><a href="javascript: dell_element_search(\'price_ol_id\', \''+ search_price[mm]+ '\', \''+ search_cat+ '\', '+ min_values+ ', '+ max_values+ ');" class="btn-remove">Remove This Item</a><span class="label">Price:'+ search_price[mm]+ '</span> </li>';
					}
				}
			} else if (min_values != '' && max_values != '') {
				shop_by_div_value += '<li><a href="javascript: dell_element_search( \'del_price\', \''+search_price+'\', \''+ search_cat + '\', '+ min_values+ ', '+ max_values+ ');" class="btn-remove">Remove This Item</a><span class="label">Price:'+ min_values+ ' - '+ max_values+'</span> </li>';
			}
			var searchInput = document.getElementById("search").value;
			shop_by_div_value += '</ol><div class="actions"><a href="javascript: window.location.replace(\'searchresults?searchby=product&q='+searchInput+'\');">Clear All</a></div>';
		}
		jQuery('#shop_by_div').html(shop_by_div_value);

		/************************** Shop By Div ***********************/

		//jQuery('#search_title_div').html('<h1>Search results for \''+ document.getElementById('search').value+ '\'</h1>');
		jQuery('#total_items').html(total_records_disp);
		jQuery('#products_display').html(CreateTableView(mynew_object.docs));
		if(mynew_object.numFound == 0)
		{
			document.getElementById('fancybox-overlay').style.display = 'none';
		}	
		
		jQuery('#cat_div').html(CreateCategoryTableView(facet_object_fields.category_name,document.getElementById('search').value,'category_name_value',search_color, search_location, search_country,first_val1, last_val1));
		jQuery('#color_div').html(CreateCategoryTableView(facet_object_fields.color,document.getElementById('search').value,search_cat, 'color_value', search_location,search_color,search_country, first_val1, last_val1));
		jQuery('#country_div').html(CreateCategoryTableView(facet_object_fields.country,document.getElementById('search').value,search_cat, 'color_value', search_location,search_country, first_val1, last_val1));
		jQuery('#location_div').html(CreateCategoryTableView(facet_object_fields.location, document.getElementById('search').value,search_cat, search_color, 'location_value',search_location, first_val1, last_val1));
		jQuery('#price_div').html(CreatePriceTableView(getPriceFacetrange, document.getElementById('search').value,search_cat, search_price, first_val1,last_val1));
		if (search_price) {
			slider_disp(document.getElementById('initialise_price_min').value,document.getElementById('initialise_price_max').value);
		}
		if (first_val1 && last_val1) {
			document.getElementById('minPrice').value = first_val1;
			document.getElementById('maxPrice').value = last_val1;
		}
		window.scrollTo(0, 0);
		document.getElementById('fancybox-overlay').style.display = 'none';
		return false;
	}
	});
}

function get_param_value(aId) {
	var collection = document.getElementById(aId).getElementsByTagName('INPUT');
	var return_array_val = new Array();
	for ( var x = 0; x < collection.length; x++) {
		if (collection[x].type.toUpperCase() == 'CHECKBOX' && collection[x].checked)
			return_array_val.push(collection[x].value);
	}
	return return_array_val;
}

function dell_element_search(dell_ele_name, dell_ele_value, cat_value, min_value, max_value) {
	if (dell_ele_name == "All" && dell_ele_value == "All") {
		checkByParent('color_ol_id', false);
		checkByParent('location_ol_id', false);
		checkByParent('price_ol_id', false);
		checkByParent('country_ol_id', false);
		document.getElementById('category_hidden').value = '';
		document.getElementById('minPrice').value = '';
		document.getElementById('maxPrice').value = '';
		document.getElementById('price_min').value = '';
		document.getElementById('price_max').value = '';
		process_search('"' + document.getElementById('search').value + '"', '','0', '', '', '', '');
	} else if (dell_ele_name == "del_price") {
		document.getElementById('minPrice').value = '';
		document.getElementById('maxPrice').value = '';
		document.getElementById('price_min').value = '';
		document.getElementById('price_max').value = '';
		process_search('"' + document.getElementById('search').value + '"', '"'+ cat_value + '"', '0', '', '', '', '');
	} else {
		var collection = document.getElementById(dell_ele_name).getElementsByTagName("input");
		for ( var a = 0; a < collection.length; a++) {
			if (collection[a].type.toLowerCase() == "checkbox") {
				if (collection[a].value == dell_ele_value)
					collection[a].checked = false;
			}
		}
		process_search('"' + document.getElementById('search').value + '"', '"'+ cat_value + '"', '0',document.getElementById('price_min').value, document.getElementById('price_max').value, '', '');
	}
}

function is_array(input) {
	return typeof (input) == 'object' && (input instanceof Array);
}

function checkByParent(aId, aChecked) {
	var collection = document.getElementById(aId).getElementsByTagName("input");
	for ( var x = 0; x < collection.length; x++) {
		if (collection[x].type.toLowerCase() == "checkbox") {
			collection[x].checked = aChecked;
		}
	}
}

function get_facet_range_value(priceRange_Values) {
	if (!priceRange_Values.length)
		return false;
	var io = 1;
	var arr = {};
	for ( var il = 0; il < priceRange_Values.length; il++) {

		io++;

		if (io > 2) {
			arr[parseInt(priceRange_Values[il - 1])] = parseInt(priceRange_Values[il]);
			io = 1;
		}
	}
	var new_arr_val = ksort(arr);

	var price_range_val = {};
	price_range_val['0-200'] = 0;
	price_range_val['201-400'] = 0;
	price_range_val['401-600'] = 0;
	price_range_val['601-800'] = 0;
	price_range_val['801-1000'] = 0;
	price_range_val['1001-1500'] = 0;
	price_range_val['1501-2000'] = 0;
	price_range_val['2001-2500'] = 0;
	price_range_val['2501-3000'] = 0;
	price_range_val['3001-3500'] = 0;
	price_range_val['3501-4000'] = 0;
	price_range_val['4001-4500'] = 0;
	price_range_val['4501-5000'] = 0;
	price_range_val['>5000'] = 0;

	for (value in new_arr_val) {
		if (value > 0 && value <= 200) {
			price_range_val['0-200'] += new_arr_val[value];
		} else if (value >= 201 && value <= 400) {
			price_range_val['201-400'] += new_arr_val[value];
		} else if (value >= 401 && value <= 600) {
			price_range_val['401-600'] += new_arr_val[value];
		} else if (value >= 401 && value <= 600) {
			price_range_val['601-800'] += new_arr_val[value];
		} else if (value >= 801 && value <= 1000) {
			price_range_val['801-1000'] += new_arr_val[value];
		} else if (value >= 1001 && value <= 1500) {
			price_range_val['1001-1500'] += new_arr_val[value];
		} else if (value >= 1501 && value <= 2000) {
			price_range_val['1501-2000'] += new_arr_val[value];
		} else if (value >= 2001 && value <= 2500) {
			price_range_val['2001-2500'] += new_arr_val[value];
		} else if (value >= 2501 && value <= 3000) {
			price_range_val['2501-3000'] += new_arr_val[value];
		} else if (value >= 3001 && value <= 3500) {
			price_range_val['3001-3500'] += new_arr_val[value];
		} else if (value >= 3501 && value <= 4000) {
			price_range_val['3501-4000'] += new_arr_val[value];
		} else if (value >= 4001 && value <= 4500) {
			price_range_val['4001-4500'] += new_arr_val[value];
		} else if (value >= 4501 && value <= 5000) {
			price_range_val['4501-5000'] += new_arr_val[value];
		} else if (value >= 5000) {
			price_range_val['>5000'] += new_arr_val[value];
		}
	}

	return price_range_val;
}

function ksort(inputArr, sort_flags) {
	var tmp_arr = {}, keys = [], sorter, i, k, that = this, strictForIn = false, populateArr = {};

	switch (sort_flags) {
	case 'SORT_STRING':
		// compare items as strings
		sorter = function(a, b) {
			return that.strnatcmp(a, b);
		};
		break;
	case 'SORT_LOCALE_STRING':
		// compare items as strings, based on the current locale (set with  i18n_loc_set_default() as of PHP6)
		var loc = this.i18n_loc_get_default();
		sorter = this.php_js.i18nLocales[loc].sorting;
		break;
	case 'SORT_NUMERIC':
		// compare items numerically
		sorter = function(a, b) {
			return ((a + 0) - (b + 0));
		};
		break;
	// case 'SORT_REGULAR': // compare items normally (don't change types)
	default:
		sorter = function(a, b) {
			var aFloat = parseFloat(a), bFloat = parseFloat(b), aNumeric = aFloat
					+ '' === a, bNumeric = bFloat + '' === b;
			if (aNumeric && bNumeric) {
				return aFloat > bFloat ? 1 : aFloat < bFloat ? -1 : 0;
			} else if (aNumeric && !bNumeric) {
				return 1;
			} else if (!aNumeric && bNumeric) {
				return -1;
			}
			return a > b ? 1 : a < b ? -1 : 0;
		};
		break;
	}

	// Make a list of key names
	for (k in inputArr) {
		if (inputArr.hasOwnProperty(k)) {
			keys.push(k);
		}
	}
	keys.sort(sorter);

	// BEGIN REDUNDANT
	this.php_js = this.php_js || {};
	this.php_js.ini = this.php_js.ini || {};
	// END REDUNDANT
	strictForIn = this.php_js.ini['phpjs.strictForIn']
			&& this.php_js.ini['phpjs.strictForIn'].local_value
			&& this.php_js.ini['phpjs.strictForIn'].local_value !== 'off';
	populateArr = strictForIn ? inputArr : populateArr;

	// Rebuild array with sorted key names
	for (i = 0; i < keys.length; i++) {
		k = keys[i];
		tmp_arr[k] = inputArr[k];
		if (strictForIn) {
			delete inputArr[k];
		}
	}
	for (i in tmp_arr) {
		if (tmp_arr.hasOwnProperty(i)) {
			populateArr[i] = tmp_arr[i];
		}
	}

	return strictForIn || populateArr;
}

function slider_disp(first_val, last_val) {
	var min_val = parseInt(first_val);
	var max_val = parseInt(parseInt(last_val)-parseInt(first_val));
	
	jQuery( "#slider-range" ).slider({
		range: true,
		min: min_val,
		max: max_val,
		values: [ min_val, max_val ],
		slide: function( event, ui ) {
			document.getElementById('price_min').value = ui.values[ 0 ];
			document.getElementById('price_max').value = ui.values[ 1 ];
			document.getElementById('minPrice').value = ui.values[ 0 ];
			document.getElementById('maxPrice').value = ui.values[ 1 ];

		 	}
		});
}

function submit_price_range() {
	var min_price_val = document.getElementById('minPrice').value;
	var max_price_val = document.getElementById('maxPrice').value;
	if (min_price_val == '') {
		min_price_val = '*';
	}
	if (max_price_val == '') {
		max_price_val = '*';
	}
	process_search(document.getElementById('search').value, document.getElementById('category_hidden').value, 0, min_price_val,max_price_val, '', '');
}

function search_order_func(image_file) {
	var min_price_val = document.getElementById('minPrice').value;
	var max_price_val = document.getElementById('maxPrice').value;

	if (document.getElementById('sort_order').value == 'desc') {
		document.getElementById('image_val').innerHTML = '<img class="v-middle" id="asc_desc_img" alt="Set Ascending Direction" src="'+ image_file + 'images/i_desc_arrow.gif">';
		process_search(document.getElementById('search').value, document.getElementById('category_hidden').value, 0, min_price_val,max_price_val, document.getElementById('sort_orderby').value,'asc');
	}
	else if (document.getElementById('sort_order').value == 'asc') {
		document.getElementById('image_val').innerHTML = '<img class="v-middle" id="asc_desc_img" alt="Set Descending Direction" src="'+ image_file + 'images/i_asc_arrow.gif">';
		process_search(document.getElementById('search').value, document.getElementById('category_hidden').value, 0, min_price_val,max_price_val, document.getElementById('sort_orderby').value,'desc');
	}
}
