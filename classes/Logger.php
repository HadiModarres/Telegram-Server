<?php

namespace TelegramServer;

class Logger
{
    /**
     * @param string $message
     */
    public static function log(string $message)
    {
        echo $message . PHP_EOL;
    }
}