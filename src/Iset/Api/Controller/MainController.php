<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

class MainController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    public function __construct(){}
	
    public function connect(Application $app)
    {
        $this->_app = &$app;
        return $this->register();
    }
    
    public function register()
    {
        $controllers = $this->_app['controllers_factory'];
        
        // Register controllers
        
        return $controllers;
    }
}