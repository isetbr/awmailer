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

namespace Iset;

use Silex\Application;
use Silex\ControllerProviderInterface as SilexControllerProviderInterface;

/**
 * Controller Provider Interface
 * 
 * This is a extension of ControllerProviderInterface provided by Silex framework
 * customized for this application 
 * 
 * @package Iset
 * @namespace Iset
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
interface ControllerProviderInterface extends SilexControllerProviderInterface
{
    /**
     * Get the Request service
     * 
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest();
    
    /**
     * Get the instance of TableGateway
     * 
     * @return \Iset\Db\TableGatewayAbstract
     */
    public function getTableGateway();
    
    /**
     * Register all routes with the controller methods
     * 
     * @return \Silex\ControllerCollection
     */
    public function register();
    
    /**
     * Static Factory Method
     * 
     * This is a static method that provides a configured instance of Controller
     * 
     * @param Application $app
     */
    public static function factory(Application &$app);
}