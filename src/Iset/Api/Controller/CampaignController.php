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

class CampaignController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    protected $gateway = null;
    
    public function __construct(){}
    
    public function getAll()
    {
    	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
    }
    
    public function getOne($idcampaign)
    {
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Fetching campaing details
        $campaign = $gateway->getCampaign($idcampaign);
        
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
    	# Getting providers
    	$request = $this->getRequest();
    	$campaign = new Campaign($this->getTableGateway());
    	
    	# Initializing service 
    	$serviceTable = new ServiceTable($this->_app);
    	$service = $serviceTable->getService($request->headers->get('Service-Key'));
    	
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
    	    $response = array('success'=>1,'campaign'=>(int)$campaign->id);
    	    return $this->_app->json($response,Response::HTTP_CREATED);
    	} elseif (is_array($result)) {
    	    $response = array_merge(array('success'=>0),$result);
    	    return $this->_app->json($response,Response::HTTP_OK);
    	} else {
    	    $response = array('success'=>0,'error'=>'Unknow error');
    	    return $this->_app->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
    	}
    }
    
    public function update($idcampaign)
    {
        # Getting providers
        $request = $this->getRequest();
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaign($idcampaign);
        
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
                $response = array('success'=>1,'campaign'=>(int)$campaign->id);
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
    
    public function remove($idcampaign)
    {
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Getting campaign
        $campaign = $gateway->getCampaign($idcampaign);
        
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
    
    public function changeStatusCampaign($idcampaign, $status = Campaign::STATUS_DEFAULT)
    {
        # Getting providers
        $gateway = $this->getTableGateway();
        
        # Treatmenting params
        if (is_null($status)) {
            $status = Campaign::STATUS_DEFAULT;
        }
        
        # Getting campaign
        $campaign = $gateway->getCampaign($idcampaign);
        
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
        $container->get('/{idcampaign}', function ($idcampaign) {
        	return $this->getOne($idcampaign);
        });
        
        # Update a campaign
        $container->put('/{idcampaign}', function ($idcampaign) {
        	return $this->update($idcampaign);
        });
        
        # Remove a campaign
        $container->delete('/{idcampaign}', function ($idcampaign) {
        	return $this->remove($idcampaign);
        });
        
        # Get current queue list from a campaign
        $container->get('/{idcampaing}/queue', function ($idcampaing) {
        	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
        });
        
        # Add destinations to an queue list from campaign
        $container->put('/{idcampaign}/queue', function ($idcampaign) {
        	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
        });
        
        # Remove destinations from an queue list
        $container->delete('/{idcampaign}/queue', function ($idcampaign) {
        	return $this->_app->abort(Response::HTTP_NOT_IMPLEMENTED);
        });
        
        # Start process
        $container->post('/{idcampaign}/start', function ($idcampaign) {
        	return $this->changeStatusCampaign($idcampaign, Campaign::STATUS_START);
        });
        
        # Pause process
        $container->post('/{idcampaign}/pause', function ($idcampaign) {
        	return $this->changeStatusCampaign($idcampaign, Campaign::STATUS_PAUSE);
        });
        
        # Stop process
        $container->post('/{idcampaign}/stop', function ($idcampaign) {
        	return $this->changeStatusCampaign($idcampaign, Campaign::STATUS_STOP);
        });
        
        # Reset status
        $container->post('/{idcampaign}/reset', function ($idcampaign) {
        	return $this->changeStatusCampaign($idcampaign);
        });
        
        return $container;
    }
    
    public static function factory(Application &$app)
    {
        # Temporary
        # Locking IpAddress and service
        if (!AuthIpAddress::authenticate($app) || !AuthService::authenticate($app)) {
            $response = new Response(null,Response::HTTP_FORBIDDEN);
            $response->send();
        }
        
        $instance = new self();
        return $instance->connect($app);
    }
}