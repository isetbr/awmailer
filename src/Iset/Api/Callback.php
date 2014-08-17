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

namespace Iset\Api;

use Slice\Http\Client as HttpClient;
use Iset\Resource\AbstractResource;
use Iset\Api\Resource\Service;

/**
 * Callback 
 * 
 * This is a Callback system of API that perform HTTP requests in notification
 * URL's of services notificating about some events occurred.
 *  
 * @package Iset
 * @subpackage Api
 * @namespace Iset\Api
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class Callback
{
    /**
     * The Service that will be called
     * @var \Iset\Api\Resource\Service
     */
    protected $_service = null;
    
    /**
     * The Resource that callback refers
     * @var \Iset\Resource\AbstractResource
     */
    protected $_resource = null;
    
    /**
     * The Constructor
     */
    public function __construct(){}
    
    /**
     * Set the service in Callback
     * 
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->_service = $service;
    }
    
    /**
     * Get the current callback Service
     * 
     * @return \Iset\Api\Resource\Service
     */
    public function getService()
    {
        return $this->_service;
    }
    
    /**
     * Set the resource that callback uses
     * 
     * @param AbstractResource $resource
     */
    public function setResource(AbstractResource $resource)
    {
        $this->_resource = $resource;
    }
    
    /**
     * Get the current resource
     * 
     * @return \Iset\Resource\AbstractResource
     */
    public function getResource()
    {
        return $this->_resource;
    }
    
    /**
     * Send a callback to the Service
     * 
     * @param array $data
     * @return boolean
     */
    public function send(array $data = array())
    {
        # Preparing data to send in callback
        $data = array_merge(array('resource'=>$this->_resource->getResourceName()),$data);
        
        # Initializing client
        $client = new HttpClient();
        
        # Configuring client instance
        $client->setUri($this->_service->notification_url);
        $client->setMethod(HttpClient::POST);
        $client->setRawData(json_encode($data));
        
        # Sending callback
        $response = $client->request();
        
        # Verifying response
        $response = json_decode($response->getBody(),true);
        if (strtolower($response['result']) == 'ok') {
            return true;
        } else {
            return false;
        }
    }
}