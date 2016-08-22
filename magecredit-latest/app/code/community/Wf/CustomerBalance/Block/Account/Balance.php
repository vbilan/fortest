<?php

/**
 * Customer balance block
 *
 */
class Wf_CustomerBalance_Block_Account_Balance extends Mage_Core_Block_Template
{
    /**
     * Retreive current customers balance in base currency
     *
     * @return float
     */
    public function getBalance()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return 0;
        }

        $model = Mage::getModel('wf_customerbalance/balance')
            ->setCustomerId($customerId)
            ->loadByCustomer();

        return $model->getAmount();
    }
}
