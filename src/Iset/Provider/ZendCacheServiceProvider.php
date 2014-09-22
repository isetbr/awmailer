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

namespace Iset\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Zend\Cache\StorageFactory;

/**
 * Zend Cache Provider
 *
 * This is a extension of ControllerProviderInterface provided by Silex framework
 * customized for this application
 *
 * @package Iset
 * @subpackage Provider
 * @namespace Iset\Provider
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class ZendCacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Cache service in application dependency injection container
     *
     * @param Application $app
     * @see \Silex\ServiceProviderInterface::register()
     */
    public function register(Application $app)
    {
        # Defining default cache options
        $app['cache.default_options'] = array(
            'zendcache'=> array(
                'adapter'=>'filesystem',
                'plugins'=>array(
                    'exception_handler'=>array(
                        'throw_exceptions'=>false
                    ),
                ),
            ),
            'cache_dir'=>'/tmp/',
        );

        # Initializing Service
        $app['cache'] = $app->share(function ($app) {
            # Verifying if user options is defined or use default options
            $app['cache.options'] = (isset($app['cache.options'])) ? $app['cache.options'] : $app['cache.default_options'];

            # Initializing Cache Provider
            $cache = StorageFactory::factory($app['cache.options']['zendcache']);

            # Set cache directory and permissions
            $cache->setOptions(array(
                'cache_dir'=>$app['cache.options']['cache_dir'],
                'dir_permission'=>0777,
                'file_permission'=>0666,
            ));

            return $cache;
        });
    }

    /**
     * Bootstrap the application
     *
     * @param Application $app
     * @see \Silex\ServiceProviderInterface::boot()
     */
    public function boot(Application $app) {}
}
