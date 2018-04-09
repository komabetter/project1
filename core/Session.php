<?php
namespace core;

class Session
{

    public function __construct()
    {}

    public static function init()
    {
        @session_start();
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        if(isset($_SESSION[$key]))
        return $_SESSION[$key];
    }
    
    public static function destroy(){
        session_destroy();
    }
    
    public static function unsetSession($key){
        if(isset($_SESSION[$key]))
        unset($_SESSION[$key]);
    }
}

