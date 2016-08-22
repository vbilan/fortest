<?php

class Wf_CustomerBalance_Model_Voucher extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('wf_customerbalance/voucher');
    }

    public function loadByCode($code)
    {
        return $this->load($code, 'code');
    }

    /**
     * Generates a new unique (by the second)
     * Sample code: ABCD12324
     * Codes are generally under 6-10 characters.
     * @return string
     */
    public function generateNewCode()
    {
        return strtoupper(dechex(rand(0,100)) . dechex(time()));
    }

    /**
     * Gives a customer the voucher amount and 
     * marks the voucher as redeemed.
     * @param  Mage_Customer_Model_Customer $customer 
     * @param  int $websiteId  (default: currently loaded website)
     * @return Wf_CustomerBalance_Model_Voucher $this
     */
    public function redeem($customer, $websiteId=null)
    {

        if ($this->getHasBeenRedeemed()) {
            $msg = Mage::helper('wf_customerbalance')->__("This voucher has already been redeeemed.");
            throw new Exception($msg);
        }

        if ($websiteId == null) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }
        
        $msg = Mage::helper('wf_customerbalance')->__("Store credit voucher with code '%s' was redeemed.", $this->getCode());

        // Create the balance transfer
        $balance = Mage::getModel('wf_customerbalance/balance')
            ->setCustomerId($customer->getId())
            ->setWebsiteId($websiteId)
            ->setAmountDelta($this->getAmount())
            ->setUpdatedActionAdditionalInfo($msg);
        $balance->save();

        // Mark the voucher as redeemed.
        $this->setRedeemedByCustomerId($customer->getId());
        $this->setHasBeenRedeemed(true);
        $this->setRedeemedAt(now());

        return $this->save();
    }

    protected function _beforeSave()
    {
        $code = $this->getCode();
        if (empty($code)) {
            $this->setCode($this->generateNewCode());
        }
        return parent::_beforeSave();
    }
}
