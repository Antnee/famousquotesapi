<?php
namespace App\Exception;

use Throwable;

class QuoteNotAddedException extends AbstractException
{
    protected $code = 2102;
    protected $message = 'Unable to add quote "%s" for author "%s"';

    public function __construct(string $text, string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $text, $name), $this->code, $previous);
    }
}