<?php


class Wf_CustomerBalance_VoucherController extends Mage_Core_Controller_Front_Action
{

    /**
     * Redeems a voucher for the currently logged in customer.
     */
    public function redeemAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        if (!($data = $this->getRequest()->getPost())) {
            return $this->_redirectError(Mage::getUrl('wf_customerbalance/info'));
        }


        try {
            $voucher = Mage::getModel('wf_customerbalance/voucher')->loadByCode($data['code']);
            if (!$voucher->getId()) {
                $errMsg = Mage::helper('wf_customerbalance')->__("Voucher with code '%s' was not found.", $data['code']);
                throw new Exception($errMsg);
            }

            $creditValue = $voucher->getAmount();
            $customer = $this->_getSession()->getCustomer();
            $formattedCreditValue = Mage::helper('core')->formatCurrency($creditValue);

            $voucher->redeem($customer);

            $successMsg = Mage::helper('wf_customerbalance')->__('Voucher was successfully redeemed and your account has been credited with %s.', $formattedCreditValue);
            $this->_getSession()->addSuccess($successMsg);
            $this->_redirectSuccess(Mage::getUrl('wf_customerbalance/info', array('_secure'=>true)));
            return;
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Sorry, we are unable to redeem this voucher code.'));
        }
        return $this->_redirectError(Mage::getUrl('wf_customerbalance/info'));
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }


    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }


}