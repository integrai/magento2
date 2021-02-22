<?php
namespace Integrai\Core\Model\ResourceModel\Events;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Integrai\Core\Model\ResourceModel\Events;


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
        $this->_init(\Integrai\Core\Model\Events::class, Events::class);
    }
}
