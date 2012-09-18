<?php

class Centerax_Akismet_Block_Adminhtml_Akismet_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_akismet';
        $this->_blockGroup = 'akismet';
		$this->_mode = 'view';

        parent::__construct();

		$this->_removeButton('save');
		$this->_removeButton('reset');

        if ((int)$this->getSpam()->getStatus() === Centerax_Akismet_Model_Api::UNREAD_STATUS) {
            $this->_addButton('read', array(
                'label'     => Mage::helper('akismet')->__('Mark as Read'),
                'onclick'   => 'deleteConfirm(\''. Mage::helper('akismet')->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getMarkReadUrl() . '\')',
            ));

	        if ($this->getSpam()->getType() == 'review') {
	            $this->_addButton('convert', array(
	                'label'     => Mage::helper('akismet')->__('Convert To Review'),
	                'onclick'   => 'deleteConfirm(\''. Mage::helper('akismet')->__('Are you sure you want to do this?')
	                    .'\', \'' . $this->getConvertUrl() . '\')',
	            ));
	        }

        }

    }

    public function getConvertUrl()
    {
        return $this->getUrl('*/*/convertToReview', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    public function getMarkReadUrl()
    {
        return $this->getUrl('*/*/markread', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

	public function getSpam()
	{
		return Mage::registry('akismet_spam');
	}

    public function getHeaderText()
    {
        return Mage::helper('akismet')->__('Viewing SPAM');
    }
}