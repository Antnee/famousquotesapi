<?php
namespace App\Exception;

class QuoteDataInvalidException extends AbstractException
{
    protected $code = 2011;
    protected $message = 'The quote data provided was invalid';
}