<?php
namespace App\Exception;

use Throwable;

class AuthorQuoteCountNotZeroException extends AbstractException
{
    protected $code = 1201;
    protected $message = 'Author has more than zero quotes. Actual count is %d';

    public function __construct(int $count, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $count), $this->code, $previous);
    }
}