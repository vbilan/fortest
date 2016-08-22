<?php

/**
 *
 */
class Wf_CustomerBalance_DebugController extends Mage_Core_Controller_Front_Action
{

    /**
     *
     */
    public function infoAction()
    {

        $install_version = Mage::getConfig()->getNode('modules/Wf_CustomerBalance/version');
        $mageVersion = Mage::getEdition() . " " . Mage::getVersion();
        echo "
        <PRE>
            Running Magecredit {$install_version}.
            Running Magento {$mageVersion}.
        </PRE>
        ";
        return $this;
    }


    public function checkallAction()
    {
        $this->checkCompilationAction();
        echo "<BR><BR>\n\n";
        $this->checkOSCAction();
        echo "<BR><BR>\n\n";
        $this->checkDesignsAction();
        echo "<BR><BR>\n\n";
        $this->checkRewritesAction();
        echo "<BR><BR>\n\n";
        $this->checkConfigAction();
        echo "<BR><BR>\n\n";

        return $this;
    }


    public function checkConfigAction()
    {
        echo ' No config issues? ' . Mage::helper('wf_customerbalance/debug')->checkConfig($this->getRequest()->getParam('verbose', true));
        return $this;
    }


    public function checkRewritesAction()
    {
        echo ' No class rewrite issues? ' . Mage::helper('wf_customerbalance/debug')->checkRewrites($this->getRequest()->getParam('verbose', true));
        return $this;
    }

    public function checkCompilationAction()
    {
        echo ' No Compilation issues? ' . Mage::helper('wf_customerbalance/debug')->checkCompilation($this->getRequest()->getParam('verbose', true));
        return $this;
    }

    public function checkOSCAction()
    {
        echo " No One Step Checkout issues? " . Mage::helper('wf_customerbalance/debug')->checkOSC($this->getRequest()->getParam('verbose', true));
        return $this;
    }

    public function checkDesignsAction()
    {
        echo " No design issues issues? " . Mage::helper('wf_customerbalance/debug')->checkDesigns($this->getRequest()->getParam('verbose', true));
        return $this;
    }


    /**
     * This controller action will remove the database install entry from the Magento
     * core_resource table. This in turn will force Magento to re-install the database scripts.
     */
    public function reinstalldbAction()
    {
        echo "Deleting core_resource table entries that have the code 'wf_customerbalance_setup'...<br>";
        flush();

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->beginTransaction();

        $this->_clearDbInstallMemory($conn, 'wf_customerbalance_setup');

        echo "Done.";
        flush();

        $conn->commit();

        echo "<br><br>\n"
            . "<a href='" . Mage::getUrl('adminhtml/notification') . "'>CLICK HERE</a> "
            . "to go back to the dashboard and module will retun it's own database install scripts over again ";

        exit;

    }

    protected function _clearDbInstallMemory($conn, $code)
    {
        $table_prefix = Mage::getConfig()->getTablePrefix();

        $conn->query("
            DELETE FROM    `{$table_prefix}core_resource`
            WHERE    `code` = '{$code}'
            ;
        ");
        echo "Resource DB for {$code} has been cleared<br>";

        return $this;
    }

    public function testAddAction()
    {
        $firstCustomerId = Mage::getModel('customer/customer')->getCollection()->getFirstItem()->getId();

        $balance = Mage::getModel('wf_customerbalance/balance')
            ->setCustomerId($firstCustomerId)
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setAmountDelta(123.00)
            ->setUpdatedActionAdditionalInfo("This was a test to see how it easy it is to add store credit to a customer's account."); // This field is optional but recomemnded.
        
        $balance->save();

        return $this;
    }
    public function testDeductAction()
    {
        $firstCustomerId = Mage::getModel('customer/customer')->getCollection()->getFirstItem()->getId();

        $balance = Mage::getModel('wf_customerbalance/balance')
            ->setCustomerId($firstCustomerId)
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setAmountDelta(-123.00)
            ->setUpdatedActionAdditionalInfo("This was a test to see how it easy it is to deduct store credit to a customer's account."); // This field is optional but recomemnded.
        
        $balance->save();

        return $this;
    }


    protected function _preDispatch()
    {
        if ($this->getRequest()->getParam('code') != Mage::helper('wf_customerbalance')->getCode()) {
            die("invalid code.");
        }
        return parent::_preDispatch();
    }
}
