<?php

class Centerax_Akismet_Block_Adminhtml_Akismet_View_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('akismet_spam');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => Mage::getUrl('*/*/save'), 'method' => 'post'));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('akismet')->__('SPAM details')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

		$spamData = unserialize($model->getExtra());
		foreach($spamData as $k=>$f):

			if($k == 'comment_type')
				continue;

	        $fieldset->addField($k, ( (strlen($f)>30) ? 'textarea' : 'text' ), array(
	            'name'  => $k,
	            'label' => Mage::helper('akismet')->__( $this->helper('akismet')->slug($k) ),
	            'id'    => $k,
	            'style' => 'background:#FAFAFA;width:300px;',
	            'readonly' =>'readonly',
	            'title' => Mage::helper('akismet')->__( $this->helper('akismet')->slug($k) ),
	            'required' => false
	        ));

		endforeach;

        $form->setValues($spamData);

		$form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}