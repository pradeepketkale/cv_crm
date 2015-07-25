function menu(val)
{
if (val==0)
{
  document.getElementById('fragment-1').className="ui-tabs-panel displayblock"
  document.getElementById('fragment-2').className="displaynone"
  document.getElementById('tabone').className="ui-tabs-nav-item selected"
  document.getElementById('tabtwo').className="ui-tabs-nav-item"
  
}

else if (val==1)
{
  document.getElementById('fragment-1').className="displaynone"
  document.getElementById('fragment-2').className="ui-tabs-panel displayblock"
  document.getElementById('tabtwo').className="ui-tabs-nav-item selected"
  document.getElementById('tabone').className="ui-tabs-nav-item"

  
}


/*else if (val==5)
{
  document.getElementById('Home').className=""
  document.getElementById('Product').className=""
  document.getElementById('Buyonline').className=""
  document.getElementById('Faq').className=""
  document.getElementById('hypBathroomVanity').className=""
  document.getElementById('hypSignatureColl').className="selected"
  
}*/


}

