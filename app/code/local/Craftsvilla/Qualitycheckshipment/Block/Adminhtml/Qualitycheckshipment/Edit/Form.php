<?php

class Craftsvilla_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {

	$id     = $this->getRequest()->getParam('id'); //shipment entityid
	$model  = Mage::getModel('sales/order_shipment_item')->load($id,'parent_id');
	
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );

      $form->setUseContainer(true);
      $this->setForm($form);

$_product = Mage::getModel('catalog/product')->load($model->getProductId());
?>
<table cellspacing="1" cellpadding="0" border = "1" class="data-table table-gride vtop">
      <col width="" />
      <col width="" />
      <!--<col width="100" />
        <col width="80" />-->
      <col width="100" />
      <col width="70" />
      <thead>
        <tr>
          <th>Image</th>
          <th><?php echo $this->__('SKU')?></th>
          <th><?php echo $this->__('Product Name')?></th>
          <th><?php echo $this->__('Product Price')?></th>
          <th><?php echo $this->__('Qty')?></th>
        </tr>
      </thead>
      <tbody>
		<tr>
		<td><a href="<?php echo Mage::helper('catalog/image')->init($_product, 'image')->resize(150, 150); ?>" target="_blank">
		<img src="<?php echo Mage::helper('catalog/image')->init($_product, 'image')->resize(150, 150); ?>" alt="<?php echo $this->htmlEscape($_product['name']); ?>" border="0" width="150" /></a></td>
		<td><?php echo $model->getSku();?></td>
		<td><?php echo $model->getName();?></td>
		<td><?php echo $model->getPrice();?></td>
		<td><?php echo $model->getQty();?></td>
		</tr>
		</tbody>
		</table>
<?php
      return parent::_prepareForm();
  }
}
