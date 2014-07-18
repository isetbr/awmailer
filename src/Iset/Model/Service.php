<?php

namespace Iset\Model;

use Iset\Silex\Model\ModelInterface;
use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\ServiceTable;

class Service implements ModelInterface
{
    
    public $id = null;
    
    public $name = null;
    
    public $key = null;
    
    private $token = null;
    
    private $gateway = null;
    
    public function __construct(TableGatewayAbstract $gateway = null)
    {
    	if (!is_null($gateway)) {
    	    $this->gateway = $gateway;
    	}
    	
    	return $this;
    }
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function exchangeArray(array $data)
    {
        $this->id    = (!empty($data['idservice'])) ? (int)$data['idservice'] : null;
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
        $this->key   = (!empty($data['key'])) ? $data['key'] : null;
        $this->token = (!empty($data['token'])) ? $data['token'] : null;
        
        return $this;
    }
    
    public function asArray()
    {
    	$data = array(
    	    'id'=>$this->id,
    	    'name'=>$this->name,
    	    'key'=>$this->key,
    	    'token'=>$this->token
    	);
    	
    	return $data;
    }
    
    public function validate()
    {
        # Validating service name
        if (is_null($this->name)) {
            return array('error'=>'A service name must be specified');
        } elseif (!is_string($this->name)) {
            return array('error'=>'A service name must be an string');
        }
        
        # Validating service key
        if (is_null($this->key)) {
            return array('error'=>'A service key must be specified');
        } elseif (!is_string($this->key)) {
            return array('error'=>'A service key must be an string');
        } else {
            $this->key = strtolower($this->key);
        }
        
        # Validating token
        if (is_null($this->token)) {
            if (is_null($this->id)) {
                $this->generateServiceToken();
            } else {
                return array('error'=>'Service token cannot be regenerated.');
            }
        }
        
        return true;
    }
    
    public function save()
    {
    	$response = $this->gateway->saveService($this);
    	if (!is_null($this->id) && !is_array($response)) {
    	    return true;
    	} else {
    	    return $response;
    	}
    }
    
    public function delete()
    {
    	$response = $this->gateway->deleteService($this);
    	if ($response) {
    	    unset($this);
    	    return true;
    	} else {
    	    return false;
    	}
    }
    
    private function generateServiceToken()
    {
        $this->token = hash('whirlpool',$this->key . '?' .rand(111111111,999999999));
    }
}