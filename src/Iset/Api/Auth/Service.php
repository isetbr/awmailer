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

namespace Iset\Api\Auth;

use Silex\Application;
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
     * @return mixed
     */
    public function validate($key,$token)
    {
        # Getting service 
        $service = $this->gateway->getService($key);
        
        if ($service) {
            if ($service->key == strtolower($key) && $service->getToken() == $token) {
                return $service;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}