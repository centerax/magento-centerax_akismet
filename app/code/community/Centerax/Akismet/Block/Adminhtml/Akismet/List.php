<?php

class Centerax_Akismet_Block_Adminhtml_Akismet_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'akismet';
        $this->_controller = 'adminhtml_akismet';
        $this->_headerText = Mage::helper('akismet')->__('SPAM');

        parent::__construct();
        $this->removeButton('add');
    }

}