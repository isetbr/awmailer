<?php

namespace Iset\Model;

use Silex\Application;
use Iset\Silex\Mongo\CollectionAbstract;

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
    public function fetch($key, $email = null)
    {
        # Mounting query
        $query = array('campaign'=>$key);
        if (!is_null($email)) {
            $query['email'] = $email;
        }
        
        # Retrieving data from database
        $result = $this->gateway->find($query)->toArray();
        
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
        $result = $this->gateway->findAndRemove($query);
        
        # Verifying result
        if (is_array($result)) {
            return true;
        } else {
            return false;
        }
    }
}