<?php

namespace Iset\Silex\Mongo;

use Silex\Application;

/**
 * Abstract Collection
 * 
 * This is a abstract class with basic structure of an collection model
 * 
 * @package Iset\Silex
 * @subpackage Mongo
 * @namespace Iset\Silex\Mongo
 * @author Lucas Mendes de Freita <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA
 *
 */
abstract class CollectionAbstract
{
    /**
     * The instance of Doctrine Collection
     * @var \Doctrine\MongoDB\Collection
     */
    protected $gateway = null;
    
    /**
     * The Constructor
     * 
     * @param Application $app
     * @param string      $collection
     */
    public function __construct(Application &$app, $collection)
    {
        @$this->gateway = &$app['mongodb']->selectDatabase($app['config']['database']['mongo']['dbname'])
                                          ->selectCollection($collection);
    }
}