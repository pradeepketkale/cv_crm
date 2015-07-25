<?php 
session_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

   $header = getHeader();
    $data= '<u><b>Customer Data<br><br>
                            <div>
                                    <table width="100%" border="1" cellspacing="0" cellpadding="5">';
    $data .= $header;
    $data .= getCustomerData();
    $data .= '</table></div><br>';
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=file.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $data;

function getHeader()
{
   return '<tr>
                <th width="15%">Customer Name</th>
                <th width="25%">Email</th>
                <th width="13%">Date Of Last Transaction</th>
                <th width="15%">Value Of Transaction</th>
                <th width="10%">Discount Value</th>
                <th width="10%">City</th>
          </tr>';
}


function getCustomerData()
{
   $orders=Mage::getModel('sales/order')->getCollection();
            
            foreach($orders as $order)
            {     
                $customerId=$order->getCustomerId();
                $firstName=$order->getCustomerFirstname();
                $email=$order->getCustomerEmail();
                $dateLastTrans=$order->getCreatedAt();
                $valueOfTrans=$order->getGrandTotal();
                $discountAmount=$order->getDiscountAmount();
                $billAdd=$order->getBillingAddressId();
                
                $data .= '<tr>
                    <td>'.$firstName.'</td>
                    <td>'.$email.'</td>
                    <td>'.$dateLastTrans.'</td>
                    <td>'.$valueOfTrans.'</td>
                    <td>'.$discountAmount.'</td>
                    ';
             
                $custData=Mage::getModel('customer/address')->load($billAdd);
                if($custData->getCity())
                {
                    $city=$custData->getCity();
                }
                else
                {
                
                    $city='-';
                }
                $data .= '<td>'.$city.'</td></tr>';
            }
    
   return $data;
}
?>