<?php

/**
 * AwMailer - The Awesome Mailer Service
 *
 * The AwMailer is a software developed for provide a mail service
 * which can be used by all services of iSET.
 *
 * The proposal of AwMailer is provide a mail tool that runs a daemon
 * as a observer for new services to be triggered, this services
 * runs natively on Linux servers independent of each others.
 *
 * This is a source code file, part of AwMailer product and this
 * source code is privately and only iSET and your developers
 * can use or distribute it.
 *
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 * @version $Id$
 *
 */

namespace Iset\Api\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Main Controller
 *
 * This is a main controller of API.
 *
 * @package Iset\Api
 * @subpackage Controller
 * @namespace Iset\Api\Controller
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class MainController implements ControllerProviderInterface
{
    /**
     * The instance of Application
     * @var \Silex\Application
     */
    protected $_app = null;

    /**
     * The Constructor
     */
    public function __construct() {}

    /**
     * Returns routes to connect to the given application.
     *
     * @see \Silex\ControllerProviderInterface::connect()
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $this->_app = &$app;

        return $this->register();
    }

    /**
     * Register all routes to the API methods
     *
     * @return \Silex\ControllerCollection
     */
    public function register()
    {
        $controllers = $this->_app['controllers_factory'];

        # Registering controllers
        $controllers->mount('/service', ServiceController::factory($this->_app));
        $controllers->mount('/campaign', CampaignController::factory($this->_app));
        $controllers->mount('/ipaddress', IpAddressController::factory($this->_app));
        $controllers->mount('/status', StatusController::factory($this->_app));

        return $controllers;
    }
}
