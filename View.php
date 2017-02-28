<?php

namespace Piwi;

class View
{
    /**
     * @var View|null
     */
    private static $_instance = null;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    
    private function __construct($config, Request $request, Session $session)
    {
        $loader = new \Twig_Loader_Filesystem($config['templates_dir']);
        $this->twig = new \Twig_Environment($loader, [
            'cache' => $config['templates_dir_cache'],
        ]);

        if(isset($config['twig_extensions'])
            && is_array($config['twig_extensions'])
            && !empty($config['twig_extensions']))
        {
            foreach($config['twig_extensions'] as $tE)
            {
                if(class_exists($tE))
                {
                    $instance = new $tE($config, $request, $session);
                    if($instance instanceof \Twig_Extension)
                        $this->twig->addExtension($instance);
                    else
                        unset($instance);
                }
            }
        }
    }
    
    /**
     * Initalization of the class
     *
     * @param array|null $config
     * @param Request $request
     * @param Session $session
     * @return View
     */
    public static function Init($config, Request $request, Session $session)
    {
        if (self::$_instance == null)
            self::$_instance = new self($config, $request, $session);
        
        return self::$_instance;
    }

    /**
     * Set Global twig variable
     *
     * @param int|string $name
     * @param mixed $value
     */
    public function setGlobal($name, $value)
    {
        $globals = $this->twig->getGlobals();
        if(isset($globals['globals']))
            $value = array_merge_recursive($globals['globals'], [$name => $value]);

        $this->twig->addGlobal('globals', $value);
    }

    /**
     * Get Global twig variable
     *
     * @param string $name
     * @return mixed
     */
    public function getGlobal($name)
    {
        $globals = $this->twig->getGlobals();
        if(isset($globals['globals'][$name]))
            return $globals['globals'][$name];

        return null;
    }

    public function render($name, $context = [])
    {
        return $this->twig->render($name, $context);
    }
    
}