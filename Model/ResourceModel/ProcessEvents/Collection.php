<?php
namespace Integrai\Core\Model\ResourceModel\ProcessEvents;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Integrai\Core\Model\ResourceModel\ProcessEvents;


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
        $this->_init(\Integrai\Core\Model\ProcessEvents::class, ProcessEvents::class);
    }
}
