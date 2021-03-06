<?php

/**
 * AwMailer - The Awesome Mailer Service
 *
 * The AwMailer is a software developed for provide a mail service
 * which can be used by all services of iSET.
 *
 * The proposal of AwMailer is provide a mail tool that runs a daemon
 * as a observer for new services to be triggered, this services
 * runs natively on Linux servers independent of each others.
 *
 * This is a source code file, part of AwMailer product and this
 * source code is privately and only iSET and your developers
 * can use or distribute it.
 *
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 * @version $Id$
 *
 */

namespace Iset\Mongo;

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
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA
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
        @$this->gateway = $app['mongodb']->selectDatabase($app['config']['database']['mongo']['dbname'])
                                         ->selectCollection($collection);
    }
}
