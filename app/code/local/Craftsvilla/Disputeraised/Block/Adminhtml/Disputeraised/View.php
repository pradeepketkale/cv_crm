<?php 
class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_View extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		
		$getData = $row->getData();
		$id = $getData['increment_id'];
		$collection = Mage::getModel('disputeraised/disputeraised')->addFieldToFilter('increment_id', $filename);
		$html = '';
		foreach($collection as $_collection)
		{
		  $html .= '<div><table border="1"><tr><th><ID></th><th>Customer Name</th><th>Image</th><th>Content</th></tr><tr><td>'.$id.'</td><td>'
		  .$getData['customer_id'].'</td><td>'.$getData['image'].'</td><td>'.$getData['content'].'</td></tr>';
	    
    }
    $html .= '</table>';
    return $html;
    
}
?>