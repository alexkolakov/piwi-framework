<?php

namespace Piwi;

class PiwiTwigExtension extends \Twig_Extension
{
    /** @var array */
    protected $config;

    /** @var Request */
    protected $request;

    /** @var Session */
    protected $session;

    public function __construct($config, Request $request, Session $session)
    {
        $this->config  = $config;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * Get Globals
     *
     * @return array
     */
    public function getGlobals()
    {
        if(isset($this->config['globals']))
        {
            return [
                'globals' => $this->config['globals']
            ];
        }

        return [];
    }

    /**
     * Get Twig Extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'common';
    }
}