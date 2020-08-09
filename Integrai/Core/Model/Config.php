<?php

namespace Integrai\Core\Model;

use Magento\Framework\Model\AbstractModel;

class Config extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Config::class);
    }
}