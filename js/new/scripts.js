window.onerror=function(desc,page,line,chr){
/* alert('JavaScript error occurred! \n'
  +'\nError description: \t'+desc
  +'\nPage address:      \t'+page
  +'\nLine number:       \t'+line
 );*/
}

jQuery(function(){
 jQuery('a').focus(function(){this.blur();});
 SI.Files.stylizeAll();
 slider.init();

 jQuery('input.text-default').each(function(){
  jQuery(this).attr('default',jQuery(this).val());
 }).focus(function(){
  if(jQuery(this).val()==jQuery(this).attr('default'))
   jQuery(this).val('');
 }).blur(function(){
  if(jQuery(this).val()=='')
   jQuery(this).val(jQuery(this).attr('default'));
 });

 jQuery('input.text,textarea.text').focus(function(){
  jQuery(this).addClass('textfocus');
 }).blur(function(){
  jQuery(this).removeClass('textfocus');
 });

 var popopenobj=0,popopenaobj=null;
 jQuery('a.popup').click(function(){
  var pid=jQuery(this).attr('rel').split('|')[0],_os=parseInt(jQuery(this).attr('rel').split('|')[1]);
  var pobj=jQuery('#'+pid);
  if(!pobj.length)
   return false;
  if(typeof popopenobj=='object' && popopenobj.attr('id')!=pid){
   popopenobj.hide(50);
   jQuery(popopenaobj).parent().removeClass(popopenobj.attr('id').split('-')[1]+'-open');
   popopenobj=null;
  }
  return false;
 });
 jQuery('p.images img').click(function(){
  var newbg=jQuery(this).attr('src').split('bg/bg')[1].split('-thumb')[0];
  jQuery(document.body).css('backgroundImage','url('+_siteRoot+'images/bg/bg'+newbg+'.jpg)');
 
  jQuery(this).parent().find('img').removeClass('on');
  jQuery(this).addClass('on');
  return false;
 });
 /*jQuery(window).load(function(){
  jQuery.each(css_ims,function(){(new Image()).src=_siteRoot+'css/images/'+this;});
  jQuery.each(css_cims,function(){
   var css_im=this;
   jQuery.each(['blue','purple','pink','red','grey','green','yellow','orange'],function(){
    (new Image()).src=_siteRoot+'css/'+this+'/'+css_im;
   });
  });
 }); */
 jQuery('div.sc-large div.img:has(div.tml)').each(function(){
  jQuery('div.tml',this).hide();
  jQuery(this).append('<a href="#" class="tml_open">&nbsp;</a>').find('a').css({
   left:parseInt(jQuery(this).offset().left)+864,top:parseInt(jQuery(this).offset().top)+1
  }).click(function(){
   jQuery(this).siblings('div.tml').slideToggle();
   return false;
  }).focus(function(){this.blur();}); 
 });
});
var slider={
 num:-1,
 cur:0,
 cr:[],
 al:null,
 at:10*880,
 ar:true,
 init:function(){
  if(!slider.data || !slider.data.length)
   return false;

  var d=slider.data;
  slider.num=d.length;
  var pos=Math.floor(Math.random()*1);//slider.num);
  for(var i=0;i<slider.num;i++){
   jQuery('#'+d[i].id).css({left:((i-pos)*880)});
   jQuery('#slide-nav').append('<a id="slide-link-'+i+'" href="#" onclick="slider.slide('+i+');return false;" onfocus="this.blur();">'+(i+1)+'</a>');
  }

  jQuery('img,div#slide-controls',jQuery('div#slide-holder')).fadeIn();
  slider.text(d[pos]);
  slider.on(pos);
  slider.cur=pos;
  window.setTimeout('slider.auto();',slider.at);
 },
 auto:function(){
  if(!slider.ar)
   return false;

  var next=slider.cur+1;
  if(next>=slider.num) next=0;
  slider.slide(next);
 },
 slide:function(pos){
  if(pos<0 || pos>=slider.num || pos==slider.cur)
   return;

  window.clearTimeout(slider.al);
  slider.al=window.setTimeout('slider.auto();',slider.at);

  var d=slider.data;
  for(var i=0;i<slider.num;i++)
   jQuery('#'+d[i].id).stop().animate({left:((i-pos)*880)},880,'swing');
  
  slider.on(pos);
  slider.text(d[pos]);
  slider.cur=pos;
 },
 on:function(pos){
  jQuery('#slide-nav a').removeClass('on');
  jQuery('#slide-nav a#slide-link-'+pos).addClass('on');
 },
 text:function(di){
  slider.cr['a']=di.client;
  slider.cr['b']=di.desc;
  slider.ticker('#slide-client span',di.client,0,'a');
  slider.ticker('#slide-desc',di.desc,0,'b');
 },
 ticker:function(el,text,pos,unique){
  if(slider.cr[unique]!=text)
   return false;

  ctext=text.substring(0,pos)+(pos%2?'-':'_');
  jQuery(el).html(ctext);

  if(pos==text.length)
   jQuery(el).html(text);
  else
   window.setTimeout('slider.ticker("'+el+'","'+text+'",'+(pos+1)+',"'+unique+'");',30);
 }
};


// STYLING FILE INPUTS 1.0 | Shaun Inman <http://www.shauninman.com/> | 2007-09-07
if(!window.SI){var SI={};};
SI.Files={
 htmlClass:'SI-FILES-STYLIZED',
 fileClass:'file',
 wrapClass:'cabinet',
 
 fini:false,
 able:false,
 init:function(){
  this.fini=true;
 },
 stylize:function(elem){
  if(!this.fini){this.init();};
  if(!this.able){return;};
  
  elem.parentNode.file=elem;
  elem.parentNode.onmousemove=function(e){
   if(typeof e=='undefined') e=window.event;
   if(typeof e.pageY=='undefined' &&  typeof e.clientX=='number' && document.documentElement){
    e.pageX=e.clientX+document.documentElement.scrollLeft;
    e.pageY=e.clientY+document.documentElement.scrollTop;
   };
   var ox=oy=0;
   var elem=this;
   if(elem.offsetParent){
    ox=elem.offsetLeft;
    oy=elem.offsetTop;
    while(elem=elem.offsetParent){
     ox+=elem.offsetLeft;
     oy+=elem.offsetTop;
    };
   };
  };
 },
 stylizeAll:function(){
  if(!this.fini){this.init();};
  if(!this.able){return;};
 }
};

function checkPostCode(inpfieldid) {
		var shipcode="";
		// Call Ajax
		var fieldid=inpfieldid;
		var fieldvalue=$(fieldid).value;
		if(fieldvalue.length > 0 && $("billing:country_id").value=="IN") {	
			var url = '/postalcode/index/index';
			var params = 'postcode=' + fieldvalue;
			new Ajax.Request(url, {
			  method: 'get', parameters: params,
			  onSuccess: function(transport) {
				var myrepArr=transport.responseText.split('|');
				// Check COD
				
				/// Check Shipping Pin
				if($('advice-required-entry-'+fieldid)){
					$('advice-required-entry-'+fieldid).remove();
					$(fieldid).removeClassName('input-text required-entry validation-failed');
					$(fieldid).addClassName('input-text required-entry');
				}
				if(myrepArr[0]==0)
				{
					alert("We do not deliver to this location currently.\nPlease try alternate address or contact customer care.");
					$(fieldid).focus();
					$(fieldid).removeClassName('input-text required-entry');
					$(fieldid).addClassName('input-text required-entry validation-failed');
					if($('advice-required-entry-'+fieldid)){$('advice-required-entry-'+fieldid).remove();}
					$(fieldid).insert({ 'after' : '<div id="advice-required-entry-'+fieldid+'" class="validation-advice" style="">We do not deliver to this location currently.</div>' });
					if((fieldid) && fieldid=="billing:postcode")
					{
						$(fieldid).value="";
						if($("billing-address-select").value!="")
						{
							$("billing-address-select").value="";
							$("billing-new-address-form").show();
						}
					}
					if((fieldid) && fieldid=="shipping:postcode")
					{
						$(fieldid).value="";
					}
				}
				if(myrepArr[1]=='0')
				{
					$('input_checkmo').hide();
					$('label_checkmo').update('<span style="color:#F00;">We do not offer Cash On Delivery  services in your location currently.</span>');
				}
			  },
			  onFailure: function() {
				alert("Sorry..! Not getting ajax response.");
			  }
			});
		}
	}
