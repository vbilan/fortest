<?php

$installer = $this;

$installer->startSetup();

// Check for issues
try {
    Mage::helper('wf_customerbalance/debug')->checkDesigns();
} catch (Exception $e) {
    // Ignore any checker errors.
}

$installer->endSetup();

