<?php

namespace Iset\Model;

use Iset\Silex\Model\ModelInterface;
use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\CampaignTable;

class Campaign implements ModelInterface
{
    
    const STATUS_DEFAULT = 0;
    const STATUS_START   = 1;
    const STATUS_PAUSE   = 2;
    const STATUS_STOP    = 3;
    const STATUS_DONE    = 4;
    
    public $id = null;
    
    public $service = null;
    
    private $key = null;
    
    public $total = 0;
    
    public $sent = 0;
    
    public $fail = 0;
    
    public $progress = 0;
    
    public $status = 0;
    
    public $subject = null;
    
    public $body = null;
    
    public $headers = array();
    
    public $user_vars = 0;
    
    public $user_headers = 0;
    
    public $date = null;
    
    public $external = null;
    
    public $pid = null;
    
    private $gateway = null;
    
    public function __construct(TableGatewayAbstract $gateway = null)
    {
        if (!is_null($gateway)) {
            $this->gateway = $gateway;
        }
        
        return $this;
    }
    
    public function getCampaignKey()
    {
        return $this->key;
    }
    
    public function getHeadersAsString()
    {
        return json_encode($this->headers);
    }
    
    public function exchangeArray(array $data)
    {
        $this->id = (!empty($data['idcampaign'])) ? $data['idcampaign'] : null;
        $this->service = (!empty($data['idservice'])) ? $data['idservice'] : null;
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
    
    public function validate()
    {
        # Generate key for campaign
        if (is_null($this->id) && is_null($this->key)) {
            $factor = '#'.$this->service.'?'.rand(1111,9999).'#'.time();
            $this->key = hash("ripemd128",bin2hex($factor));
        }
        
    	return true;
    }
    
    public function save()
    {
        $response = $this->gateway->saveCampaign($this);
        if (!is_null($this->id) && !is_array($response)) {
    	    return true;
    	} else {
    	    return $response;
    	}
    }
    
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