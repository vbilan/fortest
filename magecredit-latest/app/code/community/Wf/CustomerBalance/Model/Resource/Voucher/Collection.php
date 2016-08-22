<?php


/**
 * Magecredit voucher collection.
 *
 * @category    Wellfounded
 * @author      Magecredit Team <hi@magecredit.com>
 */
class Wf_CustomerBalance_Model_Resource_Voucher_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_didSelectCustomerName = false;
    /**
     * Initialize resource.
     */
    protected function _construct()
    {
        $this->_init('wf_customerbalance/voucher');
    }

    /**
     * Filter collection by specified websites.
     *
     * @param array|int $websiteIds
     *
     * @return Wf_CustomerVoucher_Model_Resource_Voucher_Collection
     */
    public function addWebsitesFilter($websiteIds)
    {
        $this->getSelect()->where('main_table.website_id IN (?)', $websiteIds);

        return $this;
    }

}
