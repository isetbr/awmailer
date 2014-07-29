<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Iset\Api\Auth\IpAddress as AuthIpAddress;
use Iset\Api\Auth\Service as AuthService;
use Iset\Model\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\ServiceTable;
use Iset\Model\QueueCollection;

class CampaignController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    protected $gateway = null;
    
    protected $collection = null;
    
    public function __construct(){}
    
    public function getAll()
    {
        $this->lock();
        
    	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
    }
    
    public function getOne($key)
    {
        $this->lock();
        
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
    
    public function create()
    {
        $this->lock();
        
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
    	$campaign->service  = (int)$service->id;
    	$campaign->subject  = $request->request->get('subject');
    	$campaign->body     = $request->request->get('body');
    	$campaign->headers  = $request->request->get('headers');
    	$campaign->external = $request->request->get('external');
    	
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
    
    public function update($key)
    {
        $this->lock();
        
        # Getting providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            # Getting request params
            $subject  = $request->request->get('subject');
            $body     = $request->request->get('body');
            $headers  = $request->request->get('headers');
            $external = $request->request->get('external');
            
            # Setting updates in campaign
            $campaign->subject = (!empty($subject)) ? $subject : $campaign->subject;
            $campaign->body    = (!empty($body)) ? $body : $campaign->body;
            $campaign->external = (!empty($external)) ? $external : $campaign->external;
            
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
    
    public function remove($key)
    {
        $this->lock();
        
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
    
    public function getStatus($key)
    {
        $this->lock();
        
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Fetching campaing details
        $campaign = $gateway->getCampaignByKey($key);
        
        # Verifying result
        if ($campaign) {
            $data = $campaign->asArray();
            $response = array(
            	'id'=>$data['id'],
                'key'=>$data['key'],
                'total'=>$data['total'],
                'sent'=>$data['sent'],
                'fail'=>$data['fail'],
                'progress'=>$data['progress'],
                'status'=>$data['status'],
                'external'=>$data['external'],
                'pid'=>$data['pid'],
            );
            return $this->_app->json($response,Response::HTTP_OK);
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    public function getQueue($key)
    {
        $this->lock();
        
        # Getting Collection
        $collection = $this->getCollection();
        
        # Retrieving data from db
        $result = $collection->fetch($key);
        
        # Verifying results found
        if (count($result) > 0) {
            return $this->_app->json($result,Response::HTTP_OK);
        } else {
            return new Response(null,Response::HTTP_NO_CONTENT);
        }
    }
    
    public function changeQueue($key)
    {
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
    
    public function fillQueue($key)
    {
        $this->lock();
        
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
        
        # Loop into stack for create queue
        foreach ($stack as $email) {
            $queue[] = array('campaign'=>$key,'email'=>$email);
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
    
    public function clearQueue($key)
    {
        $this->lock();
        
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
    
    public function changeStatusCampaign($key, $status = Campaign::STATUS_DEFAULT)
    {
        $this->lock();
        
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
                return new Response(null,Response::HTTP_NO_CONTENT);
            } else {
                return $this->_app->abort(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = array('success'=>0,'error'=>'Campaign not found');
            return $this->_app->json($response,Response::HTTP_OK);
        }
    }
    
    public function getRequest()
    {
        return $this->_app['request'];
    }
    
    public function getTableGateway()
    {
    	if (is_null($this->gateway)) {
    	    $this->gateway = new CampaignTable($this->_app);
    	}
    	
    	return $this->gateway;
    }
    
    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = new QueueCollection($this->_app);
        }
        
        return $this->collection;
    }
    
    public function connect(Application $app)
    {
        $this->_app = $app;
        return $this->register();
    }
    
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
        	return $this->changeStatusCampaign($key, Campaign::STATUS_START);
        });
        
        # Pause process
        $container->post('/{key}/pause', function ($key) {
        	return $this->changeStatusCampaign($key, Campaign::STATUS_PAUSE);
        });
        
        # Stop process
        $container->post('/{key}/stop', function ($key) {
        	return $this->changeStatusCampaign($key, Campaign::STATUS_STOP);
        });
        
        # Reset status
        $container->post('/{key}/reset', function ($key) {
        	return $this->changeStatusCampaign($key);
        });
        
        return $container;
    }
    
    public function lock()
    {
        # Temporary
        # Locking IpAddress and service
        if (!AuthIpAddress::authenticate($this->_app) || !AuthService::authenticate($this->_app)) {
            $response = new Response(null,Response::HTTP_FORBIDDEN);
            $response->send();
            die();
        }
    }
    
    public static function factory(Application &$app)
    {
        $instance = new self();
        return $instance->connect($app);
    }
}