<?php

class Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance extends Mage_Adminhtml_Block_Template
{

    protected $_balance = null;

    /**
     * @deprecated after 1.3.2.3
     *
     * @return int
     */
    public function getOneBalanceTotal()
    {
        return 0;
    }

    /**
     * @deprecated after 1.3.2.3
     *
     * @return bool
     */
    public function shouldShowOneBalance()
    {
        return false;
    }

    protected function _getBalance()
    {
        if ($this->_balance != null) {
            return $this->_balance;
        }

        $collection = Mage::getModel('wf_customerbalance/balance')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));

        $this->_balance = $collection;

        return $this->_balance;
    }


    /**
     * Check if we should only show a single balance
     *
     * @return bool
     */
    public function isSingleBalanceMode()
    {
        return Mage::helper('wf_customerbalance')->isSingleBalanceMode();
    }


    /**
     * If the store has only one website then this returns true
     * @return boolean
     */
    public function hasMultipleWebsites()
    {
        return Mage::helper('wf_customerbalance')->hasMultipleWebsites();
    }

    public function getSingleCreditAmount()
    {
        $amount = $this->_getBalance()->getFirstItem()->getAmount();
        $displayAmount = Mage::app()->getStore()->formatPrice($amount);
        return $displayAmount;
    }

    /**
     * Get delete orphan balances button
     *
     * @return string
     */
    public function getDeleteOrphanBalancesButton()
    {
        $customer = Mage::registry('current_customer');
        $balance = Mage::getModel('wf_customerbalance/balance');
        if ($balance->getOrphanBalancesCount($customer->getId()) > 0) {
            return $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label' => Mage::helper('wf_customerbalance')->__('Delete Orphan Balances'),
                'onclick' => 'setLocation(\'' . $this->getDeleteOrphanBalancesUrl() . '\')',
                'class' => 'scalable delete',
            ))->toHtml();
        }
        return '';
    }

    /**
     * Get delete orphan balances url
     *
     * @return string
     */
    public function getDeleteOrphanBalancesUrl()
    {
        return $this->getUrl('*/customerbalance/deleteOrphanBalances', array('_current' => true, 'tab' => 'customer_info_tabs_customerbalance'));
    }
}
