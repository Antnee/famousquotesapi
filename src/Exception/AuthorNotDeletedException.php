<?php
namespace App\Exception;

use Throwable;

class AuthorNotDeletedException extends AbstractException
{
    protected $code = 1104;
    protected $message = 'Unable to delete author "%s"';

    public function __construct(string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $name), $this->code, $previous);
    }
}