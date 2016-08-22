<?php

/**
 * Customer balance observer
 *
 */
class Wf_CustomerBalance_Model_Checkout_Cart_Observer
{
    /**
     * Prepare customer balance POST data
     *
     * @param Varien_Event_Observer $o
     * @return $this
     */
    public function cartPredispatch($o)
    {
        try {
            if (!Mage::helper('wf_customerbalance')->isEnabled()) {
                return $this;
            }

            // We've already notified, so don't notify maybe?
            if (Mage::getSingleton('customer/session')->getHasNotifiedCustomerOfBalance()) {
                return $this;
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if (!$customerId) {
                return $this;
            }

            $custBalance = Mage::getModel('wf_customerbalance/balance')
                ->setCustomerId($customerId)
                ->loadByCustomer();

            // if no balance, peace out.
            if ($custBalance->getAmount() <= 0) {
                return $this;
            }


            $balanceStr = Mage::helper('core')->currency($custBalance->getAmount());
            Mage::getSingleton('core/session')->addNotice(
                Mage::helper('wf_customerbalance')->__("We've noticed that you have a <strong>%s</strong> store credit with us. You can choose to spend your store credit in the payment step of your checkout.", $balanceStr)
            );


            Mage::getSingleton('customer/session')->setHasNotifiedCustomerOfBalance(true);


        } catch (Exception $e) {
            Mage::logException($e);
        }


        return $this;
    }

}
