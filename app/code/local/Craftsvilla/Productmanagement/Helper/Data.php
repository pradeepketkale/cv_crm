<?php

class Craftsvilla_Productmanagement_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function searchBox()
        {         

                $a .=   "<script type='text/javascript'>
                            $('.allcb').on('click', function(){
                                var childClass = $(this).attr('data-child');
                                $('.'+childClass+'').prop('checked', this.checked);
                            });
                        </script>";
        
                $a .=  '<table cellspacing="0" cellpadding="0" class="massaction">
                            <tbody style="padding:10%;">
                                <tr>
                                    <td>  
                                        <input type="checkbox" name="qualitycheck[]" value="entityId" class="allcb" data-child="chk"/><span style="color:#ea7601;">Select All</span>
                                        <span class="separator">|</span>
                                        <strong id="productmanagementGrid_massaction-count"></strong> items selected    </td>
                                    <td></td>
                                    <td></td>
                                    <td> 
                                        <input type="button" name="search" onclick="disableSelected();" style="padding:5%;background: #ffac47;  border-color: #ed6502 #a04300 #a04300 #ed6502;" value="Disable">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>    
                    <form action="" method="post">
                      <label>Select Search</label>
                        <select  name="select_search" id="select_search" required="required">
                            <option value="none">Select</option>
                            <option value="productId">Product Id</option>
                            <!--<option value="productName">Product Name</option>-->
                            <option value="vendorName">Vendor Name</option>
                             
                        </select>
                        <br><br>
                        <div class="field">
                            <input type="text" name="product_sku" id="product_sku" placeholder=" SKU, ID, Keyword "  value="" style="width:100%;">
                        </div>
                        <br>
                        <input type="button" name="search" onclick="doSearch();" value="Search">
                    </form>
                    <br>
                    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
                    <script type="text/javascript">
                            function doSearch()
                            {
                                jQuery("#loading").css("display","block");
                                var searchtext=document.getElementById("product_sku").value;
                                 var selectedSearch=document.getElementById("select_search").value;
                                //alert(searchtext);
                                      jQuery.ajax({
                                      url : "/productmanagement/index/getsearchDetail",
                                      type : "POST",
                                      data : {searchtext:searchtext,selectedSearch:selectedSearch},
                                      success : function(result){
                                        jQuery("#loading").css("display","none");
                                        $("#listProducts").html(result);
                                        // alert(result);  

                                      }
                                  });

                            }
                            function disableSelected()
                            {
                              
                                var values = [];
                                $("input[type=checkbox]:checked").each(function(){
                                    values.push(this.id);
                                      var pid= this.id;
                                
                                });
                                  var count=$(".chk:checked").size();
                                var pid = values.join();
                                //alert(count);
                                 jQuery.ajax({
                                   url : "/productmanagement/index/disableSelected",
                                   type : "POST",
                                   data : {pid:pid,count:count},
                                   success : function(result){
                                   // $("#listProducts").html(result);
                                       // $("#count").html(result); 
                                    alert(result);   
                                }
                               });
                           }
                   </script>';
                $a .='<br><br><center><div id="loading" style="display:none;"><img src="/images/loading.gif"></center></div><div id="listProducts"></div>';
                echo $a; 
        }
 
  
}
