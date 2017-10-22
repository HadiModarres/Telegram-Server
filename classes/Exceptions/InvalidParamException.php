<?php

namespace TelegramServer\Exceptions;

class InvalidParamException extends \RuntimeException
{
    /**
     * InvalidParamException constructor.
     *
     * @param string $param
     * @param string $value
     */
    public function __construct(string $param, string $value)
    {
        parent::__construct(sprintf('Invalid or unsupported value for param "%s" supplied: "%s".', $param, $value));
    }
}