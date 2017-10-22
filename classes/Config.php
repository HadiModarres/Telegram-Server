<?php

namespace TelegramServer;

class Config
{
    const IP = 1;
    const PORT = 2;

    const CACHE_PATH = 3;

    const API_ID = 4;
    const API_HASH = 5;

    const SERVER_SALT = 6;

    const TEST_MODE = 7;

    /**
     * @var Config
     */
    protected static $instance;

    /**
     * @var array
     */
    public $config = [];

    /**
     * @param int|array $name
     * @param null $value
     * @return mixed|null
     */
    public static function config($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $_name => $_value) {
                self::config($_name, $_value);
            }
            return null;
        }

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        if (isset($value)) {
            self::$instance->config[$name] = $value;
        }

        return self::$instance->config[$name];
    }
}