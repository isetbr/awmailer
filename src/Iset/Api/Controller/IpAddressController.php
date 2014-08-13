<?php

/**
 * M4A1 - The Awesome Mailer Service
 *
 * The M4A1 is a software developed for provide a mail service
 * which can be used by all services of iSET.
 *
 * The proposal of M4A1 is provide a mail tool that runs a daemon
 * as a observer for new services to be triggered, this services
 * runs natively on Linux servers independent of each others.
 *
 * This is a source code file, part of M4A1 product and this
 * source code is privately and only iSET and your developers
 * can use or distribute it.
 *
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 * @version $Id$
 *
 */

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Model\IpAddress;
use Iset\Model\IpAddressTable;

/**
 * IpAddress Controller
 *
 * This is a controller for ipaddress method in API.
 *
 * @package Iset\Api
 * @subpackage Controller
 * @namespace Iset\Api\Controller
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class IpAddressController implements ControllerProviderInterface
{
    /**
     * The instance of Application
     * @var \Silex\Application
     */
    protected $_app = null;
    
    /**
     * The instance of TableGateway
     * @var \Iset\Model\IpAddressTable
     */
    protected $gateway = null;
    
    /**
     * The Constructor
     */
    public function __construct(){}
    
    /**
     * Get all ip addresses from database
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                $ipaddress = $ipaddress->asArray();
                $response[] = $ipaddress['ipaddress'];
            }
            
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }
    
    /**
     * Allow a new ip address to perform calls in API
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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
    
    /**
     * Remove an ip address from database
     * 
     * @param string $ipaddress
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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
    
    /**
     * Get the Request
     * 
     * @see \Iset\Silex\ControllerProviderInterface::getRequest()
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->_app['request'];
    }
    
    /**
     * Get the table gateway instance
     * 
     * @see \Iset\Silex\ControllerProviderInterface::getTableGateway()
     * @return \Iset\Model\IpAddressTable
     */
    public function getTableGateway()
    {
    	if (is_null($this->gateway)) {
    	    $this->gateway = new IpAddressTable($this->_app);
    	}
    	
    	return $this->gateway;
    }
    
    /**
     * Returns routes to connect to the given application.
     * 
     * @see \Silex\ControllerProviderInterface::connect()
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
    	$this->_app = $app;
    	
    	return $this->register();
    }
    
    /**
     * Register all routes with the controller methods
     * 
     * @see \Iset\Silex\ControllerProviderInterface::register()
     * @return \Silex\ControllerCollection
     */
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
    
    /**
     * Provides a configured instance of IpAddressController
     * 
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public static function factory(Application &$app)
    {
    	$instance = new self();
    	return $instance->connect($app);
    }
}