<?php

namespace Iset\Silex\Model;

use Iset\Silex\Db\TableGatewayAbstract;

interface ModelInterface
{
    
    public function __construct(TableGatewayAbstract $gateway = null);
    
    public function exchangeArray(array $data);
    
    public function asArray();
    
    public function validate();
    
    public function save();
    
    public function delete();
}