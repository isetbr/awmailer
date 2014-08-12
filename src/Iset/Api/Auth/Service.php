<?php

namespace Iset\Api\Auth;

use Silex\Application;
use Iset\Silex\Auth\AuthInterface;
use Iset\Model\ServiceTable;

/**
 * Service Authentication Provider
 * 
 * This is a provider that verify the authentication by caller service key and token
 * 
 * @package Iset\Api
 * @subpackage Auth
 * @namespace Iset\Api\Auth
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class Service
{
    /**
     * The instance of Service model
     * @var ServiceTable
     */
    protected $gateway = null;
    
    /**
     * The Constructor
     * 
     * @param Application $app
     */
    public function __construct(Application &$app)
    {
        # Initializing gateway
    	$this->gateway = new ServiceTable($app);
    }
    
    /**
     * Validate the Service Key and Service Token in database
     * 
     * @param string $key
     * @param string $token
     * @return boolean
     */
    public function validate($key,$token)
    {
        # Getting service 
        $service = $this->gateway->getService($key);
        
        if ($service) {
            if ($service->key == strtolower($key) && $service->getToken() == $token) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}