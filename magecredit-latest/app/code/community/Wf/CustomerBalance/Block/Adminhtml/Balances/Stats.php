<?php

class Wf_CustomerBalance_Block_Adminhtml_Balances_Stats extends Mage_Core_Block_Template
{
    public function getTotalOutstandingCredit()
    {
        $currecyCode = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $total = 0;

        $table = Mage::getConfig()->getTablePrefix() . "wf_customerbalance";

        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');

        $filters = "TRUE";
        if (Mage::helper('wf_customerbalance')->isSingleBalanceMode()) {
            $filters .= " AND `website_id` = " . Mage::helper('wf_customerbalance')->getSharedWebsiteId();
        }

        $sql = "
        SELECT SUM(amount) as total FROM {$table}
        WHERE {$filters}
        ;
        ";

        $total = $conn->fetchOne($sql);

        if (empty($total)) {
            $total = 0;
        }

        $str = Mage::app()->getLocale()->currency($currecyCode)->toCurrency($total);

        return $str;
    }


}