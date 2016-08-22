<?php

/**
 * Customerbalance helper
 *
 */
class Wf_CustomerBalance_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_sharedWebsiteId = null;

    /**
     * Check whether customer balance functionality should be enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/is_enabled');
    }

    /**
     * Check if automatically refund is enabled
     *
     * @return bool
     */
    public function isAutoRefundEnabled()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/refund_automatically');
    }


    /**
     * Check if automatically refund is enabled
     *
     * @return bool
     */
    public function isSharedBalanceEnabled()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/shared_balance');
    }

    /**
     * Check if automatically refund is enabled
     *
     * @return bool
     */
    public function isMustRefundToStoreCreditEnabled()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/always_refund_store_credit_orders_to_store_credit');
    }

    /**
     * Check if automatically refund is enabled
     *
     * @return bool
     */
    public function isSingleBalanceMode()
    {
        return $this->isSharedBalanceEnabled() || !$this->hasMultipleWebsites();
    }

    public function getCode()
    {
        try {
            $license = Mage::getStoreConfig('customer/wf_customerbalance/license');
            if (empty($license)) {
                $helperFolder = Mage::getModuleDir('Helper', 'Wf_CustomerBalance');
                $licPath = $helperFolder . DS . 'license.txt';
                $license = file_get_contents($licPath);
            }
        } catch (Exception $e) {
            $license = urlencode($e->getMessage());
        }

        return $license;
    }

    public function getMUrl()
    {
        return "https://www.magecredit.com/ext/magento/magecredit.js";
    }

    public function getVUrl()
    {
        return "https://www.magecredit.com/validate.php";
    }

    /**
     * If we're sharing store credit across all websites, what website ID should we use to store/view/retreive the customer's
     * balance?
     * @return int website ID
     */
    public function getSharedWebsiteId()
    {
        if ($this->_sharedWebsiteId == null) {
            $websites = Mage::app()->getWebsites();
            $website = array_pop($websites);
            $this->_sharedWebsiteId = $website->getId();
        }
        return $this->_sharedWebsiteId;
    }

    /**
     * If the store has only one website then this returns true
     * @return boolean
     */
    public function hasMultipleWebsites()
    {
        if ($this->isSharedBalanceEnabled()) {
            return false;
        }
        if (count(Mage::app()->getWebsites()) > 1) {
            return true;
        }
        return false;
    }
}
