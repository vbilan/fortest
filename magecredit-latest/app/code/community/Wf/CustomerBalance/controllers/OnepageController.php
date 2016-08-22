<?php

/**
 * Customer balance controller for onepage checkout functions
 *
 */
class Wf_CustomerBalance_OnepageController extends Mage_Core_Controller_Front_Action
{

    /**
     * Add Store Credit from current quote
     *
     */
    public function ajaxToggleAction()
    {
        if (!Mage::helper('wf_customerbalance')->isEnabled()) {
            return;
        }
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();

        try {
            $paymentParam = $this->getRequest()->getParam('payment', array());
            if(isset($paymentParam['use_customer_balance']) && $paymentParam['use_customer_balance']) {
                $shouldUseBalance = true;
            } else {
                $shouldUseBalance = false;
            }

            $quote = $this->getOnepage()->getQuote();
            $store = Mage::app()->getStore($quote->getStoreId());
            $payment = $quote->getPayment();

            if (!$quote || !$quote->getCustomerId()
                || $quote->getBaseGrandTotal() + $quote->getBaseCustomerBalanceAmountUsed() <= 0
            ) {
                throw Mage_Core_Exception("Customer not logged in or quote not found.");
            }


            if ($shouldUseBalance) {
                $balance = Mage::getModel('wf_customerbalance/balance')
                    ->setCustomerId($quote->getCustomerId())
                    ->setWebsiteId($store->getWebsiteId())
                    ->loadByCustomer();
                if ($balance) {
                    $quote->setCustomerBalanceInstance($balance);
                    if (!$payment->getMethod()) {
                        $payment->setMethod('free');
                    }
                }
            }
            $quote->setUseCustomerBalance($shouldUseBalance);

            $quote->save();
            $quote->collectTotals();
            $quote->save();
            $result['success'] = true;

        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
            $result['success'] = false;
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
            $result['success'] = false;
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to save store credit usage for this checkout. Please contact our support team for help.');
            $result['success'] = false;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


    /**
     * Send Ajax redirect response
     *
     * @return Mage_Checkout_OnepageController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }
    
    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

}
