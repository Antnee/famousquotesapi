<?php
namespace App\Exception;

class InvalidApiKeyException extends AbstractException
{
    protected $code = 9001;
    protected $message = 'Invalid API key provided';
}