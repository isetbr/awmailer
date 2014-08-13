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

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Model\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\ServiceTable;
use Iset\Model\QueueCollection;

/**
 * Campaign Controller
 * 
 * This is a controller for campaign method in API.
 * 
 * @package Iset\Api
 * @subpackage Controller
 * @namespace Iset\Api\Controller
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class CampaignController implements ControllerProviderInterface
{
    /**
     * The instance of Application
     * @var \Silex\Application
     */
    protected $_app = null;
    
    /**
     * The instance of TableGateway
     * @var \Iset\Model\CampaignTable
     */
    protected $gateway = null;
    
    /**
     * The instance of Collection Gateway
     * @var \Iset\Model\QueueCollection
     */
    protected $collection = null;
    
    /**
     * The Constructor
     */
    public function __construct(){}
    
    /**
     * Get all campaigns (not implemented yet)
     */
    public function getAll()
    {
    	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
    }
    
    /**
     * Get one campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOne($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Fetching campaing details
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            $response = $campaign->asArray();
            unset($response['service']);
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Create a new campaign 
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create()
    {
        # Performing authentication
        $this->_app['auth.service']();
        
    	# Getting providers
    	$request = $this->getRequest();
    	$campaign = new Campaign($this->getTableGateway());
    	
    	# Initializing service 
    	$serviceTable = new ServiceTable($this->_app);
    	$service = $serviceTable->getService($request->headers->get('Auth-Service-Key'));
    	
    	# Validating service return
    	if (!$service) {
    	    $response = array('success'=>0,'error'=>'Service not found');
    	    return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
    	}
    	
    	# Getting request params
    	$subject      = $request->request->get('subject');
    	$body         = $request->request->get('body');
    	$headers      = $request->request->get('headers');
    	$user_vars    = $request->request->get('user_vars');
    	$user_headers = $request->request->get('user_headers');
    	$external     = $request->request->get('external');
    	
    	# Setting params on object
    	$campaign->service      = (int)$service->id;
    	$campaign->subject      = (!is_null($subject)) ? $subject : null;
    	$campaign->body         = (!is_null($body)) ? $body : null;
    	$campaign->headers      = (!is_null($headers)) ? $headers : array();
    	$campaign->user_vars    = (!is_null($user_vars)) ? $user_vars : 0;
    	$campaign->user_headers = (!is_null($user_headers)) ? $user_headers : 0;
    	$campaign->external     = (!is_null($external)) ? $external : null;
    	
    	# Saving campaign
    	$result = $campaign->save();
    	
    	# Verifying result
    	if ($result === true) {
    	    $response = array('success'=>1,'campaign'=>$campaign->getCampaignKey());
    	    return $this->_app->json($response,Response::HTTP_CREATED);
    	} elseif (is_array($result)) {
    	    $response = array_merge(array('success'=>0),$result);
    	    return $this->_app->json($response,Response::HTTP_OK);
    	} else {
    	    $response = array('success'=>0,'error'=>'Unknow error');
    	    return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
    	}
    }
    
    /**
     * Update an Campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Getting request params
            $subject      = $request->request->get('subject');
            $body         = $request->request->get('body');
            $headers      = $request->request->get('headers');
            $user_vars    = $request->request->get('user_vars');
            $user_headers = $request->request->get('user_headers');
            $external     = $request->request->get('external');
            
            # Setting updates in campaign
            $campaign->subject      = (!empty($subject)) ? $subject : $campaign->subject;
            $campaign->body         = (!empty($body)) ? $body : $campaign->body;
            $campaign->user_vars    = (!empty($user_vars)) ? $user_vars : $campaign->user_vars;
            $campaign->user_headers = (!empty($user_headers)) ? $user_headers : $campaign->user_headers;
            $campaign->external     = (!empty($external)) ? $external : $campaign->external;
            
            # Setting and cleaning headers
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (is_null($value)) {
                        unset($campaign->headers[$key]);
                    } else {
                        $campaign->headers[$key] = $value;
                    }
                }
            }
            
            # Saving campaign
            $result = $campaign->save();
            
            # Verifying result
            if ($result === true) {
                $response = array('success'=>1,'campaign'=>$campaign->getCampaignKey());
                return $this->_app->json($response,Response::HTTP_OK);
            } elseif (is_array($result)) {
                $response = array_merge(array('success'=>0),$result);
                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');
                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Remove an Campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function remove($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Removing campaign
            $result = $campaign->delete();
            
            # Verifying reuslt
            if ($result) {
                $response = array('success'=>1);
                return $this->_app->json($response,Response::HTTP_OK);
            } else {
                $response = array('success'=>0,'error'=>'Unknow error');
                return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Get the status of an Campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getStatus($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Fetching campaing details
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Verifying if campaign is in cache
            if ($this->_app['cache']->hasItem($key)) {
                # Getting data from cache
                $data = json_decode($this->_app['cache']->getItem($key),true);
                
                # Mounting response
                $response = array(
                    'id'=>$campaign->id,
                    'key'=>$campaign->getCampaignKey(),
                    'total'=>$data['total'],
                    'sent'=>$data['sent'],
                    'fail'=>$data['fail'],
                    'progress'=>$data['progress'],
                    'status'=>$data['status'],
                    'external'=>$campaign->external,
                    'pid'=>$data['pid'],
                    'cache'=>1,
                );
            } else {
                # Mouting response
                $response = array(
                    'id'=>$campaign->id,
                    'key'=>$campaign->getCampaignKey(),
                    'total'=>$campaign->total,
                    'sent'=>$campaign->sent,
                    'fail'=>$campaign->fail,
                    'progress'=>$campaign->progress,
                    'status'=>$campaign->status,
                    'external'=>$campaign->external,
                    'pid'=>$campaign->pid,
                );
            }
            
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Get status of multiple campaigns
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMultipleStatus()
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
    	
        # Getting request params
        $campaigns = $request->request->get('campaigns');
        
        # Initializing stack array for store results
        $stack = array();
        
        # Loop into campaigns for get status
        foreach ($campaigns as $key) {
            # Getting data from database
            $campaign = $gateway->getCampaignByKey($key);
            
            # Verifying if campaign was found
            if ($campaign) {
                # Verifying if campaign is in cache
                if ($this->_app['cache']->hasItem($key)) {
                    # Getting data from cache
                    $data = json_decode($this->_app['cache']->getItem($key),true);
                    
                    # From cache
                    $stack[$key] = array(
                        'id'=>$campaign->id,
                        'key'=>$campaign->getCampaignKey(),
                        'total'=>$data['total'],
                        'sent'=>$data['sent'],
                        'fail'=>$data['fail'],
                        'progress'=>$data['progress'],
                        'status'=>$data['status'],
                        'external'=>$campaign->external,
                        'pid'=>$data['pid'],
                        'cache'=>1,
                    );
                } else {
                    # From database
                    $stack[$key] = array(
                        'success'=>1,
                        'id'=>$campaign->id,
                        'key'=>$campaign->getCampaignKey(),
                        'total'=>$campaign->total,
                        'sent'=>$campaign->sent,
                        'fail'=>$campaign->fail,
                        'progress'=>$campaign->progress,
                        'status'=>$campaign->status,
                        'external'=>$campaign->external,
                        'pid'=>$campaign->pid,
                    );
                }
            } else {
                $stack[$key] = array('success'=>0,'error'=>'Campaign not found');
            }
        }
        
        return $this->_app->json($stack,Response::HTTP_OK);
    }
    
    /**
     * Get queue from an campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getQueue($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting Providers
        $collection = $this->getCollection();
        $request    = $this->getRequest();
        
        # Getting limit/skip params
        $limit = (int)$request->query->get('limit');
        $skip  = (int)$request->query->get('skip'); 
        
        # Retrieving data from db
        $result = $collection->fetch($key,null,$limit,$skip);
        
        # Verifying results found
        if (count($result) > 0) {
            return $this->_app->json($result,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }
    
    /**
     * Update a mail queue of an Campaign
     *  
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeQueue($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Retrieving header for select correct method
        # because the HTTP DELETE method doesn't allow
        # a request body, the use the put method using
        # a header for select the insertion or deletion
        #
        # XGH Process Certified
        $request = $this->getRequest();
        $delete = (int)$request->headers->get('Perform-Delete');
        
        # Verifying delete header
        if ($delete == 0) {
            return $this->fillQueue($key);
        } else {
            return $this->clearQueue($key);
        }
    }
    
    /**
     * Add more mails to a queue
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fillQueue($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting Providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        $collection = $this->getCollection();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        if (!$campaign) {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
        
        # Getting stack of emails
        $stack = $request->request->get('stack');
        $queue = array();
        
        # Verifying if campaign has user_vars or user_headers
        if ($campaign->user_vars == 1 || $campaign->user_headers == 1) {
            foreach ($stack as $row) {
                $queue[] = array(
                    'campaign'=>$key,
                    'email'=>$row['email'],
                    'vars'=>(!is_null($row['vars'])) ? $row['vars'] : array(),
                    'headers'=>(!is_null($row['headers'])) ? $row['headers'] : array(),
                );
            }
        } else {
            # Loop into stack for create simple queue
            foreach ($stack as $email) {
                $queue[] = array('campaign'=>$key,'email'=>$email);
            }
        }
        
        # Inserting queue in collection
        $result = $collection->saveStack($queue);
        
        # Verifying result
        if ($result) {
            # Updating total 
            $campaign->total = $campaign->total + count($queue);
            $campaign->save();
            
            $response = array('success'=>1);
            return $this->_app->json($response,Response::HTTP_CREATED);
        } else {
            $response = array('success'=>0,'error'=>'Unknow error');
            return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Remove mails from a queue
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function clearQueue($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        $collection = $this->getCollection();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        if (!$campaign) {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
        
        # Getting stack from request
        $stack = $request->request->get('stack');
        $error = 0;
        
        # Loop into stack to remove all emails
        foreach ($stack as $email) {
            $result = $collection->remove($key,$email);
            if (!$result) $error++;
        }
        
        # Verifying for errors
        if ($error == 0) {
            # Updating campaign total
            $campaign->total = $campaign->total - count($stack);
            $campaign->save();
            
            $response = array('success'=>1);
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Unknow error');
            return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Start campaign service
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startCampaign($key) 
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Changing status of campaign
            $this->changeStatusCampaign($key,Campaign::STATUS_START);
            
            return new Response(null,Response::HTTP_NO_CONTENT);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Pause service of campaign
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pauseCampaign($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Changing status of campaign
            $this->changeStatusCampaign($key,Campaign::STATUS_PAUSE);
        
            return new Response(null,Response::HTTP_NO_CONTENT);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Stop campaign process
     * 
     * @param string $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stopCampaign($key)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
    
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
    
        # Verifying result
        if ($campaign) {
            # Changing status of campaign
            $this->changeStatusCampaign($key,Campaign::STATUS_STOP);
        
            return new Response(null,Response::HTTP_NO_CONTENT);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    /**
     * Update the status of an Campaign
     * 
     * @param string  $key
     * @param integer $status
     * @return boolean
     */
    public function changeStatusCampaign($key, $status = Campaign::STATUS_DEFAULT)
    {
        # Performing authentication
        $this->_app['auth.service']();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Changing status
            $campaign->status = $status;
            
            # Saving campaign
            $result = $campaign->save();
            
            # Verifying result
            if ($result === true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Get the Request
     * 
     * @see \Iset\Silex\ControllerProviderInterface::getRequest()
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->_app['request'];
    }
    
    /**
     * Get the table gateway instance
     * 
     * @see \Iset\Silex\ControllerProviderInterface::getTableGateway()
     * @return \Iset\Model\CampaignTable
     */
    public function getTableGateway()
    {
    	if (is_null($this->gateway)) {
    	    $this->gateway = new CampaignTable($this->_app);
    	}
    	
    	return $this->gateway;
    }
    
    /**
     * Get the collection gateway instance
     * 
     * @return \Iset\Model\QueueCollection
     */
    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = new QueueCollection($this->_app);
        }
        
        return $this->collection;
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
     * @see \Iset\Silex\ControllerProviderInterface::register()
     * @return \Silex\ControllerCollection
     */
    public function register()
    {
        $container = $this->_app['controllers_factory'];
        
        # Retrieve all campaigns
        $container->get('/', function () {
        	return $this->getAll();
        });
        
        # Create a campaign
        $container->post('/', function () {
        	return $this->create();
        });
        
        # Get details from an campaign
        $container->get('/{key}', function ($key) {
        	return $this->getOne($key);
        });
        
        # Update a campaign
        $container->put('/{key}', function ($key) {
        	return $this->update($key);
        });
        
        # Remove a campaign
        $container->delete('/{key}', function ($key) {
        	return $this->remove($key);
        });
        
        # Get the status of campaign
        $container->get('/{key}/status', function ($key) {
        	return $this->getStatus($key);
        });
        
        # Get current queue list from a campaign
        $container->get('/{key}/queue', function ($key) {
        	return $this->getQueue($key);
        });
        
        # Add or remove destinations of queue list from campaign
        $container->put('/{key}/queue', function ($key) {
        	return $this->changeQueue($key);
        });
        
        # Start process
        $container->post('/{key}/start', function ($key) {
        	return $this->startCampaign($key);
        });
        
        # Pause process
        $container->post('/{key}/pause', function ($key) {
        	return $this->pauseCampaign($key);
        });
        
        # Stop process
        $container->post('/{key}/stop', function ($key) {
        	return $this->stopCampaign($key);
        });
        
        # Reset status
        $container->post('/{key}/reset', function ($key) {
        	return $this->changeStatusCampaign($key);
        });
        
        # Get status from multiple campaigns
        $container->post('/status', function() {
        	return $this->getMultipleStatus();
        });
        
        return $container;
    }
    
    /**
     * Provides a configured instance of CampaignController
     * 
     * @param Application $app
     * @return \Silex\ControllerCollection
     */
    public static function factory(Application &$app)
    {
        $instance = new self();
        return $instance->connect($app);
    }
}