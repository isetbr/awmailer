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

use Silex\Application;
use Iset\Mongo\CollectionAbstract;

/**
 * Queue Collection Gateway
 * 
 * This is a collection gateway for Mail Queue
 * 
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA
 *
 */
class QueueCollection extends CollectionAbstract
{
    /**
     * The collection name
     * @var string
     */
    const COLLECTION_NAME = 'mail_queue';
    
    /**
     * The Constructor
     * 
     * @param Application $app
     */
    public function __construct(Application &$app)
    {
        parent::__construct($app,self::COLLECTION_NAME);
    }
    
    /**
     * Fetch emails from queue
     * 
     * @param string $key
     * @param string $email
     * @return array
     */
    public function fetch($key, $email = null, $limit = 0, $skip = 0)
    {
        # Mounting query
        $query = array('campaign'=>$key);
        if (!is_null($email)) {
            $query['email'] = $email;
        }
        
        # Retrieving data from database
        $result = $this->gateway->find($query);
        
        # Limiting results
        if ($limit > 0) {
            $result = $result->limit($limit);
        }
        
        # Skipping results
        if ($skip > 0) {
            $result = $result->skip($skip);
        }
        
        # Converting to associative array
        $result = $result->toArray();
        
        # Treatmenting result
        $stack = array();
        foreach ($result as $id => $row) {
            # Verifying if is a custom queue
            if (isset($row['vars']) || isset($row['headers'])) {
                $stack[] = array('email'=>$row['email'],'vars'=>$row['vars'],'headers'=>$row['headers']);
            } else {
                # Or simple queue
                $stack[] = $row['email'];
            }
        }
        
        return $stack;
    }
    
    /**
     * Verify if has any email in campaign queue
     * 
     * @param string $key
     * @return boolean
     */
    public function hasQueue($key)
    {
        # Mouting query
        $query = array('campaign'=>$key);
        
        # Retrieving data from database
        $result = $this->gateway->count($query);
        
        # Verifying result 
        return ($result > 0) ? true : false;
    }
    
    /**
     * Save queue in collection
     * 
     * @param array $stack
     * @return boolean
     */
    public function saveStack(array $stack)
    {
        # Inserting stack in collection
        $result = $this->gateway->batchInsert($stack);
           
        # Verifying result
        if ($result['ok'] == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Remove emails from queue
     * 
     * @param string $key
     * @param string $email
     * @return boolean
     */
    public function remove($key, $email = null)
    {
        # Mounting query
        $query = array('campaign'=>$key);
        if (!is_null($email)) {
            $query['email'] = $email;
        }
        
        # Trying to remove data
        $result = $this->gateway->remove($query);
        
        # Verifying result
        if (is_array($result)) {
            return true;
        } else {
            return false;
        }
    }
}