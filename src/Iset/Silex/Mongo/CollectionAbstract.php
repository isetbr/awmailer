<?php

namespace Iset\Silex\Mongo;

use Silex\Application;

abstract class CollectionAbstract
{
    
    protected $gateway = null;
    
    public function __construct(Application &$app, $collection)
    {
        $this->gateway = &$app['mongodb']->selectDatabase($app['config']['database']['mongo']['dbname'])
                                         ->selectCollection($collection);
    }
}