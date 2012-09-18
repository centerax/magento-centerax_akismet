<?php

class Centerax_Akismet_Block_Adminhtml_Akismet_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('akismet_spam');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('akismet/akismet_akismet')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();

    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'=> Mage::helper('akismet')->__('ID'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'id',
        ));
        $this->addColumn('type', array(
            'header' => Mage::helper('akismet')->__('Type'),
            'index' => 'type',
            'type'  => 'options',
            'options' => $this->getTypeOptions(),
            'width' => '300px',
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('akismet')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => $this->getStatusesOptions(),
            'width' => '300px',
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('akismet')->__('Created At'),
            'index' => 'created_at',
            'type' => 'text',
            'width' => '300px',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('akismet')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('akismet')->__('Delete'),
                        'url'     => array('base'=>'*/*/delete'),
                        'field'   => 'id',
                        'confirm'   => Mage::helper('akismet')->__('Are you sure you want to do this?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array(
            'id' => $row->getId())
        );
    }

    protected function _prepareMassaction(){
		
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('spam');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/listGrid', array('_current'=>true));
    }

	public function getTypeOptions()
	{
		return array('contact' => $this->__('Contact Us Form'), 'review' => $this->__('Product Review'));
	}

	public function getStatusesOptions()
	{
		return array(1 => $this->__('Unread'), 2 => $this->__('Read'));
	}
}
