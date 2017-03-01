<?php

namespace Piwi;


class Request
{
    /** @var Request|null */
    private static $_instance = null;

    /** @var array */
    private $get = [];

    /** @var array */
    private $post = [];

    /** @var array */
    private $cookies = [];

    /** @var array */
    private $server = [];

    private function __construct() 
    {
        $this->cookies = $_COOKIE;
        $this->server  = $_SERVER;
    }

    /**
     * Set POST array
     *
     * @param $array
     */
    public function setPost($array)
    {
        if(is_array($array))
            $this->post = $array;
    }

    /**
     * Set GET array
     *
     * @param $array
     */
    public function setGet($array)
    {
        if(is_array($array))
            $this->get = $array;
    }

    /**
     * Check if we have value for the given key
     *
     * @param string|integer $key
     * @return bool
     */
    public function hasGet($key)
    {
        return array_key_exists($key, $this->get);
    }

    /**
     * Check if we have value for the given key
     *
     * @param string|integer $key
     * @return bool
     */
    public function hasPost($key)
    {
        return array_key_exists($key, $this->post);
    }

    /**
     * Check if we have value for the given key
     *
     * @param string|integer $key
     * @return bool
     */
    public function hasCookies($key)
    {
        return array_key_exists($key, $this->cookies);
    }

    /**
     * Check if we have value for the given key
     *
     * @param string|integer $key
     * @return bool
     */
    public function hasServer($key)
    {
        return array_key_exists($key, $this->server);
    }

    /**
     * Get element from $_GET array
     *
     * @param string|integer $key
     * @param string|null $normalize
     * @param mixed $default
     * @return mixed|null
     */
    public function get($key, $normalize = null, $default = null)
    {
        if($this->hasGet($key))
        {
            if($normalize != null)
                return Utils::normalize($this->get[$key], $normalize);
            return $this->get[$key];
        }
        
        return $default;
    }

    /**
     * Get element from $_POST array
     *
     * @param string|integer $key
     * @param string|null $normalize
     * @param mixed $default
     * @return mixed|null
     */
    public function post($key, $normalize = null, $default = null)
    {
        if($this->hasPost($key))
        {
            if($normalize != null)
                return Utils::normalize($this->post[$key], $normalize);
            
            return $this->post[$key];
        }
        return $default;        
    }

    /**
     * Get element from the $_COOKIE array
     *
     * @param string|integer $key
     * @param string|null $normalize
     * @param mixed $default
     * @return mixed|null
     */
    public function cookies($key, $normalize = null, $default = null)
    {
        if($this->hasCookies($key))
        {
            if($normalize != null)
                return Utils::normalize($this->cookies[$key], $normalize);
            
            return $this->cookies[$key];
        }
        
        return $default;        
    }

    /**
     * Get element from $_SERVER array
     *
     * @param string|integer $key
     * @param string|null $normalize
     * @param mixed $default
     * @return mixed|null
     */
    public function server($key, $normalize = null, $default = null)
    {
        if($this->hasServer($key))
        {
            if($normalize != null)
                return Utils::normalize($this->server[$key], $normalize);

            return $this->server[$key];
        }

        return $default;
    }

    /**
     * Get all GET params
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get;
    }

    /**
     * Get all POST params
     *
     * @return array
     */
    public function postAll()
    {
        return $this->post;
    }

    /**
     * Get all COOKIES params
     *
     * @return array
     */
    public function cookiesAll()
    {
        return $this->cookies;
    }

    /**
     * Get all SERVER params
     *
     * @return array
     */
    public function serverAll()
    {
        return $this->server;
    }

    /**
     * Initialization of the class
     * @return Request
     */
    public static function Init() 
    {
        if(self::$_instance == null)
            self::$_instance = new self();
        
        return self::$_instance;
    }

    /**
     * Get Scheme
     *
     * @return mixed|null
     */
    public function getScheme()
    {
        return $this->server('REQUEST_SCHEME', null, 'http');
    }

    /**
     * Get Host
     *
     * @return mixed|null
     */
    public function getHost()
    {
        return $this->server('HTTP_HOST');
    }

}