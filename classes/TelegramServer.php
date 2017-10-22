<?php

namespace TelegramServer;

use danog\MadelineProto\API;
use danog\MadelineProto\MTProto;
use TelegramServer\Exceptions\InvalidParamException;
use TelegramServer\Exceptions\InvalidSessionException;
use TelegramServer\Exceptions\MissingRequiredParamException;

class TelegramServer
{
    const SESSION_EXPIRATION = 600;

    /**
     * @var API
     */
    protected $apiTemplate;

    /**
     * @var array[]
     */
    protected $sessions = [];

    /**
     * @param int $bytes
     * @return string
     */
    protected function formatSize(int $bytes): string
    {
        $sizes = ['', 'K', 'M', 'G', 'T', 'P'];
        $factor = (int) floor((strlen($bytes) - 1) / 3);
        return sprintf('%.2f', $bytes / pow(1024, $factor)) . $sizes[$factor] . 'B';
    }

    /**
     * @param string $param
     * @param array $params
     * @return string
     */
    protected function requireParam(string $param, array $params): string
    {
        if (!isset($params[$param]) || !is_string($params[$param]) || empty($params[$param])) {
            throw new MissingRequiredParamException($param);
        }
        return $params[$param];
    }

    /**
     * @param array $params
     * @param bool $extendExpiration
     * @return array
     */
    protected function requireSession(array $params, bool $extendExpiration = true): array
    {
        $key = $this->requireParam('session', $params);

        if (!array_key_exists($key, $this->sessions)) {
            throw new InvalidSessionException($key);
        }

        if ($extendExpiration) {
            $this->sessions[$key]['expires'] = time() + self::SESSION_EXPIRATION;
        }

        return [$key, $this->sessions[$key]['api'], $this->sessions[$key]['expires']];
    }

    /**
     * TelegramServer constructor.
     *
     * @param API $api
     */
    public function __construct(API $api)
    {
        $this->apiTemplate = $api;
    }

    /**
     * @return TelegramServer
     */
    public function expireSessions(): TelegramServer
    {
        $expired = 0;

        foreach ($this->sessions as $key => $session) {
            if ($session['expires'] < time()) {
                unset($this->sessions[$key]);
                $expired++;
            }
        }

        if (0 < $expired) {
            Logger::log('Expired ' . $expired . ' session' . (1 < $expired ? 's' : '') . ' (' .
                count($this->sessions) . ' active, ' . $this->formatSize(memory_get_usage(true)) . ')');
        }

        return $this;
    }

    /**
     * @param array $params
     * @return array
     */
    public function handle(array $params): array
    {
        $action = $this->requireParam('action', $params);

        switch ($action) {
            // opens a new session and returns the handle ID
            case 'open':
                $api = clone $this->apiTemplate;
                $key = md5(spl_object_hash($api));

                $this->sessions[$key] = [
                    'api' => $api,
                    'expires' => time() + self::SESSION_EXPIRATION
                ];

                Logger::log('Opened session: ' . $key . ' (' . count($this->sessions) . ' active, ' .
                    $this->formatSize(memory_get_usage(true)) . ')');

                return [
                    'session' => $key,
                    'expires_in' => self::SESSION_EXPIRATION
                ];

            // pings a session keeping it alive
            case 'ping':
                list($key, $api, $expires) = $this->requireSession($params);

                return [
                    'session' => $key,
                    'expires_in' => $expires - time()
                ];

            // closes a previously open session
            case 'close':
                list($key) = $this->requireSession($params, false);
                unset($this->sessions[$key]);

                Logger::log('Closed session: ' . $key . ' (' . count($this->sessions) . ' active, ' .
                    $this->formatSize(memory_get_usage(true)) . ')');

                return [
                    'closed' => $key
                ];

            // initiates a login process for a specified phone number
            case 'login':
                list($key, $api) = $this->requireSession($params);

                /** @var MTProto $mtp */
                $mtp = $api->API;

                return (array) $mtp->phone_login($this->requireParam('phone', $params));

            // completes a login process for the specified phone number and received code
            case 'login.complete':
                list($key, $api) = $this->requireSession($params);

                $code = $this->requireParam('code', $params);
                $firstName = $this->requireParam('first_name', $params);
                $lastName = $this->requireParam('last_name', $params);

                /** @var MTProto $mtp */
                $mtp = $api->API;

                $result = (array) $mtp->complete_phone_login($code);
                if ('account.needSignup' === $result['_']) {
                    return (array) $mtp->complete_signup($firstName, $lastName);
                }

                return $result;

            default:
                throw new InvalidParamException('action', $action);
        }
    }
}