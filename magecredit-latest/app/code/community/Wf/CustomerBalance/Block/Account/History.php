<?php

/**
 * Customer balance history block
 *
 */
class Wf_CustomerBalance_Block_Account_History extends Mage_Core_Block_Template
{
    /**
     * Balance history action names
     *
     * @var array
     */
    protected $_actionNames = null;

    /**
     * Check if history can be shown to customer
     *
     * @return bool
     */
    public function canShow()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/show_history');
    }

    /**
     * Check config to see if we should be showing the comments in the history
     *
     * @return bool
     */
    public function showCommentsInHistory()
    {
        return Mage::getStoreConfigFlag('customer/wf_customerbalance/show_comments_in_frontend_history');
    }

    /**
     * Retreive history events collection
     *
     * @return mixed
     */
    public function getEvents()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return false;
        }

        $collection = Mage::getModel('wf_customerbalance/balance_history')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addWebsitesFilter(Mage::app()->getStore()->getWebsiteId())
            ->addOrder('updated_at', 'DESC')
            ->addOrder('history_id', 'DESC');

        return $collection;
    }

    /**
     * Retreive action labels
     *
     * @return array
     */
    public function getActionNames()
    {
        if (is_null($this->_actionNames)) {
            $this->_actionNames = Mage::getSingleton('wf_customerbalance/balance_history')->getActionNamesArray();
        }
        return $this->_actionNames;
    }

    /**
     * Retreive action label
     *
     * @param mixed $action
     * @return string
     */
    public function getActionLabel($action)
    {
        $names = $this->getActionNames();
        if (isset($names[$action])) {
            return $names[$action];
        }
        return '';
    }
}
