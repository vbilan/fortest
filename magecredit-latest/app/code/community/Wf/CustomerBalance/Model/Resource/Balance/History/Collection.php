<?php


/**
 * Magecredit Balance history collection
 *
 * @category    Wellfounded
 * @package     Wf_CustomerBalance
 * @author      Magecredit Team <hi@magecredit.com>
 */
class Wf_CustomerBalance_Model_Resource_Balance_History_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('wf_customerbalance/balance_history');
    }

    /**
     * Instantiate select joined to balance
     *
     * @return Wf_CustomerBalance_Model_Resource_Balance_History_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinInner(array('b' => $this->getTable('wf_customerbalance/balance')),
                'main_table.balance_id = b.balance_id', array('customer_id' => 'b.customer_id',
                    'website_id' => 'b.website_id',
                    'base_currency_code' => 'b.base_currency_code'));
        return $this;
    }

    /**
     * Filter collection by specified websites
     *
     * @param array|int $websiteIds
     * @return Wf_CustomerBalance_Model_Resource_Balance_History_Collection
     */
    public function addWebsitesFilter($websiteIds)
    {
        if (Mage::helper('wf_customerbalance')->isSharedBalanceEnabled()) {
            $websiteIds = Mage::helper('wf_customerbalance')->getSharedWebsiteId();
        }
        $this->getSelect()->where('b.website_id IN (?)', $websiteIds);
        return $this;
    }

    /**
     * Implement after load logic for each collection item
     *
     * @return Wf_CustomerBalance_Model_Resource_Balance_Collection
     */
    protected function _beforeLoad()
    {
        if (Mage::helper('wf_customerbalance')->isSharedBalanceEnabled()) {
            $websiteId = Mage::helper('wf_customerbalance')->getSharedWebsiteId();
            $this->getSelect()->where('b.website_id = (?)', $websiteId);
        }
        return parent::_beforeLoad();
    }

    /**
     * Retrieve history data
     *
     * @param  string $customerId
     * @param string|null $websiteId
     * @return Wf_CustomerBalance_Model_Resource_Balance_History_Collection
     */
    public function loadHistoryData($customerId, $websiteId = null)
    {
        $this->addFieldToFilter('customer_id', $customerId)
            ->addOrder('updated_at', 'DESC')
            ->addOrder('history_id', 'DESC');
        if (!empty($websiteId)) {
            $this->getSelect()->where('b.website_id IN (?)', $websiteId);
        }
        return $this;
    }
}
