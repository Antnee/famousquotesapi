<?php
namespace App\Exception;

use Throwable;

class AuthorIdNotFoundException extends AbstractException
{
    protected $code = 1002;
    protected $message = 'Requested author "%s" could not be found';

    public function __construct(string $author, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $author), $this->code, $previous);
    }
}