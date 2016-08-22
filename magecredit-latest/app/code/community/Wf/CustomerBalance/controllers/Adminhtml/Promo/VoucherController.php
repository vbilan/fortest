<?php

class Wf_CustomerBalance_Adminhtml_Promo_VoucherController extends Mage_Adminhtml_Controller_Action
{
    const EXPORT_FILE_NAME = 'vouchers';

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $voucher = Mage::getModel('wf_customerbalance/voucher')->load($id);

        if ($voucher->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $voucher->setData($data);
            }

            Mage::register('voucher', $voucher);

            $this->_loadEditForm($voucher);
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('wf_customerbalance')->__('Voucher does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $voucher  = Mage::getModel('wf_customerbalance/voucher')->load($id);

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $voucher->setData($data);
        }

        Mage::register('voucher', $voucher);

        $this->_loadEditForm($voucher);
    }

    protected function _loadEditForm($voucher)
    {
        $this->loadLayout();
        $this->_setActiveMenu('wf_customerbalance/promo_voucher');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->createBlock('wf_customerbalance/adminhtml_promo_voucher_edit');
        $block->setVoucher($voucher);
        $this->_addContent($block);

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!($data = $this->getRequest()->getPost())) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('wf_customerbalance')->__('Unable to find post to save'));
            $this->_redirect('*/*/');

            return $this;
        }

        $id = $this->getRequest()->getParam('id');

        $isNew = empty($id);

        $model = Mage::getModel('wf_customerbalance/voucher');

        try {
            if ($isNew) {
                $adminUserId = Mage::getSingleton('admin/session')->getUser()->getId();
                $data['created_by_admin_id'] = $adminUserId;
            }

            if (Mage::getModel('wf_customerbalance/voucher')->loadByCode($data['code'])->getId()) {
                throw new Exception(
                    Mage::helper('wf_customerbalance')->__("The voucher code '%s' is already in use by another voucher.", $data['code'])
                );
            }

            $data['base_currency_code'] = Mage::app()->getBaseCurrencyCode();

            $model->setData($data)->setId($id);

            $model->save();

            if ($isNew) {
                $successMsg = Mage::helper('wf_customerbalance')->__('Voucher was created successfully.');
            } else {
                $successMsg = Mage::helper('wf_customerbalance')->__('Voucher was successfully saved.');
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($successMsg);
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));

                return;
            }
            $this->_redirect('*/*/');

            return $this;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

            return $this;
        }
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('wf_customerbalance/voucher');

                $model->setId($this->getRequest()->getParam('id'))->delete();

                $msg = Mage::helper('wf_customerbalance')->__('Post was successfully deleted');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

  

    public function massDeleteAction()
    {
        $voucherIds = $this->getRequest()->getParam('vouchers', array());

        try {
            $voucherCount = 0;
            foreach ($voucherIds as $voucherId) {
                Mage::getModel('wf_customerbalance/voucher')->load($voucherId)->delete();
                $voucherCount++;
            }

            $msg = Mage::helper('wf_customerbalance')->__('%s voucher(s) were successfully deleted.', $voucherCount);
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        } catch (Exception $e) {
            $msg = Mage::helper('wf_customerbalance')->__("One or more vouchers could not be deleted.");
            Mage::getSingleton('adminhtml/session')->addError($msg);
            Mage::loadException($e);
        }

        $this->_redirect('*/*/');
    }

    /**
     * Export product grid to CSV format.
     */
    public function exportCsvAction()
    {
        $fileName = self::EXPORT_FILE_NAME.'-'.date('m.d.y.H:i:s').'.xml';
        $content = $this->getLayout()->createBlock('wf_customerbalance/adminhtml_promo_voucher_grid');
        $csv = $content->getCsv();

        $this->_sendUploadResponse($fileName, $csv);
    }

    /**
     * Export product grid to XML format.
     */
    public function exportXmlAction()
    {
        $fileName = self::EXPORT_FILE_NAME.'-'.date('m.d.y.H:i:s').'.xml';
        $content = $this->getLayout()->createBlock('wf_customerbalance/adminhtml_promo_voucher_grid');
        $xml = $content->getXml();

        $this->_sendUploadResponse($fileName, $xml);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die();
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('promo/voucher');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/promo/voucher');
    }

}
