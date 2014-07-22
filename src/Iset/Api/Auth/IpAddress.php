<?php

namespace Iset\Api\Auth;

use Silex\Application;
use Iset\Silex\Auth\AuthInterface;
use Iset\Model\IpAddressTable;

class IpAddress implements AuthInterface
{
    
    protected $gateway = null;
    
    public function __construct(Application &$app)
    {
        # Initializing gateway
    	$this->gateway = new IpAddressTable($app);
    }
    
    public function validate($ipaddress)
    {
        # Getting IpAddress
        $result = $this->gateway->getIpAddress($ipaddress);
        return ($result) ? true : false;
    }
    
    public static function authenticate(Application &$app)
    {
        # Getting IpAddress from session
        $ipaddress = $app['session']->get('Auth-IpAddress');
        
        # Validating
        if (!is_null($ipaddress)) {
            $instance = new self($app);
            return $instance->validate($ipaddress);
        } else {
            return false;
        }
    }
}