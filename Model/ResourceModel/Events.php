<?php

namespace Integrai\Core\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Events extends AbstractDb
{
    public $date;

    public function __construct(
        Context $context,
        DateTime $date
    ) {
        $this->date = $date;

        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('integrai_events', 'id');
    }

    protected function _beforeSave(AbstractModel $object)
    {
        //set default Update At and Create At time post
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        return $this;
    }
}
