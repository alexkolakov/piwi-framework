<?php

namespace Piwi;

class Utils
{
    /** @var  Request */
    protected $request;
    protected $routes;
    protected $config;

    private static $_instance = null;

    private function __construct($webFolder)
    {
        $app = App::Init($webFolder);
        $this->config  = $app->getConfig();
        $this->routes  = $app->getRoutes();
        $this->request = $app->getRequest();
    }

    /**
     * Initalization of the class
     *
     * @param string $webFolder
     * @return Utils
     */
    public static function Init($webFolder)
    {
        if (self::$_instance == null)
            self::$_instance = new self($webFolder);

        return self::$_instance;
    }

    /**
     * Generate relative to site base URL
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     * @return string
     */
    public function generateUrl($routeName, $uriParams = [], $getParams = [])
    {
        $routes = $this->routes;
        $url = '';

        if(array_key_exists($routeName, $routes))
        {
            if(!isset($routes[$routeName]['pattern']))
                throw new \InvalidArgumentException('No pattern for route: ' . $routeName, 500);

            $url = rtrim($routes[$routeName]['pattern'], '*');
            if(mb_strlen($routes[$routeName]['pattern']) > mb_strlen($url)
                && empty($uriParams))
            {
                throw new \InvalidArgumentException('No uri parameters provided for route: ' . $routeName, 500);
            }

            $url = rtrim($url, '/');

            foreach($uriParams as $value)
            {
                $url .= '/' . $value;
            }

            $hasGetParams = false;
            foreach($getParams as $key => $val)
            {
                if(!$hasGetParams)
                    $url .= '?';
                else
                    $url .= '&';

                $hasGetParams = true;

                $url .= $key . '=' . urlencode($val);
            }
        }

        return empty($url) ? '/' : $url;
    }

    /**
     * Generate full URL
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     * @return string
     */
    public function generateFullUrl($routeName, $uriParams = [], $getParams = [])
    {
        return  $this->getBaseUrl() . $this->generateUrl($routeName, $uriParams, $getParams);
    }

    /**
     * Get site base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->request->getScheme() . '://' . $this->request->getHost();
    }

    /**
     * Redirect to an internal page by route name
     *
     * @param string $routeName
     * @param array $uriParams
     * @param array $getParams
     */
    public function redirectTo($routeName, $uriParams = [], $getParams = [])
    {
        $this->header('Location: ' . $this->generateUrl($routeName, $uriParams, $getParams));
        exit;
    }

    /**
     * Check whether a real user or a bot made a request
     *
     * @return bool
     */
    public function isBrowser()
    {
        $httpUserAgent = $this->request->server('HTTP_USER_AGENT');
        return
            preg_match( '/^(Mozilla|Opera|PSP|Bunjalloo|wii)/', $httpUserAgent )
            && !preg_match( '/bot|crawl|fetch|slurp|spider|google|twitter|facebook/i', $httpUserAgent );
    }

    /**
     * Send HTTP header
     *
     * @param string $string
     * @param bool $replace
     * @param int|null $httpResponseHeader
     */
    public function header($string, $replace = true, $httpResponseHeader = null)
    {
        header($string, (bool)$replace, $httpResponseHeader);
    }

    public function getAbsolutePath($path)
    {
        $slashStarts = strpos($path, '/') === 0;

        $path  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach($parts as $part)
        {
            if('.' == $part)
                continue;

            if('..' == $part)
                array_pop($absolutes);
            else
                $absolutes[] = $part;
        }

        $absolutePath = implode(DIRECTORY_SEPARATOR, $absolutes);

        return ($slashStarts ? '/' : '') . $absolutePath;
    }

    public static function headerStatus($code)
    {
        if(!isset($_SERVER['SERVER_PROTOCOL']))
            return false;

        $codes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        ];

        if (!isset($codes[$code]))
            $code = 500;

        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $codes[$code], true, $code);

        return true;
    }

    /**
     * Normalize data
     *
     * @param mixed $data
     * @param string $types
     * @return mixed
     */
    public static function normalize($data, $types)
    {
        $types = explode('|', $types);

        if(is_array($types))
        {
            foreach($types as $v)
            {
                if($v == 'int')
                    $data = (int)$data;

                if($v == 'float')
                    $data = (float)$data;

                if($v == 'double')
                    $data = (double)$data;

                if($v == 'bool')
                    $data = (bool)$data;

                if($v == 'string')
                    $data = (string)$data;

                if($v == 'trim')
                    $data = trim($data);

                if($v == 'array')
                    $data = (array)$data;

                if($v == 'strip_tags')
                    $data = strip_tags($data);

                if($v == 'xss')
                    $data = self::xssClean($data);

            }
        }

        return $data;
    }

    /**
     * Code is taken from https://gist.github.com/1098477
     * @param string $data
     * @return string
     */
    public static function xssClean($data)
    {
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while($old_data !== $data);

        // we are done...
        return $data;
    }
}