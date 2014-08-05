<?php

namespace Iset\Model;

use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\IpAddress;

/**
 * IpAddress Table Gateway
 *
 * This is a table gateway provider for IpAddress objects
 *
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class IpAddressTable extends TableGatewayAbstract
{
    /**
     * The table name
     * @var string
     */
    const TABLE_NAME = 'ipaddress';
    
    /**
     * Fetch all ip addresses from database
     * 
     * @return array
     */
    public function fetchAll()
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "`";
        $result = $this->tableGateway->fetchAll($query);
         
        # Stack for store result
        $stack = array();
         
        foreach ($result as $row) {
            $ipaddress = new IpAddress();
            $stack[] = $ipaddress->exchangeArray($row);
        }
             
        return $stack;
    }
    
    /**
     * Fetch an IpAddress from database
     * 
     * @param string $ipaddress
     * @return \Iset\Model\IpAddress
     */
    public function getIpAddress($ipaddress)
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `ipaddress`=?";
        $result = $this->tableGateway->fetchAssoc($query,array($ipaddress));
         
        # Verifying result
        if ($result) {
            $ipaddress = new IpAddress($this);
            return $ipaddress->exchangeArray($result);
        } else {
            return false;
        }
    }
    
    /**
     * Save an IpAddress
     * 
     * @param IpAddress $ipaddress
     * @return mixed
     */
    public function saveIpAddress(IpAddress &$ipaddress)
    {
        # Validating Ip Address
    	$result = $ipaddress->validate();
    	
    	# Verifying result
    	if ($result === true) {
    	    # Verifying if IpAddress exists in database
    	    $result = $this->getIpAddress($ipaddress->ipaddress);
    	    
    	    # Verifying result
    	    if (!$result) {
    	        # Inserting
    	        $query = "INSERT INTO `" . self::TABLE_NAME . "` (`ipaddress`) VALUES (?)";
    	        $result = $this->tableGateway->executeUpdate($query,array($ipaddress->ipaddress));
    	        
    	        # Verifying result
    	        if ($result == 1) {
    	            return $ipaddress;
    	        } else {
    	            return array('error'=>'An error ocurred at try to insert data in database');
    	        }
    	    } else {
    	        return array('error'=>'IP Address already allowed');
    	    }
    	} else {
    	    return array('error'=>'Invalid ip address');
    	}
    }
    
    /**
     * Delete an IpAddress
     * 
     * @param IpAddress $ipaddress
     * @return boolean
     */
    public function deleteIpAddress(IpAddress &$ipaddress)
    {
        # Mounting and executing query
        $query = "DELETE FROM `" . self::TABLE_NAME . "` WHERE `ipaddress`=?";
        $result = $this->tableGateway->executeUpdate($query,array($ipaddress->ipaddress));
         
        # Verifying result
        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
}