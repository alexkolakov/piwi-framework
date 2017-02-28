<?php

namespace Piwi;

class Session
{
    /**
     * NativeSession initialization
     *
     * @param string $name
     * @param int $lifetime
     * @param null $path
     * @param null $domain
     * @param bool|false $secure
     */
    public function __construct($name, $lifetime = 3600, $path = null, $domain = null, $secure = false)
    {
        if(strlen($name)<1)
            $name='_sess';

        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }

    public function has($name)
    {
        return isset($_SESSION[$name]);
    }

    public function __get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function destroySession()
    {
        return session_destroy();
    }

    public function getSessionId()
    {
        return session_id();
    }

    public function saveSession()
    {
        session_write_close();
    }

    public function unsetSession()
    {
        $_SESSION = [];
    }
}