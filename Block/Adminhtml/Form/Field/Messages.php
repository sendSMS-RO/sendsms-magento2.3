<?php

namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field;

use AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field\StatusColumn;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ranges
 */
class Messages extends AbstractFieldArray
{
    /**
     * @var StatusColumn
     */
    private $statusRenderer;


    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('status', [
            'label' => __('Status'),
            'renderer' => $this->getStatusRenderer()
        ]);

        $this->addColumn('message', [
            'label' => __('Mesaj'),
            'class' => 'sendsms-char-count required-entry',
        ]);


        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $status = $row->getStatus();
        if ($status !== null) {
            $options['option_' . $this->getStatusRenderer()->calcOptionHash($status)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return StatusColumn
     * @throws LocalizedException
     */
    private function getStatusRenderer()
    {

        if (!$this->statusRenderer) {

            $this->statusRenderer = $this->getLayout()->createBlock(
                StatusColumn::class,
                'status',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->statusRenderer;
    }

}