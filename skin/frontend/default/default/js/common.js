function validatFax(value,id){		
	if(value!='' && value.length != 10){
		alert("Mobile no. should be 10 digit");
		 $(id).focus();			
	}
}
function fillPincode(pincode,opt){
		$(opt+':country_id').disabled=true;
		if(pincode == '') { 
			alert('Please enter a valid Zip/Postal Code') ; 
		}else if(pincode.length!=6){
			alert('Zip/Postal Code should be 6 digit') ; 
			$(opt+':postcode').focus();
		}else {
			var params = 'zpincode=' + pincode ; 
			var myAjax = new Ajax.Request( 
				'/aramex_postcode.php',
				{ 
					method:'post', 
					parameters: params, 
					onComplete: function(ajaxReq) { 
						eval(ajaxReq.responseText) ;
						if(result.found) {
							$(opt+':city').value = result.location_name;
							
							for(i=0;i<document.getElementById(opt+':region_id').length;i++)
							{
								if(document.getElementById(opt+':region_id').options[i].value==result.state_code)
								{
									document.getElementById(opt+':region_id').selectedIndex=i
								}
							}
							$(opt+'_country_id').value = $(opt+':country_id').value;
							$(opt+'_region_id').value = $(opt+':region_id').value;
						
						}
						else {
							$(opt+':city').value = '' ; 
							$(opt+':region').value = '' ;
							alert('We do not deliver to this location currently') ; 
							$(opt+':street1').focus();
						}
					}
				}
			);
		}
	}
/*--------------Function used in edit adders in user profile----*/	
function fillPinecodeEdit(pincode){	
	 if(pincode.length!=6 && pincode!=''){
		alert('Zip/Postal Code should be 6 digit') ; 
		$('zip').focus();
	}else {
		var params = 'zpincode=' + pincode ; 
		var myAjax = new Ajax.Request( 
			'/aramex_postcode.php',
			{ 
				method:'post', 
				parameters: params, 
				onComplete: function(ajaxReq) { 
					eval(ajaxReq.responseText) ;
					if(result.found) {
						$('city').value = result.location_name;							
						for(i=0;i<document.getElementById('region_id').length;i++){
							if(document.getElementById('region_id').options[i].value==result.state_code){
								document.getElementById('region_id').selectedIndex=i
							}
						}							
					}else{
						$('city').value   = ''; 
						$('region').value = '';						
					}
				}
			}
		);
	 }
  }
function telephoneValue(evt,value){	
	evt = (evt) ? evt : event;
	var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : 
	((evt.which) ? evt.which : 0));	
	if(charCode==8 )return true;
	
	/*if(charCode!=48 && value.length==''){	
		return false;		
	}*/
	if (charCode > 31 && (charCode < 48 || charCode > 57 ) && charCode!=45) {
	   return false;
	}
	return true;	
}
function validatString(evt){
	evt = (evt) ? evt : event;
	var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : 
	((evt.which) ? evt.which : 0));				
	if (charCode > 31 && (charCode > 48 || charCode < 57) && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && charCode!=45 && charCode!=39 && charCode!=32) {
		return false;
	}
	return true;	
}
function setShippingValue(){
	$('shipping_country_id').value	= $('shipping:country_id').value
	$('shipping_region_id').value	= $('shipping:region_id').value
}