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
     * @return mixed
     */
    public function validate($ipaddress)
    {
        # Getting IpAddress
        $result = $this->gateway->getIpAddress($ipaddress);
        return ($result) ? $result : false;
    }
}