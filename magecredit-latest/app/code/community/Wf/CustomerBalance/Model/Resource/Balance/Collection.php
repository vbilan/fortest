<?php


/**
 * Magecredit balance collection
 *
 * @category    Wellfounded
 * @package     Wf_CustomerBalance
 * @author      Magecredit Team <hi@magecredit.com>
 */
class Wf_CustomerBalance_Model_Resource_Balance_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('wf_customerbalance/balance');
    }

    /**
     * Filter collection by specified websites
     *
     * @param array|int $websiteIds
     * @return Wf_CustomerBalance_Model_Resource_Balance_Collection
     */
    public function addWebsitesFilter($websiteIds)
    {
        if (Mage::helper('wf_customerbalance')->isSharedBalanceEnabled()) {
            $websiteIds = Mage::helper('wf_customerbalance')->getSharedWebsiteId();
        }
        $this->getSelect()->where('main_table.website_id IN (?)', $websiteIds);
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
            $this->getSelect()->where('main_table.website_id = (?)', $websiteId);
        }
        return parent::_beforeLoad();
    }

    /**
     * Implement after load logic for each collection item
     *
     * @return Wf_CustomerBalance_Model_Resource_Balance_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->walk('afterLoad');
        return $this;
    }
}
