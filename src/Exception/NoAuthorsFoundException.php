<?php
namespace App\Exception;

class NoAuthorsFoundException extends AbstractException
{
    protected $code = 1001;
    protected $message = 'No authors could be found';
}