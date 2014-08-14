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

namespace Iset\Model;

use Iset\Silex\Model\ModelInterface;
use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\CampaignTable;

/**
 * Campaign
 * 
 * This is a object representation of an Campaign
 * 
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class Campaign implements ModelInterface
{
    /**
     * Available status code for campaign
     */
    const STATUS_DEFAULT = 0;
    const STATUS_START   = 1;
    const STATUS_PAUSE   = 2;
    const STATUS_STOP    = 3;
    const STATUS_DONE    = 4;
    
    /**
     * The ID of Campaign in database
     * @var integer
     */
    public $id = null;
    
    /**
     * The ID of service 
     * @var integer
     */
    public $service = null;
    
    /**
     * The key of Campaign
     * @var string
     */
    private $key = null;
    
    /**
     * The total count of queue
     * @var integer
     */
    public $total = 0;
    
    /**
     * The counter of sent mails
     * @var integer
     */
    public $sent = 0;
    
    /**
     * The counter of fail mails
     * @var integer
     */
    public $fail = 0;
    
    /**
     * The actual progress of Campaign
     * @var integer
     */
    public $progress = 0;
    
    /**
     * The status code of Campaign
     * @var integer
     */
    public $status = 0;
    
    /**
     * The subject of Campaign
     * @var string
     */
    public $subject = null;
    
    /**
     * The message body of Campaign
     * @var string
     */
    public $body = null;
    
    /**
     * Array of headers to sent in mails
     * @var array
     */
    public $headers = array();
    
    /**
     * Flag for user custom vars
     * @var integer
     */
    public $user_vars = 0;
    
    /**
     * Flag for user headers
     * @var integer
     */
    public $user_headers = 0;
    
    /**
     * The date when Campaign was created
     * @var string
     */
    public $date = null;
    
    /**
     * The external identification of Campaign
     * @var string
     */
    public $external = null;
    
    /**
     * The internal system PID
     * @var integer
     */
    public $pid = null;
    
    /**
     * The instance of TableGateway
     * @var \Iset\Silex\Db\TableGatewayAbstract
     */
    private $gateway = null;
    
    /**
     * The Constructor
     * 
     * @param TableGatewayAbstract $gateway
     * @return \Iset\Model\Campaign
     */
    public function __construct(TableGatewayAbstract $gateway = null)
    {
        if (!is_null($gateway)) {
            $this->gateway = $gateway;
        }
        
        return $this;
    }
    
    /**
     * Get the Campaign Key
     * 
     * @return string
     */
    public function getCampaignKey()
    {
        return $this->key;
    }
    
    /**
     * Get the json representation of headers array
     * @return string
     */
    public function getHeadersAsString()
    {
        return json_encode($this->headers);
    }
    
    /**
     * Fill object with an configured associative array
     * 
     * @param array $data
     * @see \Iset\Silex\Model\ModelInterface::exchangeArray()
     * @return \Iset\Model\Campaign
     */
    public function exchangeArray(array $data)
    {
        $this->id = (!empty($data['idcampaign'])) ? $data['idcampaign'] : null;
        $this->service = (!empty($data['idservice'])) ? (int)$data['idservice'] : null;
        $this->key = (!empty($data['key'])) ? $data['key'] : null;
        $this->total = (!empty($data['total_queue'])) ? (int)$data['total_queue'] : 0;
        $this->sent = (!empty($data['sent'])) ? (int)$data['sent'] : 0;
        $this->fail = (!empty($data['fail'])) ? (int)$data['fail'] : 0;
        $this->progress = (!empty($data['progress'])) ? (int)$data['progress'] : 0;
        $this->status = (!empty($data['status'])) ? (int)$data['status'] : 0;
        $this->subject = (!empty($data['subject'])) ? $data['subject'] : null;
        $this->body = (!empty($data['body'])) ? $data['body'] : null;
        $this->headers = (!empty($data['headers']) && is_string($data['headers'])) ? json_decode($data['headers'],true) : array();
        $this->user_vars = (!empty($data['user_vars'])) ? (int)$data['user_vars'] : 0;
        $this->user_headers = (!empty($data['user_headers'])) ? (int)$data['user_headers'] : 0;
        $this->date = (!empty($data['date'])) ? $data['date'] : date("Y-m-d");
        $this->external = (!empty($data['external'])) ? $data['external'] : null;
        $this->pid = (!empty($data['pid'])) ? $data['pid'] : null;
        
        return $this;
    }
    
    /**
     * Get the array representation of object
     * 
     * @see \Iset\Silex\Model\ModelInterface::asArray()
     * @return array
     */
    public function asArray()
    {
    	$data = array(
    		'id'=>$this->id,
    	    'service'=>$this->service,
    	    'key'=>$this->key,
    	    'total'=>$this->total,
    	    'sent'=>$this->sent,
    	    'fail'=>$this->fail,
    	    'progress'=>$this->progress,
    	    'status'=>$this->status,
    	    'subject'=>$this->subject,
    	    'body'=>$this->body,
    	    'headers'=>$this->headers,
    	    'user_vars'=>$this->user_vars,
    	    'user_headers'=>$this->user_headers,
    	    'date'=>$this->date,
    	    'external'=>$this->external,
    	    'pid'=>$this->pid,
    	);
    	
    	return $data;
    }
    
    /**
     * Validate the Campaign
     * 
     * @see \Iset\Silex\Model\ModelInterface::validate()
     * @return mixed
     */
    public function validate()
    {
        # Validating service id
        if (!is_null($this->service)) {
            if ($this->service instanceof Service) {
                if (is_null($this->service->id)) {
                    $this->service->save();
                }
                $this->service = (int)$this->service->id;
            } elseif (!is_integer($this->service)) {
                return array('error'=>'A service must be an instance of a Service object or the id of service');
            }
        } else {
            return array('error'=>'A Service must be specified');
        }
        
        # Generate key for campaign
        if (is_null($this->id) && is_null($this->key)) {
            $factor = '#'.$this->service.'?'.rand(1111,9999).'#'.time();
            $this->key = hash("ripemd128",bin2hex($factor));
        }
        
        # Treatment counters
        $this->total    = (int)$this->total;
        $this->sent     = (int)$this->sent;
        $this->fail     = (int)$this->fail;
        $this->progress = (int)$this->progress;
        
        # Validating subject
        if (!is_null($this->subject)) {
            $this->subject = (string)$this->subject;
        } else {
            return array('error'=>'You must set a subject');
        }
        
        # Validating body
        if (!is_null($this->body)) {
            $this->body = (string)$this->body;
        } else {
            return array('error'=>'You must set a body');
        }
        
        # Validating headers
        if (!is_array($this->headers)) {
            return array('error'=>'Headers must be an array');
        }
        
    	return true;
    }
    
    /**
     * Save Campaign
     * 
     * @see \Iset\Silex\Model\ModelInterface::save()
     * @return mixed
     */
    public function save()
    {
        $response = $this->gateway->saveCampaign($this);
        if (!is_null($this->id) && !is_array($response)) {
    	    return true;
    	} else {
    	    return $response;
    	}
    }
    
    /**
     * Delete Campaign
     * 
     * @see \Iset\Silex\Model\ModelInterface::delete()
     * @return mixed
     */
    public function delete()
    {
        $response = $this->gateway->deleteCampaign($this);
        if ($response) {
            unset($this);
            return true;
        } else {
            return false;
        }
    }
}