<?php
namespace App\Exception;

use Throwable;

class AuthorNotAddedException extends AbstractException
{
    protected $code = 1102;
    protected $message = 'Unable to add author "%s"';

    public function __construct(string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $name), $this->code, $previous);
    }
}