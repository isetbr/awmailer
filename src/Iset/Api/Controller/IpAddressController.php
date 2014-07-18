<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Model\IpAddress;
use Iset\Model\IpAddressTable;

class IpAddressController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    protected $gateway = null;
    
    public function __construct(){}
    
    public function getAll()
    {
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting data from gateway
        $results = $gateway->fetchAll();
        
        # Verifying results
        if (count($results) > 0) {
            $response = array();
            
            foreach ($results as $ipaddress) {
                $response[] = $ipaddress->asArray();
            }
            
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }
    
    public function allow()
    {
        # Getting providers
        $request = $this->getRequest();
        $ipaddress = new IpAddress($this->getTableGateway());
        
        $ipaddress->ipaddress = $request->request->get('ipaddress');
        
        $result = $ipaddress->save();
        
        if ($result === true) {
            $response = array('success'=>1);
            return $this->_app->json($response,Response::HTTP_CREATED);
        } elseif (is_array($result)) {
            $response = array_merge(array('success'=>0),$result);
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Unknow error');
            return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function remove($ipaddress)
    {
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting ip address from gateway
        $ipaddress = $gateway->getIpAddress($ipaddress);
        
        # Verifying result
        if ($ipaddress) {
            $result = $ipaddress->delete();
            
            # Verifying result
            if ($result) {
                $response = array('success'=>1);
                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');
                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'IP address not found');
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
    	    $this->gateway = new IpAddressTable($this->_app);
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
    	
    	# Retrieve all ip address
    	$container->get('/', function () {
    		return $this->getAll();
    	});
    	
    	# Allow new ip address in system
    	$container->post('/', function() {
            return $this->allow();
    	});
    	
    	# Remove ip address from server
    	$container->delete('/{ipaddress}', function ($ipaddress) {
    		return $this->remove($ipaddress);
    	});
    	
    	return $container;
    }
    
    public static function factory(Application &$app)
    {
    	$instance = new self();
    	return $instance->connect($app);
    }
}