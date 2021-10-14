<?php

namespace Integrai\Core\Cron;

class ProcessEvents
{
    private $_helper;
    private $_api;
    private $_processEvent;
    private $_processEventsFactory;
    private $_objectManager;
    private $_connection;
    private $_resource;
    private $_indexerFactory;
    private $_models = array();

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Integrai\Core\Model\ProcessEvent $processEvent,
        \Integrai\Core\Model\ProcessEventsFactory $processEventsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_processEvent = $processEvent;
        $this->_processEventsFactory = $processEventsFactory;
        $this->_objectManager = $objectManager;
        $this->_connection = $resource->getConnection();
        $this->_resource = $resource;
        $this->_indexerFactory = $indexerFactory;
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
            $this->_getHelper()->log('Iniciando processamento dos eventos...');

            $limit = $this->_getHelper()->getConfigTable('GLOBAL', 'processEventsLimit', 50);
            $timeout = $this->_getHelper()->getConfigTable('GLOBAL', 'processEventsTimeoutHours', 1);
            $isRunning = $this->_getHelper()->getConfigTable('PROCESS_EVENTS_RUNNING', null, 'NOT_RUNNING', false);
            $lastRunning = $this->_getHelper()->getConfigTable('LAST_PROCESS_EVENTS_RUN', null, null, false);
            $now = date('Y-m-d H:i:s');
            $dateDiff = date_diff(date_create($now), date_create($lastRunning));
            $interval = $dateDiff->format('%h');

            if ($isRunning === 'RUNNING' && $lastRunning && $interval < $timeout) {
                $this->_getHelper()->log('Já existe um processo rodando', array(
                    'isRunning' => $isRunning,
                    'lastRunning' => $lastRunning,
                ));
            } else {
                $this->_getHelper()->updateConfig('PROCESS_EVENTS_RUNNING', 'RUNNING');
                $this->_getHelper()->updateConfig('LAST_PROCESS_EVENTS_RUN', $now);

                $processEventsModel = $this->_processEventsFactory->create();
                $events = $processEventsModel
                    ->getCollection()
                    ->setPageSize($limit)
                    ->setCurPage(1)
                    ->load();

                $this->_getHelper()->log('Total de eventos a processar: ', count($events));

                $success = [];
                $errors = [];
                $eventIds = [];

                $hasProductEvent = false;

                foreach ($events as $event) {
                    $eventIds[] = $event->getData('id');

                    $eventId = $event->getData('event_id');
                    $eventName = $event->getData('event');
                    $payload = json_decode($event->getData('payload'), true);

                    $hasProductEvent = str_contains($eventName, 'PRODUCT');

                    try {
                        if(!isset($payload) || !isset($payload['models']) || !is_array($payload['models'])) {
                            throw new \Exception('Evento sem payload');
                        }

                        $this->_processEvent->process($payload);

                        array_push($success, $eventId);
                    } catch (\Throwable $e) {
                        $this->_getHelper()->log('Erro', $e->getMessage());
                        $this->_getHelper()->log('Erro ao processar o evento', $event);

                        if ($eventId) {
                            array_push($errors, array(
                                "eventId" => $eventId,
                                "error" => $e->getMessage()
                            ));
                        }
                    }
                }

                // Reindex
                if ($hasProductEvent) {
                    $indexerIds = array(
                        'catalog_product_price',
                        'cataloginventory_stock',
                    );

                    foreach ($indexerIds as $indexerId) {
                        $this->_getHelper()->log('indexerId ', $indexerId);

                        try{
                            $indexer = $this->_indexerFactory->create();
                            $indexer->load($indexerId);
                            $indexer->reindexAll();
                        } catch (\Throwable $e) {
                            $this->_getHelper()->log('Error reindex', $e->getMessage());
                        }
                    }
                }

                // Delete events
                if (count($success) > 0 || count($errors) > 0) {
                    $this->_getApi()->request('/store/event', 'DELETE', array(
                        'eventIds' => $success,
                        'errors' => $errors
                    ));

                    $tableName = $this->_resource->getTableName('integrai_process_events');

                    $eventIdsRemove = implode(', ', $eventIds);
                    $this->_connection->delete($tableName, "id in ($eventIdsRemove)");

                    $this->_getHelper()->log('Eventos processados: ', array(
                        'success' => $success,
                        'errors' => $errors
                    ));
                }

                $this->_getHelper()->updateConfig('PROCESS_EVENTS_RUNNING', 'NOT_RUNNING');
            }
        }
    }
}