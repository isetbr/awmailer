<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;

class ServiceController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    public function __construct(){}
    
    public function connect(Application $app)
    {
    	$this->_app = $app;
    	return $this->register();
    }
    
    public function register()
    {
    	$container = $this->_app['controllers_factory'];
    	
    	# Retrieve all services
    	$container->get('/', function (){
    		return 'Retrive all services';
    	});
    	
    	# Create a service
    	$container->post('/', function (){
    		return 'Create a service';
    	});
    	
    	# Get details about service
    	$container->get('/{key}', function ($key){
    		return 'Get service details';
    	});
    	
    	# Update a service
    	$container->put('/{key}', function ($key){
    		return 'Update a service';
    	});
    	
    	# Remove a service
    	$container->delete('/{key}', function ($key){
    		return 'Delete a service';
    	});
    	
    	return $container;
    }
    
    public static function factory(Application &$app)
    {
    	$instance = new self();
    	return $instance->connect($app);
    }
}