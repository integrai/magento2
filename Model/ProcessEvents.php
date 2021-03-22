<?php

namespace Integrai\Core\Model;

use Magento\Framework\Model\AbstractModel;

class ProcessEvents extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\ProcessEvents::class);
    }
}