<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;

class IpAddressController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    public function __construct(){}
    
    public function connect(Application $app)
    {
    	$this->_app = $app;
    	return $this->register();
    }
    
    private function register()
    {
    	$container = $this->_app['controllers_factory'];
    	
    	# Retrieve all ip address
    	$container->get('/', function () {
    		return 'Retrieve all ip addresses';
    	});
    	
    	# Allow new ip address in system
    	$container->post('/', function() {
            return 'Allow new ip address';
    	});
    	
    	# Remove ip address from server
    	$container->delete('/{ipaddress}', function ($ipaddress) {
    		return 'Remove an ip address';
    	});
    	
    	return $container;
    }
    
    public static function factory(Application &$app)
    {
    	$instance = new self();
    	return $instance->connect($app);
    }
}