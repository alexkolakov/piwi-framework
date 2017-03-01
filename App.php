<?php

namespace Piwi;

use Symfony\Component\Debug\ExceptionHandler;

class App
{
    const ERROR_CONTROLLER = 'PiwiApp\\Controllers\Error';
    const ERROR_ACTION = 'show';

    private static $instance;

    private $kernelFolder;
    private $webFolder;
    private $configPath;
    private $routesPath;
    private $session;
    private $request;
    private $db;
    private $config;

    protected $controller;
    protected $action;

    private function __construct($webFolder)
    {
        $this->kernelFolder = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->webFolder    = $webFolder;
        $this->configPath   = realpath($this->webFolder . '/../config.php');
        $this->routesPath   = realpath($this->webFolder . '/../routes.php');
    }


    /**
     * Initialize App
     *
     * @param string $indexPath
     * @return App
     */
    public static function Init($indexPath)
    {
        if (self::$instance == null)
            self::$instance = new self(realpath(dirname($indexPath)));

        return self::$instance;
    }

    /**
     * Run the app
     */
    public function run()
    {
        $uri = $this->getURI();
        $this->config = $this->getConfig();

        set_exception_handler([$this, 'exceptionHandler']);

        $this->session = new Session($this->config['session']['name'], $this->config['session']['lifetime'], $this->config['session']['path'], $this->config['session']['domain'], $this->config['session']['secure']);

        $this->dispatch($uri);
    }

    /**
     * Dispatch the request
     *
     * @param string $uri
     * @throws \Exception
     */
    private function dispatch($uri)
    {
        $uri = '/' . ltrim($uri, '/');

        $routes = $this->getRoutes();
        $actionParams = null;

        if(is_array($routes) && count($routes) > 0)
        {
            foreach($routes as $routeName => $params)
            {
                if(!isset($params['pattern']) || !isset($params['action']))
                    continue;

                $pattern = $params['pattern'];
                $patternLast2Chars = mb_substr($pattern, -2);
                if($patternLast2Chars == '/*')
                    $pattern = rtrim($pattern, '*');

                if(mb_stripos($uri, $pattern) === 0)
                {
                    $uriRest = urldecode(mb_substr($uri, mb_strlen($pattern)));
                    $actionParams = explode('/', $uriRest);

                    foreach($actionParams as $key => $val )
                    {
                        if(empty($val))
                            unset($actionParams[$key]);
                    }

                    if((!empty($actionParams) && $patternLast2Chars != '/*')
                        || (empty($actionParams) && $patternLast2Chars == '/*'))
                    {
                        continue;
                    }

                    $actionParts = explode('::',$params['action']);
                    $this->controller = $actionParts[0];
                    $this->action = $actionParts[1];

                    break;
                }
            }
        }
        else
            throw new \Exception('No routes available', 500);

        if(is_null($this->controller) || is_null($this->action))
            throw new \Exception('Route not found', 404);

        $this->request = Request::Init();
        $this->request->setPost($_POST);
        $this->request->setGet($_GET);

        $view = View::Init($this->config, $this->request, $this->session);

        $controllerClass = "PiwiApp\\Controllers\\" . $this->controller;

        if(!class_exists($controllerClass))
            throw new \Exception('No routes available', 500);

        $this->createDBConnection();

        $newController = new $controllerClass($this->config, $this->request, $this->session, $view, $this->db, $this->getUtils());

        if($newController instanceof BaseController && method_exists($newController, $this->action))
        {
            if(!$actionParams || (isset($actionParams[0]) && empty($actionParams[0])))
                $html = call_user_func_array([$newController, $this->action], [null]);
            else
                $html = call_user_func_array([$newController, $this->action], [$actionParams]);

            if(!is_string($html))
                throw new \Exception('Not valid response', 500);

            echo $html;
        }
        else
            throw new \Exception('The ' . $this->action . ' action is not implemented yet', 501);
    }

    /**
     * Get utils
     *
     * @return Utils
     */
    public function getUtils()
    {
        return Utils::Init($this->webFolder);
    }

    /**
     * Get config
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getConfig()
    {
        if($this->configPath && file_exists($this->configPath)) {
            $config = include $this->configPath;

            $missingConfigParams = $this->validateConfig($config);

            if(count($missingConfigParams) > 0) {
                throw new \Exception('Missing Config params', 500);
            }

            return $config;
        }

        return null;
    }

    /**
     * Get routes
     *
     * @return mixed|null
     */
    public function getRoutes()
    {
        if($this->routesPath && file_exists($this->routesPath)) {
            return include $this->routesPath;
        }

        return null;
    }

    /**
     * Gets Server URI
     *
     * @throws \Exception
     * @return string
     */
    private function getURI()
    {
        if(!isset($_SERVER['REQUEST_URI']))
            throw new \Exception('Invalid URI. You should enable REQUEST_URI option to be sent', 500);

        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUriArr = explode('?', $requestUri);
        return isset($requestUriArr[0]) ? ltrim($requestUriArr[0], '/') : '';
    }


    /**
     * Gets the DB Connection
     *
     * @return \PDO
     * @throws \Exception
     */
    public function createDBConnection()
    {
        $this->db = new \PDO($this->config['db']['connection_uri'], $this->config['db']['username'],
            $this->config['db']['password'], $this->config['db']['pdo_options']);

        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $this->db;
    }

    /**
     * Validate required config params
     *
     * @param array|null
     * @return array
     */
    private function validateConfig($config)
    {
        $ret = [];
        if(!isset($config['display_exceptions']))
            $ret[] = 'display_exceptions';

        if(!isset($config['templates_dir']))
            $ret[] = 'templates_dir';

        if(!isset($config['templates_dir_cache']))
            $ret[] = 'templates_dir_cache';

        if(!isset($config['twig_extensions']))
            $ret[] = 'twig_extensions';

        if(!isset($config['session']['name']))
            $ret[] = 'session:name';

        if(!isset($config['session']['lifetime']))
            $ret[] = 'session:lifetime';

        if(!isset($config['session']['path']))
            $ret[] = 'session:path';

        if(!isset($config['session']['domain']))
            $ret[] = 'session:domain';

        if(!isset($config['session']['secure']))
            $ret[] = 'session:secure';

        if(!isset($config['db']['connection_uri']))
            $ret[] = 'db:connection_uri';

        if(!isset($config['db']['username']))
            $ret[] = 'db:username';

        if(!isset($config['db']['password']))
            $ret[] = 'db:password';

        if(!isset($config['db']['pdo_options']))
            $ret[] = 'db:pdo_options';

        return $ret;
    }

    /**
     * @param \Exception | \ErrorException | \Error $ex
     */
    public function exceptionHandler($ex)
    {
        Utils::headerStatus($ex->getCode());


        if($this->config && $this->config['display_exceptions'] == true)
        {
            $handler = new ExceptionHandler(true);
            $handler->sendPhpResponse($ex);
        }
        else
        {
            $errorControllerName = self::ERROR_CONTROLLER;
            if(class_exists($errorControllerName))
            {
                $errorController = new $errorControllerName($ex);

                if($errorController instanceof BaseErrorController && method_exists($errorController, self::ERROR_ACTION))
                    call_user_func_array([$errorController, self::ERROR_ACTION], [null]);
                else
                    echo '<h1>' . $ex->getCode() . '</h1>';
            }

            else
                echo '<h1>' . $ex->getCode() . '</h1>';
        }

        exit;
    }

    /**
     * Get request
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function __destruct()
    {
        if($this->session != null)
            $this->session->saveSession();
    }
}