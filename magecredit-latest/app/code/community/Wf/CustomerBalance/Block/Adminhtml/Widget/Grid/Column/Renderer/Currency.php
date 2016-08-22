<?php

/**
 * Currency cell renderer for customerbalance grids
 *
 */
class Wf_CustomerBalance_Block_Adminhtml_Widget_Grid_Column_Renderer_Currency
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{
    /**
     * @var array
     */
    protected static $_websiteBaseCurrencyCodes = array();


    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $data = (string)$row->getData($this->getColumn()->getIndex());

        if (empty($data)) {
            $data = $this->getColumn()->getDefault();
        }
        if (empty($data)) {
            $data = 0.00;
        }


        $currency_code = $this->_getCurrencyCode($row);

        if (!$currency_code) {
            return $data;
        }

        $data = floatval($data) * $this->_getRate($row);
        $sign = (bool)(int)$this->getColumn()->getShowNumberSign() && ($data > 0) ? '+' : '';
        $data = sprintf("%f", $data);
        $data = Mage::app()->getLocale()->currency($currency_code)->toCurrency($data);

        return $data;
    }

    /**
     * Get currency code by row data
     *
     * @param Varien_Object $row
     * @return string
     */
    protected function _getCurrencyCode($row)
    {
        $websiteId = $row->getData('website_id');
        $orphanCurrency = $row->getData('base_currency_code');
        if ($orphanCurrency !== null) {
            return $orphanCurrency;
        }
        if (!isset(self::$_websiteBaseCurrencyCodes[$websiteId])) {
            self::$_websiteBaseCurrencyCodes[$websiteId] = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();
        }
        return self::$_websiteBaseCurrencyCodes[$websiteId];
    }

    /**
     * Stub getter for exchange rate
     *
     * @param Varien_Object $row
     * @return int
     */
    protected function _getRate($row)
    {
        return 1;
    }
}
