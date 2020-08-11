<?php

namespace Integrai\Core\Cron;

use \Integrai\Core\Model\Observer\Events;

class ResendEvents
{
    private $_helper;
    private $_api;
    private $_eventsFactory;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Integrai\Core\Model\EventsFactory $eventsFactory
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_eventsFactory = $eventsFactory;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute()
    {
        if ($this->_getHelper()->isEnabled()) {
            $eventsModel = $this->_eventsFactory->create();

            $events = $eventsModel->getCollection()->load();

            foreach ($events as $event) {
                $eventName = $event->getData('event');
                $payload = json_decode($event->getData('payload'), true);
                try{
                    $this->_getApi()->sendEvent($eventName, $payload, true);
                    $event->delete();
                } catch (\Exception $e) {
                    $this->_getHelper()->log('Error ao reenviar o evento', $e->getMessage());
                }
            }
        }
    }
}