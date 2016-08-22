<?php

class Wf_CustomerBalance_Block_Adminhtml_Widget_Grid_Column_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Contains a list of customers so we're not reloading duplicates in the grid
     *
     * @var array
     */
    protected $_customers = array();

    /**
     * Render the customer name and make it clickable
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $str = '';
        if ($cid = $row->getId()) {
            if ($customer = $this->_getCustomer($cid)) {
                $str = $customer->getName();
                $url = $this->getUrl('adminhtml/customer/edit/', array(
                    'id' => $cid,
                    'rback' => $this->getUrlBase64('*/*/'),
                    'tab' => 'storecredit',
                ));
                $str = '<a href="' . $url . '">' . $str . '</a>';
            }
        }

        return $str;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $name = $row->getName();
        return $name;
    }

    /**
     * Tries to load a customer from $this->_customer.
     * If not present, loads a new customer
     *
     * @param int $cid
     * @return Mage_Customer_Model_Customer|bool
     */
    protected function _getCustomer($cid)
    {
        if (isset($this->_customers[$cid])) {
            return $this->_customers[$cid];
        }

        $customer = Mage::getModel('customer/customer')->load($cid);
        if ($customer->getId()) {
            $this->_customers[$cid] = $customer;
            return $this->_customers[$cid];
        }

        return false;
    }

}