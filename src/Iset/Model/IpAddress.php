<?php

namespace Iset\Model;

use Iset\Silex\Model\ModelInterface;
use Iset\Silex\Db\TableGatewayAbstract;
use Zend\Validator\Ip as IpAddressValidator;

/**
 * IpAddress
 * 
 * This is a object representation of an IpAddress
 * 
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class IpAddress implements ModelInterface
{
    /**
     * The IP address
     * @var string 
     */
    public $ipaddress = null;
    
    /**
     * The instance of TableGateway
     * @var \Iset\Silex\Db\TableGatewayAbstract
     */
    private $gateway = null;
    
    /**
     * The Constructor
     * 
     * @param TableGatewayAbstract $gateway
     * @return \Iset\Model\IpAddress
     */
    public function __construct(TableGatewayAbstract $gateway = null)
    {
        if (!is_null($gateway)) {
            $this->gateway = $gateway;
        }
         
        return $this;
    }
    
    /**
     * Fill object with an configured associative array
     * 
     * @param array $data
     * @see \Iset\Silex\Model\ModelInterface::exchangeArray()
     * @return \Iset\Model\IpAddress
     */
    public function exchangeArray(array $data)
    {
        $this->ipaddress = (!empty($data['ipaddress']) && !is_null($data['ipaddress'])) ? $data['ipaddress'] : null;
        
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
        return array('ipaddress'=>$this->ipaddress);
    }
    
    /**
     * Validate the IpAddress
     * 
     * @see \Iset\Silex\Model\ModelInterface::validate()
     * @return mixed
     */
    public function validate()
    {
        # Validating ip address
    	$validator = new IpAddressValidator();
    	if (!$validator->isValid($this->ipaddress)) {
    	    return array('error'=>'Invalid ip address');
    	}
    	
    	return true;
    }
    
    /**
     * Save IpAddress
     * 
     * @see \Iset\Silex\Model\ModelInterface::save()
     * @return mixed
     */
    public function save()
    {
    	$response = $this->gateway->saveIpAddress($this);
    	if (!is_null($response) && !is_array($response)) {
    	    return true;
    	} else {
    	    return $response;
    	}
    }
    
    /**
     * Delete IpAddress
     * 
     * @see \Iset\Silex\Model\ModelInterface::delete()
     * @return mixed
     */
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