<?php

namespace Iset\Api\Auth;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Iset\Silex\Auth\AuthInterface;
use Iset\Model\IpAddressTable;

/**
 * IpAddress Authentication Provider
 * 
 * This is a provider that verify the authentication by caller ip address
 * 
 * @package Iset\Api
 * @subpackage Auth
 * @namespace Iset\Api\Auth
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class IpAddress
{
    /**
     * The instance of IpAddress model
     * @var IpAddressTable
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
    	$this->gateway = new IpAddressTable($app);
    }
    
    /**
     * Validate the IpAddress in database
     * 
     * @param string $ipaddress
     * @return boolean
     */
    public function validate($ipaddress)
    {
        # Getting IpAddress
        $result = $this->gateway->getIpAddress($ipaddress);
        return ($result) ? true : false;
    }
}