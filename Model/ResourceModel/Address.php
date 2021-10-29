<?php

namespace Pharmao\Delivery\Model\ResourceModel;

class Address extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('pharmao_cache_addresses', 'id');
    }
}
