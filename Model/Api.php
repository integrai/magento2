<?php

namespace Integrai\Core\Model;

class Api {
    private $_helper;
    private $_eventsFactory;
    private $_resourceInterface;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\EventsFactory $eventsFactory,
        \Magento\Framework\Module\ResourceInterface $resourceInterface
    )
    {
        $this->_helper = $helper;
        $this->_eventsFactory = $eventsFactory;
        $this->_resourceInterface = $resourceInterface;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function request($endpoint, $method = 'GET', $body = array(), $params = array()) {
        $curl = curl_init();

        $url = $this->getApiUrl() . $endpoint;

        if (isset($params) && count($params) > 0) {
           $url = $url . '?' . http_build_query($params);
       }

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_TIMEOUT => $this->_getHelper()->getGlobalConfig('api_timeout_seconds', 2),
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method
        );

        $curl_options[CURLOPT_POST] = $method === 'POST';

        if (!is_null($body) && count($body) > 0) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $curl_options);

        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);
        $response_error = isset($response['error']) ? $response['error'] : "Ocorreu um erro, tente novamente";

        if($info['http_code'] !== 200) {
            $this->_getHelper()->log("HTTP ERROR", array(
                'code' => curl_errno($curl),
                'error' => curl_error($curl),
                'response' => $response,
                'info' => $info,
                'headers' => $this->getHeaders(),
                'body' => $body,
            ));

            throw new \Exception($response_error);
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
        $moduleVersion = $this->_resourceInterface->getDbVersion('Integrai_Core');
        $apiKey = $this->_getHelper()->getConfig('api_key');
        $secretKey = $this->_getHelper()->getConfig('secret_key');
        $token = base64_encode("{$apiKey}:{$secretKey}");

        return array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Basic {$token}",
            "x-integrai-plaform: magento2",
            "x-integrai-plaform-version: {$magentoVersion}",
            "x-integrai-module-version: {$moduleVersion}",
        );
    }

    public function sendEvent($eventName, $payload, $resend = false) {
        try{
            $response = $this->request('/store/event/magento2', 'POST', array(
                'event' => $eventName,
                'payload' => $payload,
            ));
            $this->_getHelper()->log($eventName, 'Enviado com sucesso');
            return $response;
        } catch (\Exception $e) {
            if(!$resend) {
                $this->_backupEvent($eventName, $payload);
            } else {
                throw new \Exception($e);
            }
        }
    }

    private function _backupEvent($eventName, $payload) {
        $this->_getHelper()->log("Gravando no banco para mandar depois", $eventName);

        return $this->_eventsFactory->create()
            ->setData(array(
                'event' => $eventName,
                'payload' => json_encode($payload),
            ))
            ->save();
    }
}
