<?php

namespace Iset\Model;

use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\Service;

class ServiceTable extends TableGatewayAbstract
{
    
    const TABLE_NAME = 'service';
    
    public function fetchAll()
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "`";
    	$result = $this->tableGateway->fetchAll($query);
    	
    	# Stack for store result
    	$stack = array();
    	
    	foreach ($result as $row) {
    	    $service = new Service();
    	    $stack[] = $service->exchangeArray($row);
    	}
    	
    	return $stack;
    }
    
    public function getService($key){}
    
    public function saveService(Service &$service)
    {
        # Validating service
    	$result = $service->validate();
    	
    	if ($result === true) {
    	    if (is_null($service->id)) {
    	        # INSERT
    	        $query = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `key`=? OR `token`=?";
    	        $result = $this->tableGateway->fetchAll($query,array($service->key,$service->getToken()));
    	        
    	        # Verifying result
    	        if (count($result) == 0) {
    	            # Mounting query
    	            $query = "INSERT INTO `" . self::TABLE_NAME . "` (`name`,`key`,`token`) VALUES (?,?,?)";
    	            $data = array($service->name,$service->key,$service->getToken());
    	            
    	            # Inserting
    	            $result = $this->tableGateway->executeUpdate($query,$data);
    	            
    	            # Verifying result
    	            if ($result == 1) {
    	                $service->id = $this->tableGateway->lastInsertId();
    	                return $service;
    	            } else {
    	                return array('error'=>'An error ocourred at try to insert data in database');
    	            }
    	        } else {
    	            return array('error'=>'The service key or token is already in use');
    	        }
    	    } else {
    	        # UPDATE
    	    }
    	} else {
    	    return array('error'=>'Invalid service, see details for more information','details'=>$result['error']);
    	}
    }
    
    public function deleteService(Service $service){}
}