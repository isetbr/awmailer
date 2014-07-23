<?php

namespace Iset\Model;

use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\Campaign;

class CampaignTable extends TableGatewayAbstract
{
    
    const TABLE_NAME = 'campaign';
    
    public function fetchAll()
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "`";
    	$result = $this->tableGateway->fetchAll($query);
    	
    	# Stack for store result
    	$stack = array();
    	
    	foreach ($result as $row) {
    	    $campaign = new Campaign();
    	    $stack[] = $campaign->exchangeArray($row);
    	}
    	
    	return $stack;
    }
    
    public function getCampaign($idcampaign)
    {
        # Retrieving data from database
    	$query = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `idcampaign`=?";
    	$result = $this->tableGateway->fetchAssoc($query,array($idcampaign));
    	
    	# Verifying result
    	if ($result) {
    	    $campaign = new Campaign($this);
    	    return $campaign->exchangeArray($result);
    	} else {
    	    return false;
    	}
    }
    
    public function saveCampaign(Campaign &$campaign)
    {
        # Validating service
    	$result = $campaign->validate();
    	
    	if ($result === true) {
    	    if (is_null($campaign->id)) {
    	        # INSERT
    	        # Mounting query
    	        $query = "INSERT INTO `" . self::TABLE_NAME . "` (`idservice`,`total_queue`,`sent`,`fail`,`progress`,`status`,`subject`,`body`,`headers`,`date`,`external`)  VALUES (?,?,?,?,?,?,?,?,?,?,?)";
	            $data = array(
	                $campaign->service,
	                $campaign->total,
	                $campaign->sent,
	                $campaign->fail,
	                $campaign->progress,
	                $campaign->status, 
	                $campaign->subject,
	                $campaign->body,
	                $campaign->getHeadersAsString(),
	                date("Y-m-d"),
	                $campaign->external
	            );
    	        
	            # Inserting
	            $result = $this->tableGateway->executeUpdate($query,$data);
	            
	            # Verifying result
	            if ($result == 1) {
	                $campaign->id = $this->tableGateway->lastInsertId();
	                return $campaign;
	            } else {
	                return array('error'=>'An error ocourred at try to insert data in database');
	            }
    	    } else {
    	        # UPDATE
    	        # Mouting query
    	        $query = "UPDATE `" . self::TABLE_NAME . "` SET
    	            `idservice`=?,
    	            `total_queue`=?,
    	            `sent`=?,
    	            `fail`=?,
    	            `progress`=?,
    	            `status`=?, 
    	            `subject`=?,
    	            `body`=?,
    	            `headers`=?,
    	            `date`=?,
    	            `external`=? 
    	            WHERE `idcampaign`=?";
    	        $data = array(
    	            $campaign->service,
    	            $campaign->total,
    	            $campaign->sent,
    	            $campaign->fail,
    	            $campaign->progress,
    	            $campaign->status,
    	            $campaign->subject,
    	            $campaign->body,
    	            $campaign->getHeadersAsString(),
    	            date("Y-m-d"),
    	            $campaign->external,
    	            $campaign->id
    	        );
    	        
    	        # Updating
    	        $result = $this->tableGateway->executeUpdate($query,$data);
    	        
    	        # Verifying result
    	        if ($result == 1) {
    	            return $campaign;
    	        } elseif ($result == 0) {
    	            return array('error'=>'No changes');
    	        } else {
    	            return array('error'=>'An error ocourred at try to update data in database');
    	        }
    	    }
    	} else {
    	    return array('error'=>'Invalid campaign, see details for more information','details'=>$result['error']);
    	}
    }
    
    public function deleteCampaign(Campaign &$campaign)
    {
    	# Mounting and executing query
    	$query = "DELETE FROM `" . self::TABLE_NAME . "` WHERE `idcampaign`=?";
    	$result = $this->tableGateway->executeUpdate($query,array($campaign->id));
    	
    	# Verifying result
    	if ($result == 1) {
    	    return true;
    	} else {
    	    return false;
    	}
    }
}