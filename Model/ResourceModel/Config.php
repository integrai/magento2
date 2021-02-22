<?php

namespace Integrai\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Config extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('integrai_config', 'id');
    }
}
