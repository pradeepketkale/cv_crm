// This function creates a standard table with column/rows
// Parameter Information
// objArray = Anytype of object array, like JSON results
// theme (optional) = A css class to add to the table (e.g. <table class="<theme>">
// enableHeader (optional) = Controls if you want to hide/show, default is show
function CreateTableView(objArray) {
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
  
    var str = '';

    var j = 1;

    if(array.length>0)
    {
    	for (var i = 0; i < array.length; i++) {
		//	alert(array[i]['payment_method']);
           
            if(j == 1) {
				 
            str += '<ul class="products-grid first">';
			 
            }
           
            if(array[i]['discount']>0)
            {
				 
            	var special_val = '<div class="products price-box"><p class="old-price"><span class="price-label"></span><span id="old-price-' + array[i]['entity_id'] + '" class="price">Rs. ' + array[i]['price'] + '</span></p><p class="special-price"><span class="price-label"></span><span id="product-price-' + array[i]['entity_id'] + '" class="price">Rs. ' + (array[i]['price']-array[i]['discount']) + '</span></p></div><div class="clear"></div>'
            }
            else {
            	var special_val = '<div class="products price-box"> <span id="product-price-' + array[i]['entity_id'] + '" class="regular-price"> <span class="price 123">Rs. ' + array[i]['price'] + '</span> </span> </div>';
            }
			
          if(array[i]['payment_method'] == 0)
			{
				str += '<li class="item first"><div class="prCnr"><div class="codimage3" style="float:right;">COD <img align="texttop" border="0" src="http://www.craftsvilla.com/skin/frontend/default/craftsvilla2012/images/tickforcod.png"/></div>';
			} 
					else{

					str += '<li class="item first"><div class="prCnr">';
					}	
          
           
            str += '<a class="product-image spriteimg" title="'+array[i]['name']+'" href="http://www.craftsvilla.com/' + array[i]['url_path'] + ' "><img class="lazy" height="160" width="160" alt="'+array[i]['name']+'" src="'+array[i]['image']+'"></a><p class="shopbrief"><a title="'+array[i]['name']+'" href="http://www.craftsvilla.com/' + array[i]['url_path'] + '">'+array[i]['name']+'</a></p> <div class="vendorname"> <b>By <a href="http://craftsvilla.com/'+array[i]['vendor_url']+'" target="_blank">' + array[i]['vendor'] + '. </a><ul id="socielicon"><li><a href="http://pinterest.com/pin/create/button/?url=www.craftsvilla.com&media='+array[i]['image']+'" class="pin-it-button" count-layout="none" target="_blank"><img border="0" src="http://d1g63s1o9fthro.cloudfront.net/skin/frontend/default/craftsvilla2012/img/PinExt.png"/></a></li></ul></b></div><!-- magento original price -->'+special_val+' <!-- magento original price --> <!-- customize price--> <!-- <h3 id="home"></h3><?php// else: ?> <h3 id="home"></h3> <h5></h5> --> <!-- customize price--><div class="clear"></div>  </div></li>';

            j++;

            if(j>4)
            {
            j = 1;
            str += '</ul>';
            }
            else if(i == (array.length-1))
            {
            str += '</ul>';
            }
			
			 
        }
		
    }
    else {
    	str += '<p class="note-msg">Your search returns no results.</p>';
    }
    
    //str += '';
    return str;
}


//For generate html for facet color
//function CreateCategoryTableView(objArray, search_value, category_val, color_val, size_val, checked_values) {
function CreateCategoryTableView(objArray, search_value, category_val, color_val, location_val, checked_values, min_value, max_value) {
	var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;

	if(category_val == 'category_name_value')
	{
		var str_cat = '<ol id="cat_ol_id">';
	} 
	else if(color_val == 'color_value')
	{
		var str_cat = '<ol id="color_ol_id">';
	}
	else if(location_val == 'location_value')
	{
		var str_cat = '<ol id="location_ol_id">';
	}
	
	
	if(category_val == 'category_name_value')
	{
		for(var index in array) {
			if(array.hasOwnProperty(index))
			{
				str_cat += '<li><a href="javascript: process_search(\''+search_value+'\', \''+index+'\', \'0\', '+min_value+', '+max_value+')">'+index+'</a><span>('+parseInt(array[index])+')</span></li>';
			}	
		}
	}	
	
    var j = 1;
    
    /*if(category_val == 'category_name_value')
{
    var category_arr_values = new Array();
    var kk = 1
    for (var mm = 0; mm < array.length; mm++) {
    if(kk == 1) {
    var temp_category_arr_values = array[mm].split(',');
   
    for(var tt=0;tt<temp_category_arr_values.length;tt++)
    {
    if(in_array(temp_category_arr_values[tt], category_arr_values) == false)
    {
    category_arr_values[tt] = temp_category_arr_values[tt];
    str_cat += '<li><a href="javascript: process_search(\''+search_value+'\', \''+temp_category_arr_values[tt]+'\', \'0\', '+min_value+', '+max_value+')">'+temp_category_arr_values[tt]+'</a>';
    break;
    }
    }
    }
       
    //alert(category_arr_values);
   
            kk++;

            if(kk>2)
            {
            kk = 1;
            }
    }
} */
    if(category_val != 'category_name_value')
    {
    	for (var i = 0; i < array.length; i++) {
    	    if(j == 1) {
    		    /*if(category_val == 'category_name_value')
    		    {
    			    if(array[i] != '' && parseInt(array[i+1]) > 0)
    			    {
    			    	str_cat += '<li><a href="javascript: process_search(\''+search_value+'\', \''+array[i]+'\', \'0\', '+min_value+', '+max_value+')">'+array[i]+'</a>';
    			    	//str_cat += '<li><a href="javascript: process_search(\''+search_value+'\', \''+category_arr_values[0]+'\', \'0\', '+min_value+', '+max_value+')">'+array[i]+'</a>';
    			    }
    		    }
    		    else*/ if(color_val == 'color_value')
    		    {
    			    var checked_value = '';
    			    var color_assign_value = '';
    			    if(array[i] != '' && parseInt(array[i+1]) > 0)
    			    {
    				    if(in_array(array[i], checked_values))
    				    {
    				    	checked_value = 'checked="checked"';
    				    }
    				    else {
    				    	checked_value = '';
    				    }
    				   
    				    if(document.getElementById('color_hidden').value)
    				    {
    				    	color_assign_value = document.getElementById('color_hidden').value;
    				    }
    				    else {
    				    	color_assign_value = array[i];
    				    }
    				    //str_cat += '<li><input type="checkbox" name="color_facet" value="'+array[i]+'" '+checked_value+' onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \''+color_assign_value+'\', \''+size_val+'\', \'0\')" /><a href="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \''+color_assign_value+'\', \''+size_val+'\', \'0\')">'+array[i]+'</a>';
    				    str_cat += '<li><input type="checkbox" name="color_facet" value="'+array[i]+'" '+checked_value+' /><span class="price 123">'+array[i]+'</span>';
    			    }
    		    }
    		    else if(location_val == 'location_value')
    		    {
    			    var checked_value = '';
    			    var location_assign_value = '';
    			    if(array[i] != '' && parseInt(array[i+1]) > 0)
    			    {
    				    if(in_array(array[i], checked_values))
    				    {
    				    	checked_value = 'checked="checked"';
    				    }
    				    else {
    				    	checked_value = '';
    				    }
    				   
    				    if(document.getElementById('location_hidden').value)
    				    {
    				    	size_assign_value = document.getElementById('location_hidden').value;
    				    }
    				    else {
    				    	location_assign_value = array[i];
    				    }
    				    str_cat += '<li><input type="checkbox" name="location_facet" value="'+array[i]+'" '+checked_value+' /><span class="price 123">'+array[i]+'</span>';
    			    }
    			    /*if(array[i] != '' && parseInt(array[i+1]) > 0)
    			    {
    			    str_cat += '<li><input type="checkbox" value="'+array[i]+'" name="size_facet" onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\')" /><a href="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\')">'+array[i]+'</a>';
    			    }*/
    		    }
    	    }
    	    else if(j == 2) {
    		    if(array[i-1] != '' && parseInt(array[i]) > 0)
    		    {
    		    	str_cat += ' <span>('+parseInt(array[i])+')</span></li>';
    		    }
    	    }
    	   
    	    j++;
    	
    	    if(j>2)
    	    {
    	    	j = 1;
    	    }
        }
    }
    
    //onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\', '+min_value+', '+max_value+');"
    //onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\', '+min_value+', '+max_value+')"
    if(category_val != 'category_name_value')
    {
    	str_cat += '<li><input type="button" class="bluebtnsmall" value="Search" name="search_submit" id="search_submit" onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\', '+min_value+', '+max_value+')" /></li>';
    }
    
    
    str_cat += '</ol>';
    return str_cat;
}

function CreatePriceTableView(objArray, search_value, category_val, checked_values, min_val, max_val) {
var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
var str_price = '<ol id="price_ol_id">';

    var j = 1;

    for(values in array)
    {
    if(array[values])
    {
    if(in_array(values, checked_values))
        {
        checked_value = 'checked="checked"';
        }
        else {
        checked_value = '';
        }
       
        if(document.getElementById('price_hidden').value)
    {
        price_assign_value = document.getElementById('price_hidden').value;
    }
    else {
    price_assign_value = values;
    }
       
        str_price += '<li><input type="checkbox" name="price_facet" value="'+values+'" '+checked_value+' /><span class=\'price 123\'>'+values+'</span><span>('+array[values]+')</span>';
    }
    }
    
    str_price += '<li><input type="button" class="bluebtnsmall" name="search_price_val" id="search_price_val" onclick="javascript: process_search(\''+search_value+'\', \''+category_val+'\', \'0\', \'\', \'\');" value="Search" /></li>';
    
    //str_price += '<p>From: <input type="text" id="minPrice" name="minPrice" value="'+min_val+'" />Rs to <input type="text" id="maxPrice" name="maxPrice" value="'+max_val+'" />Rs</p><input type="button" name="search_price_val" id="search_price_val" onclick="submit_price_range();" value="Search" />';
    str_price += '</ol>';
    //str_price += '<div class="pform"><p><label>From:</label> <input type="text" id="minPrice" name="minPrice" value="'+min_val+'" />Rs <br /><label>to:</label> <input type="text" id="maxPrice" name="maxPrice" value="'+max_val+'" />Rs</p><div class="slideroot"><div class="slider4"><div class="indicator" id="indicator1">&nbsp;</div><div class="indicator" id="indicator2">&nbsp;</div></div></div><input type="button" name="search_price_val" id="search_price_val" onclick="submit_price_range();" value="Search" class="bluebtnsmall" /></div>';
    str_price += '<div class="pform"><p><label>From:</label> <input type="text" id="minPrice" name="minPrice" value="'+min_val+'" />Rs <br /><label>to:</label> <input type="text" id="maxPrice" name="maxPrice" value="'+max_val+'" />Rs</p>';
    str_price += '<div class="demo"><div id="slider-range"></div></div><div class="demo-description"><p><input type="button" name="search_price_val" id="search_price_val" onclick="submit_price_range();" value="Search" class="bluebtnsmall" /></p></div>';
    
    return str_price;
}

/*function get_param_value(aId) {
var collection = document.getElementById(aId).getElementsByTagName('INPUT');
var return_array_val = new Array();
for (var x=0; x<collection.length; x++) {
if (collection[x].type.toUpperCase()=='CHECKBOX' && collection[x].checked)
//collection[x].checked = aChecked;
//alert("Bow Bow"+collection[x].value);
//return_array_val[] = collection[x].value;
return_array_val.push(collection[x].value);
return return_array_val;
}
}*/

//For generate html for facet color
/*function CreateColorTableView(objArray) {
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    
    var str = '';

    var j = 1;

    for (var i = 0; i < array.length; i++) {

   
    }

    //str += '';
    return str;
}*/


function in_array( what, where ){
var a=false;
for(var i=0;i<where.length;i++){
 if(what == where[i]){
 a=true;
        break;
 }
}
return a;
}


//Removes leading whitespaces
function LTrim( value ) {
var re = /\s*((\S+\s*)*)/;
return value.replace(re, "$1");
}

// Removes ending whitespaces
function RTrim( value ) {
var re = /((\s*\S+)*)\s*/;
return value.replace(re, "$1");
}

// Removes leading and ending whitespaces
function trim( value ) {
return LTrim(RTrim(value));
}


function stripslashes (str) {
    return (str + '').replace(/\\(.?)/g, function (s, n1) {
        switch (n1) {
        case '\\':
            return '\\';
        case '0':
            return '\u0000';
        case '':
            return '';
        default:
            return n1;
        }
    });
}