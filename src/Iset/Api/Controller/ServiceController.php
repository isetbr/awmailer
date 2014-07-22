<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Api\Auth\IpAddress as AuthIpAddress;
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
    
    public function getOne($key)
    {
        # Getting provider
        $gateway = $this->getTableGateway();
        
        $service = $gateway->getService($key);
        
        if ($service) {
            $response = $service->asArray();
            unset($response['id']);
            unset($response['token']);
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Service not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    public function update($key)
    {
        # Getting provider
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        
        # Getting service from gateway
        $service = $gateway->getService($key);
        
        if ($service) {
            # Getting request params
            $name = $request->request->get('name');
            $key  = $request->request->get('key');
            
            $service->name = (!empty($name) && !is_null($name)) ? $name : $service->name;
            $service->key  = (!empty($key) && !is_null($key)) ? $key : $service->key;
            
            $result = $service->save();
            if ($result === true) {
                $response = array('success'=>1,'name'=>$service->name,'key'=>$service->key);
                return $this->_app->json($response,Response::HTTP_OK);
            } elseif (is_array($result)) {
                $response = array_merge(array('success'=>0),$result);
                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');
                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Service not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    public function remove($key)
    {
        # Getting provider
        $gateway = $this->getTableGateway();
        
        # Getting service from gateway
        $service = $gateway->getService($key);
        
        if ($service) {
            $result = $service->delete();
            if ($result) {
                $response = array('success'=>1);
                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');
                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Service not found');
            return $this->_app->json($response,Response::HTTP_OK);
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
    		return $this->getOne($key);
    	});
    	
    	# Update a service
    	$container->put('/{key}', function ($key){
    		return $this->update($key);
    	});
    	
    	# Remove a service
    	$container->delete('/{key}', function ($key){
    		return $this->remove($key);
    	});
    	
    	return $container;
    }
    
    public static function factory(Application &$app)
    {
        # Temporary
        # Locking IpAddress
        if (!AuthIpAddress::authenticate($app)) {
            $response = new Response(null,Response::HTTP_FORBIDDEN);
            $response->send();
        }
        
        # Initializing instance
    	$instance = new self();
    	return $instance->connect($app);
    }
}