<?php

namespace Integrai\Core\Model;

use Magento\Framework\Model\AbstractModel;

class Events extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Events::class);
    }
}