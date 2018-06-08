<?php
namespace App\Exception;

use Exception;
use JsonSerializable;

abstract class AbstractException extends Exception implements JsonSerializable
{
    public function jsonSerialize()
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];
    }
}