<?php

class Wf_CustomerBalance_Block_Adminhtml_System_Config_Help extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{


    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $diagnosticsUrl = $this->getUrl('adminhtml/debugstorecredit/repairdesigns');
        $html = "";
        $html .= "
            <tr><td colspan='2'>
            <div style=\" margin-bottom: 12px; margin-top: 24px; width: 100%; border-bottom: 1px solid #B3B3B3; padding-bottom: 12px;\">
                <i>Not seeing the store credit option in the checkout? <a href='{$diagnosticsUrl}'>Try running our design repair tool by clicking here</a>.</i>
                <br><i>Need more help? Check out our <a href='https://magecredit.com/troubleshooting.html' target='troubleshooting'>troubleshooting guide</a>.</i>
            </div> 
            </td></tr>
        ";
        return $html;
    }

}
