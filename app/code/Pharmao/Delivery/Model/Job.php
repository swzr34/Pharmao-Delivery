<?php

namespace Pharmao\Delivery\Model;

class Job extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Pharmao\Delivery\Model\ResourceModel\Job');
    }
}
?>