<?php

/**
 * Displays the additional info field for a store credit history model with links to
 * associated entites embeded.
 */
class Wf_CustomerBalance_Block_Adminhtml_Widget_Grid_Column_Renderer_Additionalinfo extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{


    /**
     * Render the additional info field but also add in all the entity links we can find
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $str = $row->getAdditionalInfo();
        $historyId = $row->getId();

        if (!$historyId) {
            return $str;
        }

        $str = $this->_addEntityLinks($historyId, $str, 'order');
        $str = $this->_addEntityLinks($historyId, $str, 'creditmemo');

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
        return $row->getAdditionalInfo();
    }

    /**
     * Locates entity links in a particular "entity #increment_id" format and replaces
     * them with an HTML anchor tag that links to the entity
     * @see Wf_CustomerBalance_Adminhtml_CustomerbalanceController->viewStoreCreditHistoryEntityAction()
     * @param int $historyId         The ID of the customer balance history model.
     * @param string $additionalInfo The additional_info field we're currently displaying. This can include html.
     * @param srting $type           Currently supports "order" or "creditmemo" only.
     */
    protected function _addEntityLinks($historyId, $additionalInfo, $type)
    {
        $urlCfg = array(
            'history_id' => $historyId,
            'rback' => $this->getUrlBase64('*/*/'),
            'tab' => 'storecredit',
            'type' => $type
        );
        $url = $this->getUrl('adminhtml/customerbalance/viewStoreCreditHistoryEntity', $urlCfg);
        $updatedString = preg_replace('/('.$type.' #)([0-9]+)/i', '<a href="'.$url.'" target="$0">$0</a>', $additionalInfo);

        return $updatedString;
    }

}