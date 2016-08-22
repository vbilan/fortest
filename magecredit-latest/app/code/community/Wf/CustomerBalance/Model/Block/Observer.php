<?php

class Wf_CustomerBalance_Model_Block_Observer extends Varien_Object
{

    /**
     * Customer balance instance
     *
     * @var Wf_CustomerBalance_Model_Balance
     */
    protected $_balanceModel = null;
    
    /**
     * Executed from the core_block_abstract_to_html_after event
     * @param Varien_Event $observer
     * @return $this
     */
    public function afterOutput($observer)
    {

        try {

            $block = $observer->getEvent()->getBlock();

            if (!$this->_isOpcThatNeedsIntegration($block)) {
                return $this;
            }

            if(!$this->needToShowBalance()) {
                return $this;
            }

            // Get output from _toHtml()
            $normalOutput = $observer->getTransport()->getHtml();

            $customerBalanceHtml = $this->_genCustomerBalanceBlockHtml($block);

            if (empty($customerBalanceHtml)) {
                return $this; // couldn't render customer balance block
            }

            if (strpos($normalOutput, $customerBalanceHtml) !== false) {
                return $this; // already there
            }


            $insertBeforeTag = '<dl id="checkout-payment-method-load"';
            if(stripos($normalOutput, $insertBeforeTag) === false) {
                $insertBeforeTag = '<fieldset id="checkout-payment-method-load"';
            }
            $newOutput = str_ireplace($insertBeforeTag, $customerBalanceHtml . $insertBeforeTag, $normalOutput);

            $observer->getTransport()->setHtml($newOutput);
        } catch (Exception $e) {
            // Mage::logException($e);
        }
        return $this;

    }

    /**
     * Generates either the child 'customerbalance' html or the dynamically rendered HTML
     * @param  Mage_Checkout_Block_Onepage_Payment_Methods $block 
     * @return string HTML to append to the payments block
     */
    protected function _genCustomerBalanceBlockHtml($block)
    {
        $customerBalanceHtml = $block->getChildHtml('customerbalance');
        if(!empty($customerBalanceHtml)) {
            return $customerBalanceHtml;
        }

        $customerBalanceBlock = $block->getLayout()->createBlock('wf_customerbalance/checkout_onepage_payment_additional');
        $customerBalanceBlock->setName('customerbalance');

        if ($block->getTemplate() == 'onestepcheckout/payment_method.phtml') {
            $customerBalanceBlock->setTemplate('customerbalance/idev_onestepcheckout/payment/additional.phtml');
        } else {
            $customerBalanceBlock->setTemplate('customerbalance/checkout/onepage/payment/additional.phtml');
        }
        $customerBalanceHtml = $customerBalanceBlock->renderView();


        if ($block->getTemplate() != 'onestepcheckout/payment_method.phtml') { // Not idev osc
            $customerBalanceScriptsBlock = $block->getLayout()->createBlock('wf_customerbalance/checkout_onepage_payment_additional');
            $customerBalanceScriptsBlock->setName('customerbalance_scripts');
            $customerBalanceScriptsBlock->setTemplate('customerbalance/idev_onestepcheckout/payment/scripts.phtml');
            $customerBalanceHtml = "<script>". $customerBalanceScriptsBlock->renderView() . "</script>" . $customerBalanceHtml;
        }

        return $customerBalanceHtml;
    }

    /**
     * Should we auto-integrate? In the future, when we auto-integrate into every payment
     * block this method will have less template checks. For now however, it only turns on
     * when we are using certain One Step Checkout extensions.
     * @param  Mage_Core_Block_Abstract $block  block
     * @return boolean                          true if you should auto-integrate 
     */
    protected function _isOpcThatNeedsIntegration($block)
    {
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods) {
            if ($block->getTemplate() == 'onestepcheckout/payment_method.phtml') {
                return true;
            }
            if ($block->getTemplate() == 'opcheckout/onepage/payment/methods.phtml') {
                return true;
            }
        }
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment) {
            if ($block->getTemplate() == 'opc/onepage/payment.phtml') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Can display customer balance container
     *
     * @return bool
     */
    public function needToShowBalance()
    {
        if (!$this->_getCustomer()->getId()) {
            return false;
        }

        if (!$this->getBalance()) {
            return false;
        }

        return true;
    }

    /**
     * Get balance amount
     *
     * @return float
     */
    public function getBalance()
    {
        if (!$this->_getCustomer()->getId()) {
            return 0;
        }
        return $this->_getBalanceModel()->getAmount();
    }

    /**
     * Get balance instance
     *
     * @return Wf_CustomerBalance_Model_Balance
     */
    protected function _getBalanceModel()
    {
        if (is_null($this->_balanceModel)) {
            $this->_balanceModel = Mage::getModel('wf_customerbalance/balance')
                ->setCustomer($this->_getCustomer())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            //load customer balance for customer in case we have
            //registered customer and this is not guest checkout
            if ($this->_getCustomer()->getId()) {
                $this->_balanceModel->loadByCustomer();
            }
        }
        return $this->_balanceModel;
    }

}