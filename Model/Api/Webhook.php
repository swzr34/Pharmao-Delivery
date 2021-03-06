<?php

namespace Pharmao\Delivery\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Webhook
{
    protected $logger;

    protected $_jobFactory;

    public function __construct(
        LoggerInterface $logger,
        Order $order,
        ScopeConfigInterface $scopeConfig,
        \Pharmao\Delivery\Model\JobFactory $jobFactory,
        \Pharmao\Delivery\Helper\Data $helper
    ) {

        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->_jobFactory = $jobFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */

    public function getPost($data)
    {
        $model = $this->_jobFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('job_id', trim($data['id']));
        $jobData = $collection->getData();
        if (!empty($jobData)) {
            $jobUpdate = $model->load($jobData[0]['id']);
            $jobUpdate->setStatus($data['status']);
            $jobUpdate->setAdded(date("Y-m-d H:i:s"));
            $saveData = $jobUpdate->save();
        }
        return true;
    }
}
