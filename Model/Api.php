<?php

namespace Integrai\Core\Model;

class Api {
    private $_helper;
    private $_eventsFactory;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\EventsFactory $eventsFactory
    )
    {
        $this->_helper = $helper;
        $this->_eventsFactory = $eventsFactory;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function request($body = array(), $params = array()) {
        $curl = curl_init();

        $url = $this->getApiUrl();

        if (isset($params) && count($params) > 0) {
           $url = $url . '&' . http_build_query($params);
       }

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_TIMEOUT => $this->_getHelper()->getGlobalConfig('apiTimeoutSeconds', 2),
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true
        );

        if (!is_null($body) && count($body) > 0) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $curl_options);

        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);
        $response_error = isset($response['error']) ? $response['error'] : "Ocorreu um erro, tente novamente";

        if(!in_array((int)$info['http_code'], array(200, 201, 204))) {
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
        return $this->_getHelper()->getGlobalConfig('apiUrl');
    }

    private function getHeaders() {
        return array(
            "Content-Type: application/json",
            "Accept: application/json",
        );
    }

    public function sendEvent($eventName, $payload, $resend = false, $isSync = false) {
        try{
            $response = $this->request(array(
                'partnerEvent' => $eventName,
                'payload' => $payload,
            ), array( 'isSync' => $isSync ));
            $this->_getHelper()->log($eventName, 'Enviado com sucesso');
            return $response;
        } catch (\Throwable $e) {
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
