<?php

class Wf_CustomerBalance_Block_Adminhtml_Balances_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerBalancesGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('amount', 'DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email');

        $collection->joinTable(
            array('scb' => 'wf_customerbalance/balance'),
            'customer_id=entity_id',
            array(
                'amount' => 'amount',
                'base_currency_code' => 'base_currency_code',
                'balance_id' => 'balance_id'
            ),
            null,
            'inner'
        );

        $collection->addFilter('`scb`.`website_id`', '`scb`.`website_id` = `e`.`website_id`', 'string');

        if (Mage::helper('wf_customerbalance')->isSingleBalanceMode()) {
            $websiteId = Mage::helper('wf_customerbalance')->getSharedWebsiteId();
            $collection->addFilter('`scb`.`website_id`', $websiteId);
        }


        $this->setCollection($collection);


        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        if (Mage::helper('wf_customerbalance')->isSingleBalanceMode()) {
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('customer')->__('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ));
        } else {
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('customer')->__('Balance ID'),
                'width' => '50px',
                'index' => 'balance_id',
                'type' => 'number',
            ));

            $this->addColumn('customer_id', array(
                'header' => Mage::helper('customer')->__('Customer ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ));
        }

        $this->addColumn('name', array(
            'header' => Mage::helper('customer')->__('Name'),
            'index' => 'name',
            'renderer' => 'wf_customerbalance/adminhtml_widget_grid_column_renderer_customer'
        ));
        $this->addColumn('email', array(
            'header' => Mage::helper('customer')->__('Email'),
            'width' => '150',
            'index' => 'email'
        ));

        $this->addColumn('store_credit_balance', array(
            'header' => Mage::helper('customer')->__('Balance'),
            'width' => '150',
            'index' => 'amount',
            'type' => 'currency',
            'renderer' => 'wf_customerbalance/adminhtml_widget_grid_column_renderer_currency',
        ));


        if (Mage::app()->isSingleStoreMode() || !Mage::helper('wf_customerbalance')->isSingleBalanceMode()) {
            $this->addColumn('website_id', array(
                'header' => Mage::helper('customer')->__('Website'),
                'align' => 'center',
                'width' => '80px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

        // $this->addColumn('action',
        //     array(
        //         'header'    =>  Mage::helper('customer')->__('Action'),
        //         'width'     => '100',
        //         'type'      => 'action',
        //         'getter'    => 'getId',
        //         'actions'   => array(
        //             array(
        //                 'caption'   => Mage::helper('customer')->__('View/Edit'),
        //                 'url'       => array('base'=> 'adminhtml/customer/edit/'),
        //                 'field'     => 'id'
        //             )
        //         ),
        //         'filter'    => false,
        //         'sortable'  => false,
        //         'index'     => 'stores',
        //         'is_system' => true,
        // ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return parent::_prepareColumns();
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
