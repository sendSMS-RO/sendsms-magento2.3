<?php
namespace AnyPlaceMedia\SendSMS\Controller\Adminhtml\Campaign;

class Filtered extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        # send message
        $postData = $this->getRequest()->getParam('campaign_filtered_form');
        if (is_array($postData)) {
            $message = $postData['message'];
            $phones = $postData['phones'];
            if (!empty($message) && count($phones)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helper = $objectManager->get('AnyPlaceMedia\SendSMS\Helper\SendSMS');
                foreach ($phones as $phone) {
                    $helper->sendSMS($phone, $message, 'campaign');
                }
            }
            # redirect back
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array(
                '_query' => array('sent' => 1)
            ));
        }

        # data
        $startDate = $this->getRequest()->getParam('start_date');
        $endDate = $this->getRequest()->getParam('end_date');
        $minSum = $this->getRequest()->getParam('min_sum');
        $product = $this->getRequest()->getParam('product');
        $county = $this->getRequest()->getParam('county');
        if ($product == '- toate -') {
            $product = '';
        }
        if ($county == '- toate -') {
            $county = '';
        }

        # do query to get phone numbers
        $where = [];
        $binds = [];
        if (!empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $where[] = 'so.created_at >= :START_DATE';
            $binds['START_DATE'] = $startDate.' 00:00:00';
        }
        if (!empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
            $where[] = 'so.created_at <= :END_DATE';
            $binds['END_DATE'] = $endDate.' 23:59:59';
        }
        if (!empty($minSum)) {
            $where[] = 'so.base_grand_total >= :MIN_SUM';
            $binds['MIN_SUM'] = $minSum;
        }
        if (!empty($product)) {
            $where[] = 'soi.item_id = :PRODUCT';
            $binds['PRODUCT'] = $product;
        }
        if (!empty($county)) {
            $where[] = 'soa.region = :COUNTY';
            $binds['COUNTY'] = $county;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        if (!empty($product)) {
            $sql = 'SELECT DISTINCT soa.telephone FROM ' . $resource->getTableName('sales_order') . ' AS so, ' . $resource->getTableName('sales_order_item') . ' AS soi, ' . $resource->getTableName('sales_order_address') . ' AS soa WHERE soa.parent_id = so.entity_id AND so.state = \'complete\' AND '.implode(' AND ', $where);
        } else {
            $sql = 'SELECT DISTINCT soa.telephone FROM ' . $resource->getTableName('sales_order') . ' AS so, ' . $resource->getTableName('sales_order_address') . ' AS soa WHERE soa.parent_id = so.entity_id AND so.state = \'complete\''.(count($where) ? ' AND '.implode(' AND ', $where):'');
        }
        $results = $connection->fetchAll($sql, $binds);

        # send collection to registry
        $registry = $objectManager->get('Magento\Framework\Registry');
        $registry->register('sendsms_filters', $results);

        return $this->resultPageFactory->create();
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return true;
    }
}
