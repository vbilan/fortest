<?php

class Wf_CustomerBalance_Block_Adminhtml_Promo_Voucher_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $formData = $this->_loadFormData();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array(
                'id' => $this->getRequest()->getParam('id'), 
                'ret' => Mage::registry('ret')
            )),
            'method'    => 'post'
        ));
        $this->setForm($form);

        $fieldset = $form->addFieldset('voucher_form', array(
            'legend' => $this->__('Voucher Information'),
        ));

        if (!isset($formData['has_been_redeemed'])) {
            $formData['has_been_redeemed'] = false;
        }

        $fieldset->addField('amount', $formData['has_been_redeemed'] ? 'note' : 'text', array(
            'name' => 'amount',
            'required' => true,
            'text' => isset($formData['amount']) ? Mage::helper('core')->formatCurrency($formData['amount']) : '',
            'class' => 'validate-number',
            'label' => $this->__('Voucher Credit Value'),
            'note'  => $this->__("How much store credit will the customer get from redeeming this voucher?")
        ));


        $codeNote = $this->__('This is the code that customers will enter to redeem the voucher. It can be anything you want, but it IS case sensitive.');
        if (!isset($formData['voucher_id'])) {
            $codeNote .= ' '. $this->__('Leave empty to autogenerate a voucher code.');
        }
        $fieldset->addField('code', $formData['has_been_redeemed'] ? 'note' : 'text', array(
            'name' => 'code',
            'text' => isset($formData['code']) ? $formData['code'] : '',
            'required' => isset($formData['voucher_id']),
            'class' => isset($formData['voucher_id']) ? '' : '',
            'note' => $codeNote,
            'label' => $this->__('Voucher Code'),
        ));

        if (isset($formData['voucher_id'])) {
            $fieldset->addField('has_been_redeemed', 'note', array(
                'label'    => $this->__('Has been redeemed yet?'),
                'name'      => 'has_been_redeemed',
                'text'     => $formData['has_been_redeemed'] ? $this->__('Yes') : $this->__('No')
            ));
        }


        if ($formData['has_been_redeemed']) {
            $customer = Mage::getModel('customer/customer')->load($formData['redeemed_by_customer_id']);
            $formData['name'] = $customer->getName();
            $fieldset->addField('name', 'link', array(
                'label'    => $this->__('Redeemed By'),
                'href'      => $this->getUrl('adminhtml/customer/edit', array(
                    'id' => $customer->getId()
                )),
                'target'    => "_customer_".$customer->getId()
            ));
            $fieldset->addField('redeemed_at', 'note', array(
                'text' => $formData['redeemed_at'],
                'label' => $this->__('Date/Time Redeeemed'),
            ));
        }

        $form->setValues($formData);
        $form->setUseContainer(true);

        return parent::_prepareForm();
    }

    protected function _loadFormData()
    {
        if (Mage::getSingleton('adminhtml/session')->getVoucherData()) {
            $formData = Mage::getSingleton('adminhtml/session')->getVoucherData();
        } elseif (Mage::registry('voucher')) {
            $formData = Mage::registry('voucher')->getData();
        } else {
            $formData = array();
        }
        return $formData;
    }
}
