<?php
namespace App\Exception;

use Throwable;

class AuthorNotUpdatedException extends AbstractException
{
    protected $code = 1103;
    protected $message = 'Unable to update author name from "%s" to "%s"';

    public function __construct(string $oldName, string $newName, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $oldName, $newName), $this->code, $previous);
    }
}