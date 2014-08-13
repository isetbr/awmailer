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