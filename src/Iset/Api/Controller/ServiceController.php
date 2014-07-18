<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Model\Service;
use Iset\Model\ServiceTable;

class ServiceController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    protected $gateway = null;
    
    public function __construct(){}
    
    public function create()
    {
        # Getting provider
        $request = $this->getRequest();
        $service = new Service($this->getTableGateway());
        
        $service->name = $request->request->get('name');
        $service->key  = $request->request->get('key');
        
        $result = $service->save();
        
        if ($result === true) {
            $response = array('success'=>1,'key'=>$service->key,'token'=>$service->getToken());
            return $this->_app->json($response,Response::HTTP_CREATED);
        } elseif (is_array($result)) {
            $response = array_merge(array('success'=>0),$result);
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Unknow error');
            return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function getAll()
    {
        # Getting provider
        $gateway = $this->getTableGateway();
        
        $services = $gateway->fetchAll();
        
        if (count($services) > 0) {
            $response = array();
            
            foreach ($services as $service) {
                $data = $service->asArray();
                unset($data['id']);
                unset($data['token']);
                $response[] = $data;
            }
            
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }
    
    public function getRequest()
    {
        return $this->_app['request'];
    }
    
    public function getTableGateway()
    {
        if (is_null($this->gateway)) {
            $this->gateway = new ServiceTable($this->_app);
        }
        
        return $this->gateway;
    }
    
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
    		return $this->getAll();
    	});
    	
    	# Create a service
    	$container->post('/', function (){
    		return $this->create();
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