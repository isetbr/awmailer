<?php

namespace Iset\Model;

use Iset\Silex\Model\ModelInterface;
use Iset\Silex\Db\TableGatewayAbstract;
use Zend\Validator\Ip as IpAddressValidator;

class IpAddress implements ModelInterface
{
    
    public $ipaddress = null;
    
    private $gateway = null;
    
    public function __construct(TableGatewayAbstract $gateway = null)
    {
        if (!is_null($gateway)) {
            $this->gateway = $gateway;
        }
         
        return $this;
    }
    
    public function exchangeArray(array $data)
    {
        $this->ipaddress = (!empty($data['ipaddress']) && !is_null($data['ipaddress'])) ? $data['ipaddress'] : null;
        
        return $this;
    }
    
    public function asArray()
    {
        return array('ipaddress'=>$this->ipaddress);
    }
    
    public function validate()
    {
        # Validating ip address
    	$validator = new IpAddressValidator();
    	if (!$validator->isValid($this->ipaddress)) {
    	    return array('error'=>'Invalid ip address');
    	}
    	
    	return true;
    }
    
    public function save()
    {
    	$response = $this->gateway->saveIpAddress($this);
    	if (!is_null($response) && !is_array($response)) {
    	    return true;
    	} else {
    	    return $response;
    	}
    }
    
    public function delete()
    {
        $response = $this->gateway->deleteIpAddress($this);
        if ($response) {
            unset($this);
            return true;
        } else {
            return false;
        }
    }
}