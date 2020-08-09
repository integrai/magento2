<?php

namespace Integrai\Core\Model;

class Api {
    private $_helper;

    public function __construct(
        \Integrai\Core\Helper\Data $helper
    )
    {
        $this->_helper = $helper;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function request($endpoint, $method = 'GET', $body = array()) {
        $curl = curl_init();
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_TIMEOUT => $this->_getHelper()->getGlobalConfig('api_timeout_seconds', 2),
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_URL => $this->getApiUrl() . $endpoint,
        );

        if ($method === 'POST') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $curl_options);

        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);

        if($info['http_code'] !== 200) {
            $this->_getHelper()->log("HTTP ERROR", array(
                'code' => curl_errno($curl),
                'error' => curl_error($curl),
                'response' => $response,
                'info' => $info,
            ));

            throw new Exception($response['error']);
        }

        curl_close($curl);
        return $response;
    }

    private function getApiUrl() {
        return $this->_getHelper()->getGlobalConfig('api_url');
    }

    private function getHeaders() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $magentoVersion = $productMetadata->getVersion();;
        $moduleVersion = \Magento\Framework\Module\ResourceInterface::getDbVersion('Integrai_Core');
        $apiKey = $this->_getHelper()->getConfig('api_key');
        $secretKey = $this->_getHelper()->getConfig('secret_key');
        $token = base64_encode("{$apiKey}:{$secretKey}");

        return array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer {$token}",
            "x-integrai-plaform: magento",
            "x-integrai-plaform-version: {$magentoVersion}",
            "x-integrai-module-version: {$moduleVersion}",
        );
    }

    public function sendEvent($eventName, $payload, $resend = false) {
        $this->_getHelper()->log($eventName, $payload);
//        try{
//            $response = $this->request('/event/magento', 'POST', array(
//                'event' => $eventName,
//                'payload' => $payload,
//            ));
//            $this->_getHelper()->log($eventName, 'Enviado com sucesso');
//            return $response;
//        } catch (Exception $e) {
//            if(!$resend) {
//                $this->_backupEvent($eventName, $payload);
//            } else {
//                throw new Exception($e);
//            }
//        }
    }

    private function _backupEvent($eventName, $payload) {
        $this->_getHelper()->log("Gravando no banco para mandar depois", $eventName);

        return \Integrai\Core\Model\EventsFactory::create()
            ->setData(array(
                'event' => $eventName,
                'payload' => json_encode($payload),
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ))
            ->save();
    }

    public function resendBackupEvents() {
        if ($this->_getHelper()->isEnabled()) {
            $eventsModel = \Integrai\Core\Model\EventsFactory::create();

            $events = $eventsModel->getCollection();

            foreach ($events as $event) {
                $eventName = $event->getData('event');
                $payload = json_decode($event->getData('payload'), true);
                try{
                    $this->sendEvent($eventName, $payload, true);
                    $this->_getHelper()->log('DELETE');
                    $event->delete();
                } catch (Exception $e) {
                    $this->_getHelper()->log('Error ao reenviar o evento', $eventName, Zend_Log::ERR);
                }
            }
        }
    }
}