<?php

class Wf_CustomerBalance_Block_Adminhtml_Promo_Voucher_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('vouchersGrid');
        $this->setDefaultSort('voucher_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('wf_customerbalance/voucher')->getCollection();

        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('voucher_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'voucher_id'
        ));

        $this->addColumn('amount', array(
            'header' => Mage::helper('sales')->__('Amount'),
            'index' => 'amount',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));
        
        $this->addColumn('code', array(
            'header' => $this->__('Voucher Code'),
            'align' => 'left',
            'index' => 'code'
        ));


        $this->addColumn('has_been_redeemed', array(
            'header'    => $this->__('Has been redeeemd?'),
            'type'      => 'options',
            'options'   => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
            ),
            'index'     => 'has_been_redeemed',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }

    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('voucher_id');
        $this->getMassactionBlock()->setFormFieldName('vouchers');
        
        $this->getMassactionBlock()->addItem('delete', array (
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
        
        return $this;
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
