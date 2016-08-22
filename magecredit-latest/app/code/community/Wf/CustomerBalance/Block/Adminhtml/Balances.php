<?php

class Wf_CustomerBalance_Block_Adminhtml_Balances extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'wf_customerbalance';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_balances';

    public function __construct()
    {
        $this->_headerText = Mage::helper('customer')->__('Customer Store Credit Balances (Magecredit)');
        parent::__construct();
        $this->_removeButton('add');
        return $this;
    }


    public function getGridHtml()
    {
        return $this->getChildHtml('stats') . parent::getGridHtml();
    }


    protected function _toHtml()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('customer/storecredit/view')) {
            return '';
        }
        return parent::_toHtml();
    }

}
