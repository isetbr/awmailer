<?php

namespace Iset\Silex\Db;

use Silex\Application;

abstract class TableGatewayAbstract
{
    
    protected $tableGateway = null;
    
    public function __construct(Application &$app)
    {
        $this->tableGateway = &$app['db'];
    }
}