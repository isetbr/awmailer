<?php

namespace Iset\Silex\Db;

use Silex\Application;

/**
 * Abstract Table Gateway
 * 
 * This is a abstract class that provides a basic structure for models
 * that performs updates in MySQL databases.
 * 
 * @package Iset\Silex
 * @subpackage Db
 * @namespace Iset\Silex\Db
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
abstract class TableGatewayAbstract
{
    /**
     * An instance of Doctrine DBAL
     * @var \Doctrine\DBAL\Connection
     */
    protected $tableGateway = null;
    
    /**
     * The Constructor
     * 
     * @param Application $app
     */
    public function __construct(Application &$app)
    {
        $this->tableGateway = $app['db'];
    }
}