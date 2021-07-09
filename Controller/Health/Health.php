<?php

namespace Integrai\Core\Controller\Health;

class Health extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_connection;
    protected $_resource;
    protected $_processEventsFactory;
    protected $_eventsFactory;
    protected $_resourceInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Framework\App\ResourceConnection $resource,
        \Integrai\Core\Model\ProcessEventsFactory $processEventsFactory,
        \Integrai\Core\Model\EventsFactory $eventsFactory,
        \Magento\Framework\Module\ResourceInterface $resourceInterface
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_connection = $resource->getConnection();
        $this->_resource = $resource;
        $this->_processEventsFactory = $processEventsFactory;
        $this->_eventsFactory = $eventsFactory;
        $this->_resourceInterface = $resourceInterface;

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
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            $magentoVersion = $productMetadata->getVersion();;
            $moduleVersion = $this->_resourceInterface->getDbVersion('Integrai_Core');
            $isRunningEventProcess = $this->_getHelper()->getConfigTable('PROCESS_EVENTS_RUNNING', null, 'RUNNING', false);

            $processEventsModel = $this->_processEventsFactory->create();
            $totalEventsToProcess = $processEventsModel
                ->getCollection()
                ->getSize();

            $eventsModel = $this->_eventsFactory->create();
            $totalUnsentEvent = $eventsModel
                ->getCollection()
                ->getSize();

            $data = array(
                'phpVersion' => phpversion(),
                'platform' => 'magento2',
                'platformVersion' => $magentoVersion,
                'moduleVersion' => $moduleVersion,
                'isRunningEventProcess' => $isRunningEventProcess === 'RUNNING',
                'totalEventsToProcess' => $totalEventsToProcess,
                'totalUnsentEvent' => $totalUnsentEvent
            );

            $this->_getApi()->request(
                '/store/health',
                'POST',
                $data
            );

            $this->_getHelper()->log('Health executado');

            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => true
            ));
        } catch (\Throwable $e) {
            $this->_getHelper()->log('Health error', $e->getMessage());
            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => false,
                "error" => $e->getMessage()
            ));
        }
    }
}