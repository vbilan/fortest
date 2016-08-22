<?php

/**
 *
 */
class Wf_CustomerBalance_Helper_Debug extends Mage_Core_Helper_Abstract
{
    public $troubleShootingUrl = "https://www.magecredit.com/troubleshooting.html";

    public function checkInstall($verbose = false)
    {
        $allOk = true;
        $allOk = $allOk && $this->checkCompilation($verbose);
        $allOk = $allOk && $this->checkRewrites($verbose);
        $allOk = $allOk && $this->checkOSC($verbose);
        $allOk = $allOk && $this->checkConfig($verbose);

        return $allOk;
    }


    public function checkConfig($verbose = false)
    {
        $allGood = true;

        if (!Mage::getStoreConfigFlag('payment/free/active')) {
            $cfg = new Mage_Core_Model_Config();
            $cfg->saveConfig('payment/free/active', 1, 'default', 0);
            $allGood = false;
            foreach (Mage::app()->getStores() as $store) {
                if (!Mage::getStoreConfigFlag('payment/free/active', $store->getId())) {
                    $cfg->saveConfig('payment/free/active', 1, 'stores', $store->getId());
                }
            }
            $allGood = false;
            if ($verbose) {
                echo "  <B>Notice:</B> $0 checkout config option was disabled. We automatically enabled it.<br>\n";
            }
        }

        return $allGood;
    }

    /**
     * Checks to see if there are any magecredit incompatible rewrites.
     * @param  boolean $verbose
     * @return true if no issues detected.
     */
    public function checkRewrites($verbose = false)
    {
        $rewrites = $this->findRewrites(
            array('**order_payment'),
            array('**payment', 'customer/customer', 'sales/quote'),
            array(), array(), "TBT_");
        $allRewrites = array_merge($rewrites['models'], $rewrites['blocks']);
        if (sizeof($allRewrites) == 0) {
            return true;
        }

        if ($verbose) {
            echo "  <B>WARNING:</B> Other extensions are rewriting the payment checkout. Here's some data related to the issue: " . json_encode($allRewrites);
        } else {
            $msg_title = "Magecredit detected modules that may cause checkout problems.";
            $msg_desc = "Magecredit detected modules installed on your store that may cause isseus with your checkout, thus making Magecredit malfunction. Although issues are unlikely to occur, your developer should be informed of this issue. Data about the class rewrites can be found here: " . json_encode($allRewrites);
            $this->createInstallNotice($msg_title, $msg_desc, $this->troubleShootingUrl, Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
        }
        return false;
    }

    /**
     * Finds rewrites by matching the provided strings
     * @param  array $blocks array of rewrites to look for. Example: array("sales/quote"). Use ** to do a wildcard search.
     * @param  array $models
     * @param  array $helpers
     * @param  array $configs
     * @param  string $allow if this string is found in a rewrite match then the match will be ignored.
     * @return array          array of arrays for each type of rewrite, a list of rewrites matched.
     */
    public function findRewrites($blocks = array(), $models = array(), $helpers = array(), $configs = array(), $allow = null)
    {
        $rewritesData = $this->getRewrites();

        $findRewritesData = array(
            'blocks' => is_array($blocks) ? $blocks : array($blocks),
            'models' => is_array($models) ? $models : array($models),
            'helpers' => is_array($helpers) ? $helpers : array($helpers),
            'config' => is_array($configs) ? $configs : array($configs),
        );

        $foundRewrites = array('models' => array(), 'blocks' => array(), 'helpers' => array(), 'config' => array());
        foreach ($rewritesData as $rewriteType => $rewrites) {
            $findRewrites = $findRewritesData[$rewriteType]; // Example: ['models']
            foreach ($rewrites as $rewrite => $rewriteInfo) {
                foreach ($findRewrites as $findRewrite) {
                    if (!empty($allow) && sizeof($rewriteInfo) == 1) {
                        if (strpos($rewriteInfo[0], $allow) !== false) { // if the allowed string matches then skip this match
                            continue;
                        }
                    }
                    if (strpos($findRewrite, '**') === 0) {
                        $findRewriteWild = substr($findRewrite, 2);
                        if (stripos($rewrite, $findRewriteWild) !== false) {
                            $foundRewrites[$rewriteType][$findRewrite] = $rewrites[$rewrite];
                        }
                    } else { // Do a stardard compare
                        if (strcmp($rewrite, $findRewrite) === 0) {
                            $foundRewrites[$rewriteType][$findRewrite] = $rewrites[$rewrite];
                        }
                    }
                }
            }
        }

        return $foundRewrites;
    }

    /**
     * @return array an associative array of rewrites. For example: http://pastebin.com/weYDw2FZ
     */
    public function getRewrites()
    {
        //folders to parse
        $folders = array(
            Mage::getBaseDir('code') . DS . 'local' . DS,
            Mage::getBaseDir('code') . DS . 'community' . DS
        );

        $configFiles = array();
        foreach ($folders as $folder) {
            $files = glob($folder . '*' . DS . '*' . DS . 'etc' . DS . 'config.xml');//get all config.xml files in the specified folder
            $configFiles = array_merge($configFiles, $files);//merge with the rest of the config files
        }
        $rewrites = array();//list of all rewrites

        foreach ($configFiles as $file) {
            $dom = new DOMDocument;
            $fileContents = file_get_contents($file);
            if (empty($fileContents)) continue; // file empty, move on.
            $dom->loadXML($fileContents);
            $xpath = new DOMXPath($dom);
            $path = '//rewrite/*';//search for tags named 'rewrite'
            $text = $xpath->query($path);
            foreach ($text as $rewriteElement) {
                $type = $rewriteElement->parentNode->parentNode->parentNode->tagName;//what is overwritten (model, block, helper)
                $parent = $rewriteElement->parentNode->parentNode->tagName;//module identifier that is being rewritten (core, catalog, sales, ...)
                $name = $rewriteElement->tagName;//element that is rewritten (layout, product, category, order)
                foreach ($rewriteElement->childNodes as $element) {
                    $rewrites[$type][$parent . '/' . $name][] = $element->textContent;//class that rewrites it
                }
            }
        }

        return $rewrites;
    }

    public function searchForFile($folder, $search, $folderSearch = null)
    {
        $it = new RecursiveDirectoryIterator($folder);
        $search = is_array($search) ? $search : array($search);
        $files = array();
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if ($folderSearch) {
                if (stripos($file, $folderSearch) === false) {
                    continue; // not in a folder we're looking for
                }
            }
            $fileParts = explode(DS, $file);
            $filename = strtolower(array_pop($fileParts));
            if (in_array($filename, $search))
                $files[] = $file;
        }

        return $files;
    }

    /**
     * Checks for design template/layout issues.
     * @param  boolean $verbose if true will output results to screen.
     * @return boolean IF an issue occurred returns false, otherwise true.
     */
    public function checkDesigns($verbose = false)
    {
        $allOkay = true;
        $frontendDesignPath = Mage::getBaseDir('design') . DS . "frontend";
        $frontendBaseDesigns = $frontendDesignPath . DS . "base" . DS . "default";

        $defaultPaymentPhtmPath = $frontendBaseDesigns . DS . "template" . DS . "checkout" . DS . "onepage" . DS . "payment.phtml";


        $methodsAdditionalHtml = '<?php echo $this->getChildChildHtml(\'methods_additional\', \'\', true, true)';
        $methodsHtml = '<?php echo $this->getChildHtml(\'methods\') ?>';
        $fieldsetHtml = "id=\"co-payment-form\">";

        $defaultPaymentFiles = array();

        // First collect all BASE/DEFAULT payment.phtml files that contain our criteria
        if ($verbose) echo "Searching for payment.phtml and payment-method.phtml in {$frontendBaseDesigns}...<Br>\n";
        $filesToLookAt = $this->searchForFile($frontendBaseDesigns, "payment.phtml", 'onepage');
        $filesToLookAt = array_merge($filesToLookAt, $this->searchForFile($frontendBaseDesigns, "payment-method.phtml"));
        foreach ($filesToLookAt as $filename) {
            if ($verbose) echo "FOUND {$filename} <br>\n";

            $customPaymentPhtml = file_get_contents($filename);

            if (stripos($customPaymentPhtml, $methodsAdditionalHtml) !== false) {
                if ($verbose) echo "  [disqualified methods_additional]<br>\n";
                continue; // No prob, found he methods additional line.
            }

            if (stripos($customPaymentPhtml, $methodsHtml) === false) {
                if ($verbose) echo "  [disqualified methods]<br>\n";
                continue;
            }

            // if(stripos($customPaymentPhtml, $fieldsetHtml) === false) {
            //     if($verbose) echo "  [Disqualified fieldset_missing]\n<br>";
            //     continue;
            // }

            $defaultPaymentFiles[] = (string)$filename;
        }
        if ($verbose) echo "<BR>\n";


        // OK now we have all the bas/default files, time to loop through the themes and make sure they exhist there.
        $themePaymentFiles = array();
        foreach (Mage::app()->getStores() as $store) {
            $package = Mage::getStoreConfig('design/package/name', $store->getId());
            if (empty($package)) continue;

            $theme = Mage::getStoreConfig('design/theme/template', $store->getId());
            $theme = empty($theme) ? "default" : $theme;

            foreach ($defaultPaymentFiles as $defaultPaymentFile) {
                $themePaymentFile = str_ireplace('base' . DS . 'default', $package . DS . $theme, $defaultPaymentFile);
                if ($verbose) echo "-&gt;ADDED {$themePaymentFile}<br>\n";
                $themePaymentFiles[$themePaymentFile] = $defaultPaymentFile;
            }
        }
        // Now search design updates
        $designUpdates = Mage::getResourceModel('core/design_collection');
        foreach ($designUpdates as $designUpdate) {
            list($package, $theme) = explode("/", $designUpdate->getDesign());
            if (empty($package)) continue;

            foreach ($defaultPaymentFiles as $defaultPaymentFile) {
                $themePaymentFile = str_ireplace('base' . DS . 'default', $package . DS . $theme, $defaultPaymentFile);
                if ($verbose) echo "-&gt;ADDED {$themePaymentFile}<br>\n";
                $themePaymentFiles[$themePaymentFile] = $defaultPaymentFile;
            }
        }


        if ($verbose) echo "<BR>\n";

        if ($verbose) echo "About to modify following files: <PRE>" . print_r($themePaymentFiles, 1) . "</PRE>";


        $modifiedFiles = array();
        foreach ($themePaymentFiles as $path => $basePath) {
            if ($verbose) echo "Check path {$path}...<br>\n";

            $this->createFileFromTemplate($basePath, $path, $verbose);

            if (!realpath($path)) {
                if ($verbose) echo "[ERROR: File doesn't exist!]<br>\n";
                continue; // file doesn't exist
            }

            $customPaymentPhtml = file_get_contents($path);
            if (stripos($customPaymentPhtml, $methodsAdditionalHtml) !== false) {
                if ($verbose) echo "  [already contains methods_additional]<br>\n";
                continue; // No prob, found the methods additional line.
            }

            $allOkay = false;

            $newMethodsHtml = $methodsAdditionalHtml . "; /* This line was auto-integrated by Magecredit. */ ?>\n    " . $methodsHtml;
            $customPaymentPhtml = str_ireplace($methodsHtml, $newMethodsHtml, $customPaymentPhtml);
            file_put_contents($path, $customPaymentPhtml);

            $modifiedFiles[] = $path;

            if ($verbose) echo "<br>\n&nbsp;&nbsp;&nbsp;&nbsp;<B>Notice:</B> Conflicting template detected but it was automatically fixed: [{$path}]<br>\n----<br>\n";
        }

        if (sizeof($modifiedFiles) > 0) {
            if ($verbose) {
                echo "Magecredit detected custom checkout templates but we automatically fixed them for you. This was done by adding a getChildHtml('methods_additional') line to the following file(s): " . implode(', ', $modifiedFiles) . ". For additional information please see https://www.magecredit.com/installation_instructions.html";
            } else {
                $msg_title = "Magecredit detected custom checkout templates but we automatically fixed them for you.";
                $msg_desc = "Magecredit detected custom checkout templates but we automatically fixed them for you. This was done by adding a getChildHtml('methods_additional') line to the following file(s): " . implode(', ', $modifiedFiles) . ". For additional information please see https://www.magecredit.com/installation_instructions.html";
                $this->createInstallNotice($msg_title, $msg_desc, $this->troubleShootingUrl, Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
            }
        } elseif (!$allOkay) {
            if ($verbose) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<B>Warning!</B> Conflicting template detected and could not auto-fix a path. <br>\n";
            } else {
                $msg_title = "Magecredit detected incompatible custom templates and may not work until they are fixed.";
                $msg_desc = "Magecredit detected incompatible custom templates and may not work until they are fixed. These custom files may be one of the following files: " . implode(', ', $paymentPhtmlFiles) . ". For additional information please see https://www.magecredit.com/installation_instructions.html.";
                $this->createInstallNotice($msg_title, $msg_desc, $this->troubleShootingUrl, Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
            }
        } else {
            // Some other unknown issue...
            if ($verbose) echo "Some other unknown issue occurred...";
        }

        return $allOkay;
    }

    public function createFileFromTemplate($baseTemplatePath, $destination, $verbose = false)
    {
        if (realpath($destination)) {
            if ($verbose) echo "already a real path. ";
            return false;
        }

        try {
            if ($verbose) echo "&nbsp;&nbsp;&nbsp;&nbsp;Copying file {$baseTemplatePath} to {$destination}....";

            $dirs = explode(DS, $destination);
            $destinationFolders = array();
            $numFolders = sizeof($dirs);
            for ($di = 0; $di < $numFolders; $di++) {
                if (array_pop($dirs) == 'app') break;
                $destinationFolders[] = implode(DS, $dirs);
            }

            for ($i = sizeof($destinationFolders) - 1; $i >= 0; $i--) {
                $destinationFolder = $destinationFolders[$i];
                if (realpath($destinationFolder)) {
                    continue;
                }
                mkdir($destinationFolder);
                if ($verbose) echo "<Br>\n[Created {$destinationFolder}]";
            }

            copy($baseTemplatePath, $destination);

            if ($verbose) echo "Copied!<Br>\n";

        } catch (Exception $e) {
            // Ignore error, just move on.
            return false;
        }

        return true;
    }


    public function copyBaseTemplateTo($baseTemplate, $destination, $verbose = false)
    {
        // If using default Magento 1.7 or older, then add in the methods_additional
        $basePaymentPhtmlPath = Mage::getBaseDir('design') . DS . "frontend" . DS . 'base' . DS
            . 'default' . DS . "template" . DS . $baseTemplate;
        if (realpath($destination)) {
            if ($verbose) echo "{$destination} is already a real path. ";
            return false;
        }

        try {
            if ($verbose) echo "Copied {$basePaymentPhtmlPath} to {$destination}...";

            $dirs = explode(DS, $destination);
            $destinationFolders = array();
            $numFolders = sizeof($dirs);
            for ($di = 0; $di < $numFolders; $di++) {
                if (array_pop($dirs) == 'app') break;
                $destinationFolders[] = implode(DS, $dirs);
            }

            foreach ($destinationFolders as $destinationFolder) {
                if (realpath($destinationFolder)) {
                    continue;
                }
                mkdir($destinationFolder);
                if ($verbose) echo "Created folder {$destinationFolder}. ";
            }

            copy($basePaymentPhtmlPath, $destination);
            if ($verbose) echo "Copied file {$basePaymentPhtmlPath} to {$destination}. ";
        } catch (Exception $e) {
            // Ignore error, just move on.
            return false;
        }

        return true;
    }

    /**
     * Checks for OSC extensions supported or unsupported and automatically outputs a notice if need be.
     * @param bool $verbose
     * @return bool true if no incompatible OSC modules are detected.
     */
    public function checkOSC($verbose = true)
    {
        $unsupportedOSCExtensions = array(
            'GoMage_Checkout',
            'Smartwave_OnepageCheckout',
            'DeivisonArthur_OnepageCheckout',
        );

        foreach ($unsupportedOSCExtensions as $ext) {
            if (Mage::getConfig()->getNode("modules/{$ext}/version")) {
                if ($verbose) {
                    echo "Magecredit detected that you have the custom checkout extension {$ext} installed which is not supported. Please visit https://www.magecredit.com/one_step_checkout.html for help with getting Magecredit to show in your checkout.";
                } else {
                    $msg_title = "Magecredit detected the custom checkout extension {$ext} installed which is not supported.";
                    $msg_desc = "Magecredit detected that you have the custom checkout extension {$ext} installed which is not supported. Please visit https://www.magecredit.com/one_step_checkout.html for help with getting Magecredit to show in your checkout.";
                    $this->createInstallNotice($msg_title, $msg_desc, "https://www.magecredit.com/one_step_checkout.html", Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
                }
                return false;
            }
        }

        $supportedOSCExtensions = array(
            'Magestore_Onestepcheckout',
            'Idev_OneStepCheckout',
            'IWD_OnePageCheckout',
            'AW_OneStepCheckout',
            'Ait_OneStepCheckout',
        );

        foreach ($supportedOSCExtensions as $ext) {
            if (Mage::getConfig()->getNode("modules/{$ext}/version")) {
                if ($verbose) {
                    echo "Magecredit detected that you have the custom checkout extension {$ext} installed. Please visit https://www.magecredit.com/one_step_checkout.html for help with getting Magecredit to show in your checkout.";
                } else {
                    $msg_title = "Magecredit detected the known checkout extension {$ext} installed. Magecredit should work fine, however custom checkout modules are not supported by our team.";
                    $msg_desc = "Magecredit detected the known checkout extension {$ext} installed. Magecredit should work fine, however custom checkout modules are not supported by our team. Please visit https://www.magecredit.com/one_step_checkout.html for more information.";
                    $this->createInstallNotice($msg_title, $msg_desc, "https://www.magecredit.com/one_step_checkout.html", Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
                }
                break; // only need to detect one custom checkout to speak out.
            }
        }

        return true;
    }

    /**
     * Checks to see if compilation is enabled. If so, disables it and pushes out a notice.
     * @param bool $verbose
     * @return bool true if all OK, false if problems detected
     */
    public function checkCompilation($verbose = true)
    {
        if (!$this->isCompilationEnabled()) {
            return true; // Nothing to do here.
        }

        try {
            $compileDir = Mage::getBaseDir('base') . DS . "includes";
            $configFile = $compileDir . DS . "config.php";
            if (!file_exists($configFile)) {
                throw new Exception("Compilation config file not found");
            }

            $originalConfigContent = file_get_contents($configFile);
            $newConfigContent = str_ireplace("define", "#define", $originalConfigContent);
            $newConfigContent = str_ireplace("##define", "#define", $newConfigContent);
            file_put_contents($configFile, $newConfigContent);

        } catch (Exception $e) {
            if ($verbose) {
                echo "Compilation was enabled while trying to install Magecredit, so we tried disabled it but failed. Please do this now to avoid errors and  remember to do this next time you install an extension! Exception: " . $e;
            } else {
                $msg_title = "You must disable your Magento compiler before installing Magecredit or any new extensions. Please do this now to avoid errors!";
                $msg_desc = "Compilation was enabled while trying to install Magecredit, so we tried disabled it but failed. Please do this now to avoid errors and  remember to do this next time you install an extension!";
                $this->createInstallNotice($msg_title, $msg_desc, $this->troubleShootingUrl, Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
            }
            return false;
        }

        if ($verbose) {
            echo "Compilation was enabled while trying to install Magecredit, so we disabled it to avoid any errors. Please recompile before re-enabling it and remember to do disable it first next time you install an extension.";
        } else {
            $msg_title = "Magecredit disabled your Magento compiler. Please recompile before re-enabling it.";
            $msg_desc = "Compilation was enabled while trying to install Magecredit, so we disabled it to avoid any errors. Please recompile before re-enabling it and remember to do disable it first next time you install an extension.";
            $this->createInstallNotice($msg_title, $msg_desc, $this->troubleShootingUrl, Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
        }

        return false;
    }

    /**
     * Checks if Magento has compiled files
     * @return boolean true if any compiled files are found
     */
    public function isCompiled()
    {
        if (!Mage::helper('wf_customerbalance/version')->isBaseMageVersionAtLeast('1.4')) {
            return false;
        }

        $compiler = Mage::getModel('compiler/process');
        if (!$compiler) {
            return false;
        }

        if ($compiler->getCollectedFilesCount() == 0) {
            return false;
        }

        return true;
    }

    /**
     * Chekcs if the Magento compilation is enabled.
     * @return boolean true if the Mangento compilation mode (compiler) is enabled
     */
    public function isCompilationEnabled()
    {
        if (!defined('COMPILER_INCLUDE_PATH')) {
            return false;
        }
        return true;
    }


    /**
     * Creates an installation message notice in the backend.
     * @param string $msg_title
     * @param string $msg_desc
     * @param string $url =null if null default Sweet Tooth URL is used.
     * @param null $severity
     * @return $this
     */
    public function createInstallNotice($msg_title, $msg_desc, $url = null, $severity = null)
    {
        $message = Mage::getModel('adminnotification/inbox');
        $message->setDateAdded(date("c", time()));

        if ($url == null) {
            $url = "https://www.magecredit.com/changelog.html";
        }

        if ($severity === null) {
            $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
        }

        $message->setTitle($msg_title);
        $message->setDescription($msg_desc);
        $message->setUrl($url);
        $message->setSeverity($severity);
        $message->save();

        return $this;
    }

}
