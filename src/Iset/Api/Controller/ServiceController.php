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
use Iset\Api\Resource\Service;
use Iset\Model\ServiceTable;

/**
 * Service Controller
 *
 * This is a controller for service method in API.
 *
 * @package Iset\Api
 * @subpackage Controller
 * @namespace Iset\Api\Controller
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class ServiceController implements ControllerProviderInterface
{
    /**
     * The instance of Application
     * @var \Silex\Application
     */
    protected $_app = null;

    /**
     * The instance of TableGateway
     * @var \Iset\Model\ServiceTable
     */
    protected $gateway = null;

    /**
     * The Constructor
     */
    public function __construct() {}

    /**
     * Create a new service
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create()
    {
        # Getting provider
        $request = $this->getRequest();
        $service = new Service($this->getTableGateway());

        $service->name             = $request->request->get('name');
        $service->key              = $request->request->get('key');
        $service->notification_url = $request->request->get('notification_url');

        $result = $service->save();

        if ($result === true) {
            $response = array('success'=>1,'key'=>$service->key,'token'=>$service->getToken());

            return $this->_app->json($response,Response::HTTP_CREATED);
        } elseif (is_array($result)) {
            $response = array_merge(array('success'=>0),$result);
            $code     = (isset($response['details'])) ? Response::HTTP_BAD_REQUEST : Response::HTTP_INTERNAL_SERVER_ERROR;

            return $this->_app->json($response,$code);
        } else {
            $response = array('success'=>0,'error'=>'Unknow error');

            return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all services from database
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAll()
    {
        # Getting provider
        $gateway = $this->getTableGateway();

        $services = $gateway->fetchAll();

        if (count($services) > 0) {
            $response = array();

            foreach ($services as $service) {
                $data = $service->asArray();
                unset($data['id']);
                unset($data['token']);
                $response[] = $data;
            }

            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Get a service
     *
     * @param  string                                         $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOne($key)
    {
        # Getting provider
        $gateway = $this->getTableGateway();

        $service = $gateway->getService($key);

        if ($service) {
            $response = $service->asArray();
            unset($response['id']);
            unset($response['token']);

            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Service not found');

            return $this->_app->json($response,Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a service
     *
     * @param  string                                         $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update($key)
    {
        # Getting provider
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();

        # Getting service from gateway
        $service = $gateway->getService($key);

        if ($service) {
            # Getting request params
            $name         = $request->request->get('name');
            $key          = $request->request->get('key');
            $notification = $request->request->get('notification_url');

            $service->name = (!empty($name) && !is_null($name)) ? $name : $service->name;
            $service->key  = (!empty($key) && !is_null($key)) ? $key : $service->key;

            # Verifying if notification_url has sent
            if ($request->request->has('notification_url')) {
                if (is_null($notification) || empty($notification)) {
                    $service->notification_url = null;
                } else {
                    $service->notification_url = $notification;
                }
            }

            $result = $service->save();
            if ($result === true) {
                $response = array('success'=>1,'name'=>$service->name,'key'=>$service->key);

                return $this->_app->json($response,Response::HTTP_OK);
            } elseif (is_array($result)) {
                $response = array_merge(array('success'=>0),$result);
                $code     = (isset($response['details'])) ? Response::HTTP_BAD_REQUEST : Response::HTTP_INTERNAL_SERVER_ERROR;

                return $this->_app->json($response,$code);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');

                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Service not found');

            return $this->_app->json($response,Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove an service
     *
     * @param  string                                         $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function remove($key)
    {
        # Getting provider
        $gateway = $this->getTableGateway();

        # Getting service from gateway
        $service = $gateway->getService($key);

        if ($service) {
            $result = $service->delete();
            if ($result) {
                $response = array('success'=>1);

                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');

                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Service not found');

            return $this->_app->json($response,Response::HTTP_NOT_FOUND);
        }
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
     * Get the table gateway instance
     *
     * @see \Iset\ControllerProviderInterface::getTableGateway()
     * @return \Iset\Model\ServiceTable
     */
    public function getTableGateway()
    {
        if (is_null($this->gateway)) {
            $this->gateway = new ServiceTable($this->_app);
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
     * @see \Iset\ControllerProviderInterface::register()
     * @return \Silex\ControllerCollection
     */
    public function register()
    {
        $container = $this->_app['controllers_factory'];

        # Retrieve all services
        $container->get('/', function () {
            return $this->getAll();
        });

        # Create a service
        $container->post('/', function () {
            return $this->create();
        });

        # Get details about service
        $container->get('/{key}/', function ($key) {
            return $this->getOne($key);
        });

        # Update a service
        $container->put('/{key}/', function ($key) {
            return $this->update($key);
        });

        # Remove a service
        $container->delete('/{key}/', function ($key) {
            return $this->remove($key);
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
