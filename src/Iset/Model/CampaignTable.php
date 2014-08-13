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

use Iset\Silex\Db\TableGatewayAbstract;
use Iset\Model\Campaign;

/**
 * Campaign Table Gateway
 * 
 * This is a table gateway provider for Campaign objects
 * 
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class CampaignTable extends TableGatewayAbstract
{
    /**
     * The table name
     * @var string
     */
    const TABLE_NAME = 'campaign';
    
    /**
     * Fetch all campaigns from database
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
    	    $campaign = new Campaign($this);
    	    $stack[] = $campaign->exchangeArray($row);
    	}
    	
    	return $stack;
    }
    
    /**
     * Fetch campaigns by status
     * 
     * @param integer $status
     * @return array
     */
    public function getCampaignsByStatus($status = Campaign::STATUS_DEFAULT)
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `status`=?";
        $result = $this->tableGateway->fetchAll($query,array($status));
        
        # Stack for store result
        $stack = array();
        foreach ($result as $row) {
            $campaign = new Campaign($this);
            $stack[] = $campaign->exchangeArray($row);
        }
        
        return $stack;
    }
    
    /**
     * Get Campaign by ID
     * 
     * @param integer $idcampaign
     * @return \Iset\Model\Campaign
     */
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
    
    /**
     * Get Campaign by Key
     * 
     * @param string $key
     * @return \Iset\Model\Campaign
     */
    public function getCampaignByKey($key)
    {
        # Retrieving data from database
        $query = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `key`=?";
        $result = $this->tableGateway->fetchAssoc($query,array($key));
         
        # Verifying result
        if ($result) {
            $campaign = new Campaign($this);
            return $campaign->exchangeArray($result);
        } else {
            return false;
        }
    }
    
    /**
     * Save an Campaign
     * 
     * @param Campaign $campaign
     * @return mixed
     */
    public function saveCampaign(Campaign &$campaign)
    {
        # Validating service
    	$result = $campaign->validate();
    	
    	if ($result === true) {
    	    if (is_null($campaign->id)) {
    	        # INSERT
    	        # Mounting query
    	        $query = "INSERT INTO `" . self::TABLE_NAME . "` (`idservice`,`key`,`total_queue`,`sent`,`fail`,`progress`,`status`,`subject`,`body`,`headers`,`user_vars`,`user_headers`,`date`,`external`,`pid`)  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	            $data = array(
	                $campaign->service,
	                $campaign->getCampaignKey(),
	                $campaign->total,
	                $campaign->sent,
	                $campaign->fail,
	                $campaign->progress,
	                $campaign->status, 
	                $campaign->subject,
	                $campaign->body,
	                $campaign->getHeadersAsString(),
	                $campaign->user_vars,
	                $campaign->user_headers,
	                date("Y-m-d"),
	                $campaign->external,
	                $campaign->pid,
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
    	            `key`=?,
    	            `total_queue`=?,
    	            `sent`=?,
    	            `fail`=?,
    	            `progress`=?,
    	            `status`=?, 
    	            `subject`=?,
    	            `body`=?,
    	            `headers`=?,
    	            `user_vars`=?,
    	            `user_headers`=?,
    	            `date`=?,
    	            `external`=?,
    	            `pid`=? 
    	            WHERE `idcampaign`=?";
    	        $data = array(
    	            $campaign->service,
    	            $campaign->getCampaignKey(),
    	            $campaign->total,
    	            $campaign->sent,
    	            $campaign->fail,
    	            $campaign->progress,
    	            $campaign->status,
    	            $campaign->subject,
    	            $campaign->body,
    	            $campaign->getHeadersAsString(),
    	            $campaign->user_vars,
    	            $campaign->user_headers,
    	            date("Y-m-d"),
    	            $campaign->external,
    	            $campaign->pid,
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
    
    /**
     * Delete an Campaign
     * 
     * @param Campaign $campaign
     * @return boolean
     */
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