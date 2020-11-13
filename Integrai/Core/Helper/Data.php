<?php

namespace Integrai\Core\Helper;

class Data
{
    protected $_logger;
    protected $_scopeConfig;
    protected $_configFactory;

    public function __construct(
        \Integrai\Core\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Integrai\Core\Model\ConfigFactory $configFactory
    ){
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_configFactory = $configFactory;
    }

    public function log($message, $array = null, $level = null)
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        $this->_logger->setName("integrai");
        $this->_logger->debug($message);
    }

    public function getConfig($name, $group = 'general') {
        return $this->_scopeConfig->getValue("integrai_core/{$group}/{$name}");
    }

    public function getConfigTable($name, $configName = null, $defaultValue = null, $parseJson = true) {
        $config = $this->_configFactory->create()->load($name, 'name');

        if ($parseJson) {
            $values = json_decode($config->getData('values'), true);
        } else {
            $values = $config->getData('values');
        }

        if ($configName) {
            return $values[$configName] ?: $defaultValue;
        }

        return $values;
    }

    public function isEnabled() {
        return $this->getConfig('enable');
    }

    public function isEventEnabled($eventName) {
        $events =  $this->getConfigTable('EVENTS_ENABLED');
        return $this->isEnabled() && in_array($eventName, $events);
    }

    public function getGlobalConfig($configName, $defaultValue = null) {
        return $this->getConfigTable('GLOBAL', $configName, $defaultValue);
    }
}