<?php
namespace App\Exception;

use Throwable;

class AuthorNameNotFoundException extends AbstractException
{
    protected $code = 1003;
    protected $message = 'Requested author name "%s" could not be found';

    public function __construct(string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $name), $this->code, $previous);
    }
}