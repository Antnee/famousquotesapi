<?php
namespace App\Entity;

use Exception;
use Ramsey\Uuid\Uuid;

class ApiResponse
{
    public static function content($content=null, Exception $e=null): string
    {
        return json_encode([
            'requestId' => Uuid::uuid4(),
            'time' => time(),
            'error' => $e,
            'content' => $content
        ]);
    }

    public static function success($content): string
    {
        return self::content($content);
    }

    public static function error(Exception $e): string
    {
        return self::content(null, $e);
    }
}