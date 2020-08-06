<?php


namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\System;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class DynamicComment extends AbstractBlock implements CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $url = 'https://www.sendsms.ro/ro/contact/';
        return "Daca nu ai deja implementat un sender ID, contacteaza echipa <a href='$url' target='_blank'>sendSMS.ro</a>";
    }
}