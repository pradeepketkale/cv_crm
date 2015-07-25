function submitcolorred(){
rewriteUrloffiltersColor("red");
}
function submitcolorgreen(){
rewriteUrloffiltersColor("green");
}
function submitcolorwhite(){
rewriteUrloffiltersColor("white");
}
function submitcolorblack(){
rewriteUrloffiltersColor("black");
}
function submitcoloryellow(){
rewriteUrloffiltersColor("yellow");
}
function submitcolormagenta(){
rewriteUrloffiltersColor("magenta");
}
function submitcolorpurple(){
rewriteUrloffiltersColor("purple");
}
function submitcolorgrey(){
rewriteUrloffiltersColor("grey");
}
function submitcolorblue(){
rewriteUrloffiltersColor("blue");
}
function submitcolorbrown(){
rewriteUrloffiltersColor("brown");
}
function submitcolorsilver(){
rewriteUrloffiltersColor("silver");
}
function submitcolorbeige(){
rewriteUrloffiltersColor("beige");
}
function submitcolorgold(){
rewriteUrloffiltersColor("gold");
}
function submitcolormulticolor(){
rewriteUrloffiltersColor("multicolor");
}


function submitdiscount50(){
		rewriteUrloffiltersDiscount(50,100);
}
function submitdiscount40(){
	
	rewriteUrloffiltersDiscount(40,100);

}
function submitdiscount30(){
	
		rewriteUrloffiltersDiscount(30,100);

}
function submitdiscount20(){
	
	rewriteUrloffiltersDiscount(20,100);
}
function submitdiscount10(){
		rewriteUrloffiltersDiscount(10,100);

}

function submitprice(){
   rewriteUrloffiltersPrice(10,500);
}

function submitprice1(){
   rewriteUrloffiltersPrice(500,1000);
}


function submitprice2(){
   rewriteUrloffiltersPrice(1000,2000);}


function submitprice3(){
   rewriteUrloffiltersPrice(2000,5000);}

function submitprice4(){
   rewriteUrloffiltersPrice(5000,100000);
}

function submitprice5(){
		   var min_value = document.getElementById("min_value5").value;
		   var max_value = document.getElementById("max_value5").value;
	if(isNaN(min_value) || isNaN(max_value)){ 
		alert("Please enter valid price in numbers")
		}
		else{  
		   if(min_value=='')
		   {
			   min_value = '0';

		   }
		   if(max_value=='')
		   {
			   max_value= '100000';
			   
		   }
    var pathname = window.location.pathname;
	var url = "?min_value="+min_value+"&max_value="+max_value;
		 
	window.location.href = pathname+"?min_value=" + min_value+"&max_value="+max_value;

	}
}
function rewriteUrloffiltersPrice(min_value,max_value){
var pathname = window.location.href;
var str1 = 'min_value='+getParameterByNamecv('min_value');
var str2 = 'min_value='+min_value;


if((window.location.href.indexOf('min_value')>=0) && window.location.href.indexOf('max_value')>=0){
			pathname = pathname.replace(str1,str2);
			pathname = pathname.replace('max_value='+getParameterByNamecv('max_value'),'max_value='+max_value);
			window.location.href = pathname;
	}
	else
	{
		if(window.location.href.indexOf('?')>=0)
		 window.location.href = pathname+"&min_value=" + min_value+"&max_value="+max_value;
		else
		window.location.href = pathname+"?min_value=" + min_value+"&max_value="+max_value;
	}		
}
function removeUrloffiltersPrice(){
var pathname = window.location.href;
var str1 = 'min_value='+getParameterByNamecv('min_value');
var str2 = '';


if((window.location.href.indexOf('min_value')>=0) && window.location.href.indexOf('max_value')>=0){
			pathname = pathname.replace('&'+str1,str2);
			pathname = pathname.replace(str1,str2);
			pathname = pathname.replace('&max_value='+getParameterByNamecv('max_value'),'');
			pathname = pathname.replace('max_value='+getParameterByNamecv('max_value'),'');
			pathname = cleanUrlFilter(pathname);
			window.location.href = pathname;

	}
			
}
function rewriteUrloffiltersDiscount(min_discount,max_discount){
var pathname = window.location.href;
var str1 = 'min_discount='+getParameterByNamecv('min_discount');
var str2 = 'min_discount='+min_discount;


if((window.location.href.indexOf('min_discount')>=0) && window.location.href.indexOf('max_discount')>=0){
			pathname = pathname.replace(str1,str2);
			pathname = pathname.replace('max_discount='+getParameterByNamecv('max_discount'),'max_discount='+max_discount);
			window.location.href = pathname;
	}
	else
	{
		if(window.location.href.indexOf('?')>=0)
		 window.location.href = pathname+"&min_discount=" + min_discount+"&max_discount="+max_discount;
		else
		window.location.href = pathname+"?min_discount=" + min_discount+"&max_discount="+max_discount;
	}		
}

function removefiltersDiscount(){
var pathname = window.location.href;
var str1 = 'min_discount='+getParameterByNamecv('min_discount');
var str2 = '';


if((window.location.href.indexOf('min_discount')>=0) && window.location.href.indexOf('max_discount')>=0){
			pathname = pathname.replace('&'+str1,str2);
			pathname = pathname.replace(str1,str2);
			pathname = pathname.replace('&max_discount='+getParameterByNamecv('max_discount'),'');
			pathname = pathname.replace('max_discount='+getParameterByNamecv('max_discount'),'');
			pathname = cleanUrlFilter(pathname);
			window.location.href = pathname;

	}
			
}

function rewriteUrloffiltersColor(color){
var pathname = window.location.href;
var str1 = 'color='+getParameterByNamecv('color');
var str2 = 'color='+color;


if((window.location.href.indexOf('color')>=0)){
			pathname = pathname.replace(str1,str2);
			window.location.href = pathname;
	}
	else
	{
		if(window.location.href.indexOf('?')>=0)
		 window.location.href = pathname+"&color=" + color;
		else
		window.location.href = pathname+"?color=" + color;
	}		
}
function removefiltersColor(){
var pathname = window.location.href;
var str1 = 'color='+getParameterByNamecv('color');
var str2 = '';

if((window.location.href.indexOf('color')>=0)){
			pathname = pathname.replace('&'+str1,str2);
			pathname = pathname.replace(str1,str2);
			pathname = cleanUrlFilter(pathname);			
			window.location.href = pathname;
	}
}
function removefiltersCod(){
var pathname = window.location.href;
var str1 = 'cod='+getParameterByNamecv('cod');
var str2 = '';

if((window.location.href.indexOf('cod')>=0)){
			pathname = pathname.replace('&'+str1,str2);
			pathname = pathname.replace(str1,str2);
			pathname = cleanUrlFilter(pathname);			
			window.location.href = pathname;
	}

}

function removefiltersZip(){
var pathname = window.location.href;
var str1 = 'zip='+getParameterByNamecv('zip');
var str2 = '';

if((window.location.href.indexOf('zip')>=0)){
			pathname = pathname.replace('&'+str1,str2);
			pathname = pathname.replace(str1,str2);
			pathname = cleanUrlFilter(pathname);			
			window.location.href = pathname;
	}

}


function cleanUrlFilter(str1){
var pathname = window.location.href;
if((str1.indexOf('?&')>=0)){
		str1 = str1.replace('?&','?');
	}
//alert(str1[str1.indexOf('?')+1])
if((str1[str1.indexOf('?')+1]==null)){

		str1 = str1.replace('?','');
	}
			return str1;
}



jQuery(document).ready(function(){

//var currentCurrency = document.getElementById("currencysymbol").value;


if((window.location.href.indexOf('min_value')>=0) && window.location.href.indexOf('max_value')>=0){


var htmlPriceremove; 
if(getParameterByNamecv('min_value') <= 4999){
htmlPriceremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removeUrloffiltersPrice()" title="Remove Price Filter" class="spriteimg remove"> </div>'+'<div style="margin:2px;display: inline-block;vertical-align:8px;font-size: 12px;"> Rs.' +getParameterByNamecv('min_value') + ' - Rs.' + getParameterByNamecv('max_value')+'</div>';
}
else{
htmlPriceremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removeUrloffiltersPrice()" title="Remove Price Filter" class="spriteimg remove"> </div>'+'<div style="margin:2px;display: inline-block;vertical-align:8px;font-size: 12px;"> Above Rs.5000</div>';
}

document.getElementById("removeFiltPrice32514").innerHTML=htmlPriceremove;
}

if((window.location.href.indexOf('min_discount')>=0) && window.location.href.indexOf('max_discount')>=0){

var htmlDiscountremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removefiltersDiscount()" title="Remove Discount Filter" class="spriteimg remove"> </div>'+'<div style="margin:2px;display: inline-block;vertical-align:8px;font-size: 12px">Discount > '+getParameterByNamecv('min_discount') +'%</div>';
document.getElementById("removeFiltDiscount758214").innerHTML=htmlDiscountremove;
}

if((window.location.href.indexOf('color')>=0)){

var htmlColorremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removefiltersColor()" title="Remove Color Filter" class="spriteimg remove"> </div>'+' <div  style ="width:20px;height:20px;background:'+getParameterByNamecv('color')+';cursor:pointer;margin:2px;display: inline-block;font-size: 12px;"></div> ';
document.getElementById("removeFiltColor65412").innerHTML=htmlColorremove;
}
if((window.location.href.indexOf('cod')>=0)){

var htmlCodremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removefiltersCod()" title="Remove COD Filter" class="spriteimg remove"> </div>'+'<div style="margin:2px;display: inline-block;vertical-align:8px;font-size: 12px;">Show Only COD</div>';
document.getElementById("removeFiltCashondelivery9635").innerHTML=htmlCodremove;
}

if((window.location.href.indexOf('zip')>=0)){

var htmlZipremove = '<div style="cursor:pointer;margin-left:2px;width:25px;display: inline-block;" onClick = "removefiltersZip()" title="Remove Zipcode Filter" class="spriteimg remove"> </div>'+'<div style="margin:1px;display: inline-block;vertical-align:8px;font-size: 12px;">Pincode: '+getParameterByNamecv('zip')+'</div>';
document.getElementById("removeFiltZipcode85258").innerHTML=htmlZipremove;
}

});

function getParameterByNamecv(name) {
    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}

function rewriteUrloffilterszip(){
	
var paramzip = document.getElementById('pincode_value').value;
var pathname = window.location.href;

var str1 = 'zip='+getParameterByNamecv('zip');
var str2 = 'zip='+paramzip;


if((window.location.href.indexOf('zip')>=0)){
			pathname = pathname.replace(str1,str2);
			window.location.href = pathname;
	}
	else
	{
		if(window.location.href.indexOf('?')>=0)
		 window.location.href = pathname+"&zip=" + paramzip;
		else
		window.location.href = pathname+"?zip=" + paramzip;
	}		
}

function rewriteUrloffiltersCod2135(){
	var pathname = window.location.href;
	var str1 = 'cod=showcod';
	if((window.location.href.indexOf('cod')>=0)){
		window.location.href = pathname;
	}
	else
	{
		if(window.location.href.indexOf('?')>=0)
		window.location.href = pathname+"&cod=showcod";
		else
		window.location.href = pathname+"?cod=showcod";
	}		
}



