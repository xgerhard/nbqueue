<?php
namespace App\src;

class Session
{
    static function has($identifier)
    {
        return isset($_SESSION[$identifier]);
    }

    static function put($identifier, $value)
    {
        $_SESSION[$identifier] = $value;
    }

    static function pull($identifier)
    {
        return $_SESSION[$identifier];
    }
}
?>