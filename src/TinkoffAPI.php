<?php

namespace Tinkoff;

use Exception;
use GuzzleHttp\Client;

/**
 * Class TinkoffAPI
 * @package Tinkoff
 */
class TinkoffAPI {
    private $_client;

    private $_baseUri = 'https://api.tinkoff.ru';
    private $_origin = 'web,ib5,platform';
    private $_webUserId = null;
    private $_sessionId = null;

    /**
     * Создание объекта
     *
     * @param array $params
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function __construct($params = []) {
        $this->_client = new Client([
            'base_uri' => $this->_baseUri,
        ]);

        if (isset($params['webUserId'])) {
            $this->_webUserId = $params['webUserId'];
        } else {
            $this->_webUserId = $this->getWebUserId();
        }

        if (isset($params['sessionId'])) {
            $this->_sessionId = $params['sessionId'];
        } else {
            $this->_sessionId = $this->getSessionId();
        }
    }

    /**
     * Вход в личный кабинет по логину и паролю
     *
     * @param $username
     * @param $password
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function signUp($username, $password) {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        $params = [
            'wuid' => $this->_webUserId,
            'entrypoint_type' => 'context',
            'fingerprint' => 'Mozilla/5.0 (Windows NT 10.0 Win64 x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36###1920x1080x24###-180###true###true###Chrome PDF Plugin::Portable Document Format::application/x-google-chrome-pdf~pdf;Chrome PDF Viewer::::application/pdf~pdf;Native Client::::application/x-nacl~,application/x-pnacl~',
            'fingerprint_gpu_shading_language_version' => 'WebGL GLSL ES 1.0 (OpenGL ES GLSL ES 1.0 Chromium)',
            'fingerprint_gpu_vendor' => 'WebKit',
            'fingerprint_gpu_extensions_hash' => '3edb841ebc63ed5979dac735b1c34d6c',
            'fingerprint_gpu_extensions_count' => '26',
            'fingerprint_device_platform' => 'Win32',
            'fingerprint_client_timezone' => '-180',
            'fingerprint_client_language' => 'ru-RU',
            'fingerprint_canvas' => 'fe6505667f07db8da4e98ccca66d6adb',
            'fingerprint_accept_language' => 'ru-RU,ru,en-US,en',
            'mid' => '78361992811047472982056945525899061491',
            'device_type' => 'desktop',
            'form_view_mode' => 'desktop',
            'username' => $username,
            'password' => $password,
        ];

        return $this->requestMethod('sign_up', $query, $params);
    }

    /**
     * Подтверждение входа по SMS
     *
     * @param $method
     * @param $ticket
     * @param $code
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function confirmBySms($method, $ticket, $code) {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        $params = [
            'initialOperationTicket' => $ticket,
            'initialOperation' => $method,
            'confirmationData' => '{"SMSBYID":"' . $code . '"}',
        ];

        return $this->requestMethod('confirm', $query, $params);
    }

    /**
     * Получение прав (после входа)
     *
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function levelUp() {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        return $this->requestMethod('level_up', $query);
    }

    /**
     * Получение состояния сессии
     *
     * @return mixed
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function getSessionStatus() {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
        ];

        return $this->requestMethod('session_status', $query);
    }

    /**
     * Кэширование данных на сервере (???)
     *
     * @param $fields
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function warmUpCache($fields) {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        $this->requestMethod('warmup_cache', $query, $fields);
    }

    /**
     * Получение информации о пользователе
     *
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function getPersonalInfo() {
        $query = [
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        $params = [
            'wuid' => $this->_webUserId,
        ];

        return $this->requestMethod('personal_info', $query, $params);
    }

    /**
     * Получение информации о счетах и их балансах
     *
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function getAccountsFlat() {
        $query = [
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        $params = [
            'wuid' => $this->_webUserId,
        ];

        return $this->requestMethod('accounts_flat', $query, $params);
    }

    /**
     * Получение ИД веб-пользователя
     *
     * @return string
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function getWebUserId() {
        $query = [];

        if (isset($this->_sessionId)) {
            $query['sessionid'] = $this->_sessionId;
        }

        if (isset($this->_webUserId)) {
            $query['wuid'] = $this->_webUserId;
        }

        $payload = $this->requestMethod('webuser', $query);
        return $payload->wuid;
    }

    /**
     * Получение ИД сессии
     *
     * @return string
     * @throws InvalidRequestDataException|WaitingConfirmationException
     */
    public function getSessionId() {
        $query = [
            'origin' => $this->_origin,
        ];

        if (isset($this->_sessionId)) {
            $query['sessionid'] = $this->_sessionId;
        }

        if (isset($this->_webUserId)) {
            $query['wuid'] = $this->_webUserId;
        }

        return $this->requestMethod('session', $query);
    }

    /**
     * Отправка активности
     *
     * @return object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function ping() {
        $query = [
            'origin' => $this->_origin,
            'sessionid' => $this->_sessionId,
            'wuid' => $this->_webUserId,
        ];

        return $this->requestMethod('ping', $query);
    }

    /**
     * Вызов произвольного метода
     *
     * @param $method
     * @param array $query
     * @param array $params
     * @return bool|string|object
     * @throws InsufficientPrivilegesException
     * @throws InvalidRequestDataException
     * @throws WaitingConfirmationException
     */
    public function requestMethod($method, $query = [], $params = []) {
        $methodUri = "/v1/{$method}";

        if (!empty($params)) {
            $data = $this->sendPost($methodUri, $query, $params);
        } else {
            $data = $this->sendGet($methodUri, $query);
        }

        if ($data->resultCode === 'INVALID_REQUEST_DATA') {
            throw new InvalidRequestDataException($data->plainMessage);
        }

        if ($data->resultCode === 'WAITING_CONFIRMATION') {
            throw new WaitingConfirmationException($data, $this->_sessionId, $this->_webUserId);
        }

        if ($data->resultCode === 'INSUFFICIENT_PRIVILEGES') {
            throw new InsufficientPrivilegesException('Недостаточно прав!');
        }

        if ($data->resultCode !== 'OK') {
            throw new Exception('Неизвестный результат: "' . $data->resultCode . '"');
        }

        if (isset($data->payload)) {
            return $data->payload;
        } else {
            return true;
        }
    }

    /**
     * Получение локального ИД сессии
     *
     * @return null|string
     */
    public function getLocalSessionId() {
        return $this->_sessionId;
    }

    /**
     * Получение локального ИД веб-пользователя
     *
     * @return null|string
     */
    public function getLocalWebUserId() {
        return $this->_webUserId;
    }

    /**
     * Отправка GET запроса
     *
     * @param $uri
     * @param array $query
     * @return object
     */
    private function sendGet($uri, $query = []) {
        $response = $this->_client->post($uri, [
            'query' => $query
        ]);

        return json_decode($response->getBody());
    }

    /**
     * Отправка POST запроса
     *
     * @param $uri
     * @param array $query
     * @param array $params
     * @return object
     */
    private function sendPost($uri, $query = [], $params = []) {
        $response = $this->_client->post($uri, [
            'query' => $query,
            'form_params' => $params,
        ]);

        return json_decode($response->getBody());
    }
}

/**
 * Исключение, выбрасываемое при неверных отправленных данных
 *
 * Class InvalidRequestDataException
 * @package Tinkoff
 */
class InvalidRequestDataException extends Exception {
    // Exception if Tinkoff API returned bad request data
}

/**
 * Исключение, выбрасываемое при недостаточных правах
 *
 * Class InsufficientPrivilegesException
 * @package Tinkoff
 */
class InsufficientPrivilegesException extends Exception {
    // Exception if Tinkoff API returned bad request data
}

/**
 * Исключение, выбрсываемое при необходимости подтверждения операции
 *
 * Class WaitingConfirmationException
 * @package Tinkoff
 */
class WaitingConfirmationException extends Exception {
    private $_data = null;
    private $_sessionId = null;
    private $_webUserId = null;

    /**
     * WaitingConfirmationException constructor.
     * @param object $data
     * @param string $session
     * @param string $webUserId
     */
    public function __construct($data, $session, $webUserId) {
        if (is_object($data)) {
            $this->_data = $data;
        }

        if (is_string($session)) {
            $this->_sessionId = $session;
        }

        if (is_string($webUserId)) {
            $this->_webUserId = $webUserId;
        }

        parent::__construct('Необходимо подтверждение операции!');
    }

    /**
     * @return string
     */
    public function __toString() {
        $dataJson = json_encode($this->_data);
        return __CLASS__ . ": [{$this->message}]: {$dataJson}\n";
    }

    /**
     * @return object
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @return null|string
     */
    public function getSessionId() {
        return $this->_sessionId;
    }

    /**
     * @return null|string
     */
    public function getWebUserId() {
        return $this->_webUserId;
    }
}