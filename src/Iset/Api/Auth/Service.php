<?php

namespace Iset\Api\Auth;

use Silex\Application;
use Iset\Silex\Auth\AuthInterface;
use Iset\Model\ServiceTable;

class Service implements AuthInterface
{
    
    protected $gateway = null;
    
    public function __construct(Application &$app)
    {
        # Initializing gateway
    	$this->gateway = new ServiceTable($app);
    }
    
    public function validate($key,$token)
    {
        # Getting service 
        $service = $this->gateway->getService($key);
        
        if ($service) {
            //var_dump($token);die;
            if ($service->key == strtolower($key) && $service->getToken() == $token) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public static function authenticate(Application &$app)
    {
        # Getting Service Key and Token from session
        $key   = $app['session']->get('Auth-Service-Key');
        $token = $app['session']->get('Auth-Token');
        
        # Validating
        if (!is_null($key) && !is_null($token)) {
            $instance = new self($app);
            return $instance->validate($key,$token);
        } else {
            return false;
        }
    }
}