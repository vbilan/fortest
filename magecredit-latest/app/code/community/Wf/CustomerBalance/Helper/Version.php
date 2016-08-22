<?php

class Wf_CustomerBalance_Helper_Version extends Mage_Core_Helper_Abstract
{

    /**
     * Returns true if the base version of this Magento installation
     * is equal to the version specified or newer.
     * @param string $version
     * @param mixed $task
     * @return bool
     */
    public function isBaseMageVersionAtLeast($version, $task = null)
    {
        // convert Magento Enterprise, Professional, Community to a base version
        $mage_base_version = $this->convertVersionToCommunityVersion(Mage::getVersion(), $task);

        if (version_compare($mage_base_version, $version, '>=')) {
            return true;
        }
        return false;
    }

    /**
     * True if the base version is at least the version specified without converting version numbers to other versions of Magento.
     *
     * @param string $version
     * @return bool
     * @internal param unknown_type $task
     */
    public function isRawVerAtLeast($version)
    {
        // convert Magento Enterprise, Professional, Community to a base version
        $mage_base_version = Mage::getVersion();

        if (version_compare($mage_base_version, $version, '>=')) {
            return true;
        }
        return false;
    }

    /**
     * True if the base version is at least the verison specified without checking
     * @param string $version
     * @return bool
     */
    public function isEnterpriseAtLeast($version)
    {
        if (!$this->isMageEnterprise()) return false;

        return $this->isRawVerAtLeast($version);
    }

    /**
     *
     * @param string $version
     * @param mixed $task
     * @return boolean
     */
    public function isBaseMageVersion($version, $task = null)
    {
        // convert Magento Enterprise, Professional, Community to a base version
        $mage_base_version = $this->convertVersionToCommunityVersion(Mage::getVersion(), $task);

        if (version_compare($mage_base_version, $version, '=')) {
            return true;
        }
        return false;
    }

    /**     * @alias isBaseMageVersion
     * @param $version
     * @param null $task
     * @return bool
     */
    public function isMageVersion($version, $task = null)
    {
        return $this->isBaseMageVersion($version, $task);
    }

    /**     * @alias isBaseMageVersion
     * @param $version
     * @param null $task
     * @return bool
     */
    public function isMage($version, $task = null)
    {
        return $this->isBaseMageVersion($version, $task);
    }

    /**     * @alias isBaseMageVersionAtLeast
     * @param $version
     * @param null $task
     * @return bool
     */
    public function isMageVersionAtLeast($version, $task = null)
    {
        return $this->isBaseMageVersionAtLeast($version, $task);
    }

    /**
     * True if the Magento version currently running is between the versions specified inclusive
     * @nelkaake -a 16/11/10:
     * @param $version1
     * @param $version2
     * @param mixed $task
     * @return bool
     * @internal param string $version
     */
    public function isMageVersionBetween($version1, $version2, $task = null)
    {

        $is_between = $this->isBaseMageVersionAtLeast($version1, $task) && !$this->isBaseMageVersionAtLeast($version2, $task);
        $is_later_version = $this->isMageVersion($version2);
        return $is_between || $is_later_version;
    }

    /**
     * True if the version of Magento currently being run is Enterprise Edition
     */
    public function isMageEnterprise()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')
        && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws')
        && Mage::getConfig()->getModuleConfig('Enterprise_Checkout')
        && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }


    /**
     * attempt to convert an Enterprise, Professional, Community magento version number to its compatable Community version
     *
     * @param $version
     * @return string
     * @internal param string $task fix problems where direct version numbers cant be changed to a community release without knowing the intent of the task
     */
    public function convertVersionToCommunityVersion($version)
    {

        if ($this->isMageEnterprise()) {
            if (version_compare($version, '1.14.0.0', '>='))
                return '1.9.0.0';
            if (version_compare($version, '1.13.0.0', '>='))
                return '1.8.0.0';
            if (version_compare($version, '1.12.0.0', '>='))
                return '1.7.0.0';
            if (version_compare($version, '1.11.0.0', '>='))
                return '1.6.0.0';
            if (version_compare($version, '1.9.1.0', '>='))
                return '1.5.0.0';
            if (version_compare($version, '1.9.0.0', '>='))
                return '1.4.2.0';
            if (version_compare($version, '1.8.0.0', '>='))
                return '1.3.1.0';
            return '1.3.1.0';
        }

        return $version;
    }

}
