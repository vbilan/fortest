<?php

/**
 * Customer balance history grid
 */
class Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance_History_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Wf_CustomerBalance_Model_Mysql4_Balance_Collection
     */
    protected $_collection;

    /**
     * Initialize some params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('historyGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('updated_at');
    }

    /**
     * Prepare grid collection
     *
     * @return Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance_History_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('wf_customerbalance/balance_history')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance_History_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('wf_customerbalance')->__('Date'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'filter' => false,
            'width' => 200,
        ));

        $this->addColumn('website_id', array(
            'header' => Mage::helper('wf_customerbalance')->__('Website'),
            'index' => 'website_id',
            'type' => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
            'sortable' => false,
            'width' => 200,
        ));

        $this->addColumn('balance_action', array(
            'header' => Mage::helper('wf_customerbalance')->__('Action'),
            'width' => 70,
            'index' => 'action',
            'sortable' => false,
            'type' => 'options',
            'options' => Mage::getSingleton('wf_customerbalance/balance_history')->getActionNamesArray(),
        ));

        $this->addColumn('balance_delta', array(
            'header' => Mage::helper('wf_customerbalance')->__('Balance Change'),
            'width' => 50,
            'index' => 'balance_delta',
            'type' => 'price',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'wf_customerbalance/adminhtml_widget_grid_column_renderer_currency',
        ));

        $this->addColumn('balance_amount', array(
            'header' => Mage::helper('wf_customerbalance')->__('Balance'),
            'width' => 50,
            'index' => 'balance_amount',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'wf_customerbalance/adminhtml_widget_grid_column_renderer_currency',
        ));

        $this->addColumn('is_customer_notified', array(
            'header' => Mage::helper('wf_customerbalance')->__('Customer notified?'),
            'index' => 'is_customer_notified',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('wf_customerbalance')->__('Notified'),
                '0' => Mage::helper('wf_customerbalance')->__('No'),
            ),
            'sortable' => false,
            'filter' => false,
            'width' => 75,
        ));

        $this->addColumn('additional_info', array(
            'header' => Mage::helper('wf_customerbalance')->__('Additional information'),
            'index' => 'additional_info',
            'sortable' => false,
            'renderer' => 'wf_customerbalance/adminhtml_widget_grid_column_renderer_additionalinfo',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click callback
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridHistory', array('_current' => true));
    }
}
