<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Api\Auth\IpAddress as AuthIpAddress;
use Iset\Api\Controller\ServiceController;
use Iset\Api\Controller\CampaignController;
use Iset\Api\Controller\IpAddressController;

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
        
        # Register controllers
        $controllers->mount('/service', ServiceController::factory($this->_app));
        $controllers->mount('/campaign', CampaignController::factory($this->_app));
        $controllers->mount('/ipaddress', IpAddressController::factory($this->_app));
        
        return $controllers;
    }
}