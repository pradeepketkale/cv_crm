jQuery(document).ready(
	function() {
		process_shop_search(document.getElementById('search').value, 0, '', '');
	}	
);

function process_shop_search(search_value, page_value, sort_field_val, sort_field_by) {
	
	jQuery('#shop-search').html('');
	jQuery('#tot_item_div').html('');
	jQuery('#pagination_div').html('');
	document.getElementById('fancybox-overlay').style.display = 'block';
	window.scrollTo(0, 0);
	
	if(sort_field_val)
	{
		document.getElementById('sort_field').value = sort_field_val;
	}
	else
	{
		document.getElementById('sort_field').value = 'items_count';
	}	
	
	if(sort_field_by)
	{
		document.getElementById('sort_by').value = sort_field_by;
	}
	else
	{
		document.getElementById('sort_by').value = 'desc';
	}	
	
	document.getElementById('page').value = page_value;
	
	jQuery.ajax({
		url : "/solariumsearch/shopgetvalues.php",
		type : "POST",
		data : {
			search_val : search_value,
			page : page_value,
			sort_field : sort_field_val,
			sort_by : sort_field_by
		},
		cache : false,
		success : function(response_shop) {
			/*alert(response_shop);
			return false;*/
			var myObject_shop = eval('(' + response_shop + ')');
			var myobject_shop_records = myObject_shop.facet_counts.facet_fields.vendor;
			
			var myobject_shop_products_records = myObject_shop.products;
			//var total_pages = Math.ceil(mynew_object.numFound/ recotd_get);
			//var total_records = mynew_object.numFound;
			//alert(myobject_shop_products_records.toSource());
			
			/*
			 *
			 * 
			 * 
			 * <div class="shop">
      <div class="shop-info v2">
        <div class="shop-details"> <span class="shopname wrap "> <a href="#"> organicquiltcompan... </a> </span> </div>
        <div class="shop-owner">
          <div class="avatar trigger-action-toolbox"> <a data-uid="5377363" href="#"> <img width="75" height="75" alt="Becky and Lisa" class="grey_border" src="<?php echo $this->getSkinUrl('images/spowner.jpg')?>"> </a> </div>
          <div class="real-name"> <a href="#">Becky and Lisa</a> <span class="shop-owner-heading">Shop Owner</span> </div>
        </div>
      </div>
      <div class="shop-listings">
        <div class="item"> <a href="#"> <img width="75" height="75" alt="" src="<?php echo $this->getSkinUrl('images/sp01.jpg')?>"></a> </div>
        <div class="item"> <a href="#"> <img width="75" height="75" alt="" src="<?php echo $this->getSkinUrl('images/sp01.jpg')?>"></a> </div>
        <div class="item"> <a href="#"> <img width="75" height="75" alt="" src="<?php echo $this->getSkinUrl('images/sp01.jpg')?>"></a> </div>
        <div class="item"> <a href="#"> <img width="75" height="75" alt="" src="<?php echo $this->getSkinUrl('images/sp01.jpg')?>"></a> </div>
        <div class="clear"></div>
      </div>
      <div class="shop-count square-count"><a href="#"> <span class="count-number">189</span> items </a> </div>
      <div class="clear"></div>
    </div>
			 * */
			var html_value = '';
		    var j = 0;
		    
		    var tot_no_values = 0;
		    
			if(myobject_shop_records)
			{
				var array = typeof myobject_shop_records != 'object' ? JSON.parse(myobject_shop_records) : myobject_shop_records;
			}
			else
			{
				html_value += '<p class="note-msg">Your search returns no results.</p>';
		    	document.getElementById('fancybox-overlay').style.display = 'none';
		    	var array = Array(); 
			}	
			
			var products_array = typeof myobject_shop_products_records != 'object' ? JSON.parse(myobject_shop_products_records) : myobject_shop_products_records;

		    if(array.length>0)
		    {
				for (var i = 0; i < array.length; i++) {
					j = j+1;
					if(j == 1) {
						tot_no_values = tot_no_values + 1;
						html_value += '<div class="shop"><div class="shop-info v2"><div class="shop-details"> <span class="shopname wrap "> <a href="http://www.craftsvilla.com/'+products_array[array[i]][0]['vendor_url']+'" target="_blank">'+array[i]+'</a> </span> </div><div class="shop-owner"><div class="avatar trigger-action-toolbox"> <a data-uid="5377363" href="http://www.craftsvilla.com/'+products_array[array[i]][0]['vendor_url']+'/thoughtyard/profile/" target="_blank"> <img alt="'+products_array[array[i]][0]['vendor_owner']+'" class="grey_border" src="'+products_array[array[i]][0]['vendor_logo']+'"> </a> </div><div class="real-name"> <a href="http://www.craftsvilla.com/'+products_array[array[i]][0]['vendor_url']+'/thoughtyard/profile/" target="_blank">'+products_array[array[i]][0]['vendor_owner']+'</a> <span class="shop-owner-heading">Shop Owner</span> </div></div></div><div class="shop-listings">';
						for(var h=0;h<products_array[array[i]].length;h++)
						{
							html_value += '<div class="item"> <a href="http://www.craftsvilla.com/'+products_array[array[i]][h]['url_path']+'" target="_blank"><img width="75" height="75" alt="" src="'+products_array[array[i]][h]['image']+'"></a> </div>';
						}	
						html_value += '<div class="clear"></div></div><div class="shop-count square-count">';
						html_value += '<a href="http://www.craftsvilla.com/'+products_array[array[i]][0]['vendor_url']+'/thoughtyard/profile/" target="_blank">';
					}
					else {
						html_value += '<span class="count-number">'+array[i]+'</span> items </a> </div><div class="clear"></div></div>';
						j = 0;
					}
				}
		    }
		    else
		    {
		    	jQuery('#shop-search').html();
				jQuery('#shop-search').html(html_value);
		    }	
			
		    var total_val_disp_div_val = '';
		    if(myObject_shop.total_facets>20)
		    {
		    	//alert("Bow Bow"+page_value);
		    	var curr_page_val = 0;
		    	var last_val = myObject_shop.total_facets;
		    	if(page_value != 0)
		    	{
		    		curr_page_val = parseInt(((page_value-1)*20)+1);
		    	}
		    	else
		    	{
		    		curr_page_val = 1;
		    	}	
		    	
		    	if(parseInt(curr_page_val+19)<myObject_shop.total_facets)
		    	{
		    		last_val = parseInt(curr_page_val+19);
		    	}	
		    	
		    	if(curr_page_val == 0)
		    	{
		    		total_val_disp_div_val = 'Items <span>'+(curr_page_val+1)+'</span> to <span>'+(last_val+1)+'</span> of <span>'+myObject_shop.total_facets+'</span> total';
		    	}	
		    	else
		    	{
		    		total_val_disp_div_val = 'Items <span>'+(curr_page_val)+'</span> to <span>'+(last_val)+'</span> of <span>'+myObject_shop.total_facets+'</span> total';
		    	}	
		    }	
		    else
		    {
		    	total_val_disp_div_val = 'Total <span>'+myObject_shop.total_facets+'</span> Items';
		    }	
		    
		    /*alert("PAGE VALUE:"+page_value);
	    	alert("Current Page Value:"+curr_page_val);*/
		    
		    jQuery('#tot_item_div').html();
		    jQuery('#tot_item_div').html(total_val_disp_div_val);
		    
		    if(myObject_shop.total_facets>20)
		    {
		    	//var pagination_div = '<ol><li> <a title="Previous" href="#" class="previous i-previous"> <img width="27" height="27" border="0" align="Previous" title="Previous" src="http://doejofinal.craftsvilla.com/skin/frontend/default/craftsvilla2012/img/icon/pagination_left.png"> </a> </li><li class="current">1</li><li><a href="#">2</a></li><li><a href="#">3</a></li><li><a href="#">4</a></li><li><a href="#">5</a></li><li><a href="#" title="" class="next_jump">...</a></li><li><a href="#" class="last">15</a></li><li> <a title="Next" href="#" class="next i-next"> <img width="27" height="27" border="0" align="Next" title="Next" src="http://doejofinal.craftsvilla.com/skin/frontend/default/craftsvilla2012/img/icon/pagination_right.png"> </a> </li></ol>';
		    	/*************** pagination ************************************/

				if (page_value)
					var page = parseInt(page_value);
				else
					var page = 0;
				var tpages = Math.ceil(myObject_shop.total_facets/20);
				var adjacents = 4;

				if (page <= 0)
					page = 1;
				var out = "<ol>";
				
				// previous
		        if(page==1) {
		            out+= "<li>&nbsp;</li>";
		        }
		        /*else if(((page/myObject_shop.total_facets)+1)==2) {
		            out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '0', '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla/images/previous-btn.gif\"></a></li>";
		        }*/
		        else {
		        	out+= "<li><a class=\"previous i-previous\" title=\"Previous\" href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '"+(page_value-1)+"', '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Previous\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_left.png\"></a></li>";
		        	//out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', "+(page-myObject_shop.total_facets)+", '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla/images/previous-btn.gif\"></a></li>";
		        }
		           
		        // first
		        if((page+1)>(adjacents+1)) {
		    		out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '0', '"+sort_field_val+"', '"+sort_field_by+"')\">1</a></li>";
		    	}
		        /*if(((page/myObject_shop.total_facets)+1)>(adjacents+1)) {
		            $out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '"+search_cat+"', '0', '"+sort_field_val+"', '"+sort_field_by+"')\">1</a></li>";
		        }*/
		           
		        // interval
		        if((page+1)>(adjacents+2)) {
		    		out+= "<li>...</li>";
		    	}
		        /*if(((page/myObject_shop.total_facets)+1)>(adjacents+2)) {
		            $out+= "<li>...</li>";
		        }*/
		           
		        
		        // pages
		    	pmin = ((page+1)>adjacents) ? ((page+1)-adjacents) : 1;
		    	pmax = ((page+1)<(tpages-adjacents)) ? ((page+1)+adjacents) : tpages;
		    	for(var i=pmin; i<=pmax; i++) {
		    		if(i==page) {
		    			out+= "<li class=\"current\">" + i + "</li>\n";
		    		}
		    		else if(i==1) {
		    			out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '1', '"+sort_field_val+"', '"+sort_field_by+"')\">" + i + "</a></li>";
		    		}
		    		else {
		    			out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '"+i+"', '"+sort_field_val+"', '"+sort_field_by+"')\">" + i + "</a></li>";
		    		}
		    	}
		    	// interval
		    	if((page+1)<(tpages-adjacents-1)) {
		    		out+= "<li>...</li>";
		    	}
		        
		        // pages
		        /*var pmin = (((page/myObject_shop.total_facets)+1)>adjacents) ? (((page/myObject_shop.total_facets)+1)-adjacents) : 1;
		        var pmax = (((page/myObject_shop.total_facets)+1)<(tpages-adjacents)) ? (((page/myObject_shop.total_facets)+1)+adjacents) : tpages;
		        for(var i=pmin; i<=pmax; i++) {
		            if(i==((page/myObject_shop.total_facets)+1)) {
		            	out+= "<li class=\"current\">" + i + "</li>";
		            }
		            else if(i==1) {
		            	out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '0', '"+sort_field_val+"', '"+sort_field_by+"')\">" + i + "</a></li>";
		            }
		            else {
		            	out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '"+((i*myObject_shop.total_facets)-myObject_shop.total_facets)+"', '"+sort_field_val+"', '"+sort_field_by+"')\">" + i + "</a></li>";
		            }
		        }

		        // interval
		        if(((page/myObject_shop.total_facets)+1)<(tpages-adjacents-1)) {
		        	out+= "<li>...</li>";
		        }*/
		           
		    	// last
		    	if((page+1)<(tpages-adjacents)) {
		    		out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '"+tpages+"', '"+sort_field_val+"', '"+sort_field_by+"')\">" + tpages + "</a></li>";
		    	}
		    	
		    	// last
		        /*if(((page/myObject_shop.total_facets)+1)<(tpages-adjacents)) {
		        	out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '"+((tpages*myObject_shop.total_facets)-myObject_shop.total_facets)+"', '"+sort_field_val+"', '"+sort_field_by+"')\">" + tpages + "</a></li>";
		        }*/
		           
		        // next
		        /*if(((page/myObject_shop.total_facets)+1)<tpages) {
		            if(page == 1)
		            {
		            	out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '"+(myObject_shop.total_facets)+"', '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla/images/next-btn.gif\"></a></li>";
		            }
		            else
		            {
		            	out+= "<li><a href=\"javascript: process_shop_search('"+search_value+"', '"+(page+myObject_shop.total_facets)+"', '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla/images/next-btn.gif\"></a></li>";
		            }
		        }
		        else {
		            out+= "<li><li>";
		        }*/
		    	
		    	if((page+1)<tpages) {
		    		if(page_value == 0)
		    		{
		    			page_value = parseInt(parseInt(page_value) + 1);
		    		}	
		    		out+= "<li><a href=\"javascript:void(0);\" onClick=\"javascript: process_shop_search('"+search_value+"', '"+parseInt(parseInt(page_value) + 1)+"', '"+sort_field_val+"', '"+sort_field_by+"')\"><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_right.png\"></a></li>";
		    		//out+= "<li><a href=\"javascript:process_shop_search('"+search_value+"', '"+parseInt(parseInt(page_value) + 1)+"', '"+sort_field_val+"', '"+sort_field_by+"');\" ><img width=\"26\" height=\"25\" border=\"0\" align=\"Next\" title=\"Next\" src=\"skin/frontend/default/craftsvilla2012/img/icon/pagination_right.png\"></a></li>";
		    	}
		    	else {
		    		out+= "<li>&nbsp;</li>";
		    	}
		           
		        out+= "</ol>";

				/*************** pagination ************************************/
		    	jQuery('#pagination_div').html();
			    jQuery('#pagination_div').html(out);
		    }
		    else 
		    {
		    	jQuery('#pagination_div').html();
		    }	
		    
		    jQuery('#shop-search').html();
			jQuery('#shop-search').html(html_value);
			document.getElementById('fancybox-overlay').style.display = 'none';
			window.scrollTo(0, 0);
			return false;
		}	
	});	
}


function get_search_values(search_val, page, searchby, desc_asc, image_path, change_val)
{
	if(desc_asc == 'desc')
	{
		document.getElementById('asc_desc_id').title = 'Set Ascending Direction'; 
		jQuery('#asc_desc_id').html('<img class="v-middle" alt="Set Ascending Direction" src="'+image_path+'images/i_asc_arrow.gif">');
	}
	else if(desc_asc == 'asc')
	{
		document.getElementById('asc_desc_id').title = 'Set Descending Direction';
		jQuery('#asc_desc_id').html('<img class="v-middle" alt="Set Descending Direction" src="'+image_path+'images/i_desc_arrow.gif">');
	}
	
	if(change_val == 'change')
	{
		if(desc_asc == 'desc')
		{
			process_shop_search(search_val, page, searchby, 'asc');
		}
		else if(desc_asc == 'asc')
		{
			process_shop_search(search_val, page, searchby, 'desc');
		}	
	}
	else
	{
		process_shop_search(search_val, page, searchby, 'desc');
	}	
}

