<?php

namespace Integrai\Core\Cron;

class ProcessEvents
{
    private $_helper;
    private $_api;
    private $_processEventsFactory;
    private $_objectManager;
    private $_connection;
    private $_resource;
    private $_models = array();

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Integrai\Core\Model\ProcessEventsFactory $processEventsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_processEventsFactory = $processEventsFactory;
        $this->_objectManager = $objectManager;
        $this->_connection = $resource->getConnection();
        $this->_resource = $resource;
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

            $limit = $this->_getHelper()->getConfigTable('GLOBAL', 'process_events_limit', 50);
            $isRunning = $this->_getHelper()->getConfigTable('PROCESS_EVENTS_RUNNING', null, 'RUNNING');

            $this->_getHelper()->log('limit', $limit);
            $this->_getHelper()->log('isRunning', $isRunning);

            if ($isRunning === 'RUNNING') {
                $this->_getHelper()->log('Já existe um processo rodando');
            } else {
                $this->_getHelper()->updateConfig('PROCESS_EVENTS_RUNNING', true);

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

                foreach ($events as $event) {
                    $eventIds[] = $event->getData('id');

                    $this->_getHelper()->log('Evento a processar', $event);

                    $eventId = $event->getData('event_id');
                    $payload = json_decode($event->getData('payload'), true);

                    try {
                        if(!isset($payload) || !isset($payload['models']) || !is_array($payload['models'])) {
                            throw new \Exception('Evento sem payload');
                        }

                        foreach($payload['models'] as $modelKey => $modelValue) {
                            $modelName = $modelValue['name'];
                            $modelRun = (bool)$modelValue['run'];

                            if ($modelRun) {
                                $modelArgs = $this->transformArgs($modelValue);
                                $modelMethods = $modelValue['methods'];

                                $model = call_user_func_array(array($this->_objectManager, "create"), $modelArgs);
                                $model = $this->runMethods($model, $modelMethods);

                                $this->_models[$modelName] = $model;
                            }
                        }

                        array_push($success, $eventId);
                    } catch (\Exception $e) {
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

                // Delete events
                if (count($success) > 0 || count($errors) > 0) {
                    $this->_getApi()->request('/store/event', 'DELETE', array(
                        'event_ids' => $success,
                        'errors' => $errors
                    ));

                    $tableName = $this->_resource->getTableName('integrai_process_events');

                    $eventIdsRemove = implode(', ', $eventIds);
                    $this->_getHelper()->log('eventIdsRemove', $eventIdsRemove);
                    $this->_getHelper()->log('where', "id in ($eventIdsRemove)");
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

    private function runMethods($model, $modelMethods) {
        foreach($modelMethods as $methodKey => $methodValue) {
            $methodName = $methodValue['name'];
            $methodRun = (bool)$methodValue['run'];

            if($methodRun && $model) {
                $methodArgs = $this->transformArgs($methodValue);
                $model = call_user_func_array(array($model, $methodName), $methodArgs);
            }
        }

        return $model;
    }

    private function getOtherModel($modelName) {
        return $this->_models[$modelName];
    }

    private function transformArgs($itemValue) {
        $newArgs = array();

        $args = isset($itemValue['args']) ? $itemValue['args'] : null;
        if(is_array($args)) {
            $argsFormatted = array_values($args);

            foreach($argsFormatted as $arg){
                if(is_array($arg) && isset($arg['otherModelName'])) {
                    $model = $this->getOtherModel($arg['otherModelName']);

                    if (isset($arg['otherModelMethods'])) {
                        array_push($newArgs, $this->runMethods($model, $arg['otherModelMethods']));
                    } else {
                        array_push($newArgs, $model);
                    }
                } else {
                    array_push($newArgs, $arg);
                }
            }
        }

        return $newArgs;
    }
}