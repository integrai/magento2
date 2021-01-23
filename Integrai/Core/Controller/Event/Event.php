<?php

namespace Integrai\Core\Controller\Event;

class Event extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_objectManager;
    protected $_models = array();

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_objectManager = $objectManager;

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

            $events = $this->_getApi()->request('/store/event');

            $success = [];
            foreach ($events as $event) {
                $eventId = $event['_id'];
                $payload = $event['payload'];

                try {
                    foreach($payload['models'] as $modelKey => $modelValue) {
                        $modelName = $modelValue['name'];
                        $modelRun = (bool)$modelValue['run'];

                        if ($modelRun) {
                            $modelArgs = $this->transformArgs($modelValue);
                            $modelMethods = $modelValue['methods'];

                            $model = call_user_func_array(array($this->_objectManager, "create"), $modelArgs);

                            foreach($modelMethods as $methodKey => $methodValue) {
                                $methodName = $methodValue['name'];
                                $methodRun = (bool)$methodValue['run'];

                                if($methodRun) {
                                    $methodArgs = $this->transformArgs($methodValue);
                                    $model = call_user_func_array(array($model, $methodName), $methodArgs);
                                }
                            }

                            $this->_models[$modelName] = $model;
                        }
                    }

                    array_push($success, $eventId);
                } catch (Exception $e) {
                    $this->_getHelper()->log('Erro ao processar o evento', $event);
                    $this->_getHelper()->log('Erro', $e->getMessage());
                }
            }

            // Delete events with success
            if(count($success) > 0){
                $this->_getApi()->request('/store/event', 'DELETE', array(
                    'event_ids' => $success
                ));
            }

            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => true
            ));
        } catch (\Exception $e) {
            $this->_getHelper()->log('Error ao processar o event', $e->getMessage());
            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => true,
                "error" => $e->getMessage()
            ));
        }
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
                if(is_array($arg) && $arg['otherModelName']) {
                    array_push($newArgs, $this->getOtherModel($arg['otherModelName']));
                } else {
                    array_push($newArgs, $arg);
                }
            }
        }

        return $newArgs;
    }
}