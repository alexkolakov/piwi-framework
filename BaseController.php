<?php

namespace Piwi;

class BaseController
{
    /**
     * View object holder
     *
     * @var View
     */
    protected $view;

    /**
     * Config array
     *
     * @var array
     */
    protected $config;

    /**
     * Request object holder
     *
     * @var Request
     */
    protected $request;

    /**
     * Request object holder
     *
     * @var Session
     */
    protected $session;

    /**
     * PDO object
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Utils object holder
     *
     * @var Utils
     */
    protected $utils;


    public function __construct($config, $request, $session, $view, $db, $utils)
    {
        $this->view      = $view;
        $this->config    = $config;
        $this->request   = $request;
        $this->session   = $session;
        $this->db        = $db;
        $this->utils     = $utils;
    }

    /**
     * Get a parameter from app.php file
     *
     * @param mixed $paramName
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($paramName, $default = null)
    {
        if(isset($this->config[$paramName])) {
            return $this->config[$paramName];
        }

        return $default;
    }

    /**
     * Generate relative to site base URL
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     * @return string
     */
    protected function generateUrl($routeName, $uriParams = [], $getParams = [])
    {
        return $this->utils->generateUrl($routeName, $uriParams, $getParams);
    }

    /**
     * Generate full URL
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     * @return string
     */
    protected function generateFullUrl($routeName, $uriParams = [], $getParams = [])
    {
        return $this->utils->generateFullUrl($routeName, $uriParams, $getParams);
    }

    /**
     * Get site base URL
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->utils->getBaseUrl();
    }

    /**
     * Redirect to an internal page by route name
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     */
    protected function redirectTo($routeName, $uriParams = [], $getParams = [])
    {
        $this->utils->redirectTo($routeName, $uriParams, $getParams);
    }
}