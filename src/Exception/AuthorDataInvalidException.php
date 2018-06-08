<?php
namespace App\Exception;

class AuthorDataInvalidException extends AbstractException
{
    protected $code = 1011;
    protected $message = 'The author data provided was invalid';
}