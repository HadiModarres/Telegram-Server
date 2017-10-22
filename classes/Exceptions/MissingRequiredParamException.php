<?php

namespace TelegramServer\Exceptions;

class MissingRequiredParamException extends \RuntimeException
{
    /**
     * MissingRequiredParamException constructor.
     *
     * @param string $param
     */
    public function __construct(string $param)
    {
        parent::__construct(sprintf('Missing required query param: "%s".', $param));
    }
}