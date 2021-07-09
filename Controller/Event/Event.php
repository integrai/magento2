<?php

namespace Integrai\Core\Controller\Event;

class Event extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_connection;
    protected $_resource;
    protected $_processEventsFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Framework\App\ResourceConnection $resource,
        \Integrai\Core\Model\ProcessEventsFactory $processEventsFactory
    )
    {
        $this->_request = $request;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_connection = $resource->getConnection();
        $this->_resource = $resource;
        $this->_processEventsFactory = $processEventsFactory;

        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi(){
        return $this->_api;
    }

    public function execute() {
        try{
            $batchIdParam = $this->_request->getParam('batchId');
            $batchId = isset($batchIdParam) ? trim($batchIdParam) : "";
            $events = $this->_getApi()->request(
                '/store/event',
                'GET',
                null,
                array("batchId" => $batchId)
            );

            $this->_getHelper()->log('Total de eventos carregados: ', count($events));

            if (count($events) > 0) {
                $eventIds = array_map(function ($event) {
                    return $event['_id'];
                }, $events);

                $actualEvents = $this->_processEventsFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'event_id',
                        array('in' => $eventIds)
                    )
                    ->load();

                $actualEventIds = array();
                foreach ($actualEvents as $actualEvent) {
                    $actualEventIds[] = $actualEvent->getData('event_id');
                }

                $data = array();
                foreach ($events as $event) {
                    $eventId = $event['_id'];

                    if (!in_array($eventId, $actualEventIds)) {
                        $data[] = array(
                            'event_id' => $eventId,
                            'event' => $event['event'],
                            'payload' => json_encode($event['payload']),
                            'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                        );
                    }
                }

                $this->_getHelper()->log('Total de eventos agendado para processar: ', count($data));

                if (count($data) > 0) {
                    $tableName = $this->_resource->getTableName('integrai_process_events');
                    $this->_connection->insertMultiple($tableName, $data);
                }
            }

            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => true
            ));
        } catch (\Throwable $e) {
            $this->_getHelper()->log('Erro ao salvar os eventos', $e->getMessage());
            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => false,
                "error" => $e->getMessage()
            ));
        }
    }
}