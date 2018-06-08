<?php
namespace App\Exception;

use Throwable;

class NoQuotesFoundException extends AbstractException
{
    protected $code = 2001;
    protected $message = 'No quotes could be found';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct($this->message, $this->code, $previous);
    }
}