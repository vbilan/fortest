<?php

/**
 * Refund to customer balance functionality section in the credit memo form
 *
 */
class Wf_CustomerBalance_Block_Adminhtml_Sales_Order_Creditmemo_Controls extends Mage_Core_Block_Template
{

    public function getOrder()
    {
        return $this->getCreditMemo()->getOrder();
    }

    public function getCreditMemo()
    {
        return Mage::registry('current_creditmemo');
    }

    /**
     * Check whether refund to customerbalance is required
     *
     * @return bool
     */
    public function mustRefundToCustomerBalance()
    {
        if(!Mage::helper('wf_customerbalance')->isMustRefundToStoreCreditEnabled()) {
            return false;
        }

        if (!$this->canRefundToCustomerBalance()) {
            return false;
        }

        $storeCreditAmount = $this->getOrder()->getBaseCustomerBalanceAmount();
        if(empty($storeCreditAmount)) {
            return false;
        }

        if($storeCreditAmount < $this->getOrder()->getBaseGrandTotal()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether refund to customerbalance is available
     *
     * @return bool
     */
    public function canRefundToCustomerBalance()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('customer/storecredit/add_or_deduct')) {
            return false;
        }

        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Check whether real amount can be refunded to customer balance
     *
     * @return bool
     */
    public function canRefundMoneyToCustomerBalance()
    {
        if (!$this->getCreditMemo()->getGrandTotal()) {
            return false;
        }

        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Prepopulate amount to be refunded to customerbalance
     *
     * @return float
     */
    public function getReturnValue()
    {
        $max = $this->getCreditMemo()->getCustomerBalanceReturnMax();
        if ($max) {
            return $max;
        }
        if(Mage::helper('wf_customerbalance')->isAutoRefundEnabled()) {
            return $this->getCreditMemo()->getGrandTotal();
        }
        return 0;
    }
}
