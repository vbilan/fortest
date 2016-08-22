<?php

class Wf_CustomerBalance_Block_Adminhtml_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{


    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $ver = Mage::getConfig()->getNode('modules/Wf_CustomerBalance/version');
        $html = "";
        $html .= "
            <tr><td colspan='2'>
            <div style=\" margin-bottom: 12px; width: 100%; border-top: 1px solid #B3B3B3; padding-top: 12px;\">
                You are currently running <b>Magecredit v{$ver}</b>. 
                <a href='https://www.magecredit.com/changelog.html' target='_blank'>Click here for updates.</a><BR />
            </div> 
            </td></tr>
        ";
        return $html;
    }

}
