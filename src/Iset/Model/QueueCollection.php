<?php

namespace Iset\Model;

use Silex\Application;
use Iset\Silex\Mongo\CollectionAbstract;

class QueueCollection extends CollectionAbstract
{
    
    const COLLECTION_NAME = 'mail_queue';
    
    public function __construct(Application &$app)
    {
        parent::__construct($app,self::COLLECTION_NAME);
    }
    
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
            $stack[] = $row['email'];
        }
        
        return $stack;
    }
    
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