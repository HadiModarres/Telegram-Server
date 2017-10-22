<?php

namespace TelegramServer\Exceptions;

class InvalidSessionException extends \RuntimeException
{
    /**
     * InvalidSessionException constructor.
     *
     * @param string $session
     */
    public function __construct(string $session)
    {
        parent::__construct(sprintf('Invalid or inactive session identifier: "%s".', $session));
    }
}