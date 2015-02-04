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
use Iset\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Status Controller
 *
 * This is a controller for status resource in API
 *
 * @package Iset\Api
 * @subpackage Controller
 * @namespace Iset\Api\Controller
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 */
class StatusController implements ControllerProviderInterface
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
     * Verify if daemon is running an return the current status
     *
     * @return Response
     */
    public function getStatus()
    {
        $path = $this->_app['log_path'];
        $file = $this->_app['config']['log']['daemon']['options']['stream'];

        $timestamp = filemtime($path . $file);
        $last_update = new \DateTime();
        $last_update->setTimestamp($timestamp);

        $now = new \DateTime();

        $diff = $now->diff($last_update);
        if ($diff->s > (int) $this->_app['config']['service']['daemon']['delay']) {
            # notification emails
            mail($this->_app['config']['api']['notification']['emails'],
                 'AwMailer Tango Down',
                 'Daemon not running, verified at ' . $now->format(\DateTime::ISO8601),
                 "Content-Type: text/plain\r\nX-Priority: 1 (Higuest)\r\nImportance: High\r\n");
            $response = new Response(null,Response::HTTP_SERVICE_UNAVAILABLE);
        } else {
            $response = new Response(null,Response::HTTP_OK);
        }

        return $response;
    }

    /**
     * Get the Request
     *
     * @see \Iset\ControllerProviderInterface::getRequest()
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->_app['request'];
    }

    /**
     * Not implemented
     */
    public function getTableGateway() {}

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
     * @see \Iset\ControllerProviderInterface::register()
     * @return \Silex\ControllerCollection
     */
    public function register()
    {
        $container = $this->_app['controllers_factory'];

        $container->get('/', function () {
            return $this->getStatus();
        });

        return $container;
    }

    /**
     * Provides a configured instance of ServiceController
     *
     * @param  Application                 $app
     * @return \Silex\ControllerCollection
     */
    public static function factory(Application &$app)
    {
        # Initializing instance
        $instance = new self();

        return $instance->connect($app);
    }
}
