<?php

class Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance_History extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('wf/customerbalance/balance/history.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('wf_customerbalance/adminhtml_customer_edit_tab_customerbalance_balance_history_grid', 'customer.balance.history.grid'));
        return parent::_prepareLayout();
    }
}
