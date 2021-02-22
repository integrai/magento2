<?php
namespace Integrai\Core\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Integrai\Core\Model\ResourceModel\Config;


class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'id';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Integrai\Core\Model\Config::class, Config::class);
    }
}
