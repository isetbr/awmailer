<?php

require_once __DIR__ . '/AppKernel.php';

use Silex\Application;
use Zend\Config\Reader\Ini as ConfigReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use SilexExtension\MongoDbExtension;
use Iset\Silex\Provider\ZendCacheServiceProvider;
use Monolog\Logger;
use Iset\Api\Controller\MainController as ApiController;

/**
 * App
 * 
 * This is a App class that configures a application environment,
 * initialize service, controllers, routes and all libraries of 
 * system to let application ready for bootstrapping.
 * 
 * @package App
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class App
{
	/**
	 * Configure the application instance
	 * 
	 * @static
	 * @return Application
	 */
	public static function configure()
	{
		# Initializing Kernel
	    $kernel = new AppKernel();
	    
	    # Setting root path
	    $kernel['root_path'] = dirname(__FILE__) . '/../';
		
		# Loading application configuration
		$reader = new ConfigReader();
		$kernel['config'] = $reader->fromFile($kernel['root_path'] . 'app/config/application.ini');
		
		# Configuring application
		$kernel['base_url'] = $kernel['config']['general']['base_url'];
		$kernel['debug']    = ((int)$kernel['config']['general']['debug'] == 1) ? true : false;
		foreach ($kernel['config']['paths'] as $key => $value) {
		    $path = $kernel['root_path'] . $value;
		    $kernel[$key.'_path'] = $path;
		}
		
		# Register providers
		# Log System
		$kernel['monolog.factory'] = $kernel->protect(function ($name, array $options = array()) use ($kernel) {
		    # Initializing logger
		    $logger = new Logger($name);
		    
		    # Parsing handler
		    switch ($options['handler']) {
		        case 'StreamHandler' :
		            # Getting options
		            $stream = (!is_null($stream = $options['options']['stream'])) ? $kernel['log_path'] . $stream : null;
		            $level = (!is_null($level = $options['options']['level'])) ? $level : Logger::DEBUG;
		            
		            # Initializing handler
		            $handler = new Monolog\Handler\StreamHandler($stream,$level,true,0777);
		            break;
		        default :
		            return false;
		            break;
		    }
		    
		    # Setting handler and returning logger
		    $logger->pushHandler($handler);
		    return $logger;
		});
		
		# Loop into configuration to create instances of log channels
		foreach ($kernel['config']['log'] as $channel => $options) {
		    $kernel['monolog.'.$channel] = $kernel->share(function ($kernel) use ($channel,$options) {
		        return $kernel['monolog.factory']($channel,$options);
		    });
		}
		
        # Creating container for log requests in api
        $kernel['monolog.api.service'] = $kernel->protect(function (Request $request, Response $response) use ($kernel){
            # Logging request
            $log_data = array(
                'method'=>$request->getMethod(),
                'path'=>$request->get('_route'),
                'status'=>$response->getStatusCode(),
                'service-key'=>$kernel['session']->get($kernel['config']['api']['auth_session']['service']),
                'token'=>$kernel['session']->get($kernel['config']['api']['auth_session']['token']),
                'client-ip'=>$kernel['session']->get($kernel['config']['api']['auth_session']['ipaddress']),
            );
            
            $kernel['monolog.api']->addInfo('API Call',$log_data);
        });
		
		# Doctrine DBAL
		$kernel->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options'=>$kernel['config']['database']['mysql']
		));
		
		# Symfony Session
		$kernel->register(new Silex\Provider\SessionServiceProvider());
		
		# Doctrine Mongodb
		$kernel->register(new SilexExtension\MongoDbExtension(), array(
			'mongodb.connection'=>array(
			    'server'=>$kernel['config']['database']['mongo']['dsn'],
			    'options'=>array(),
			    'eventmanager'=>function ($eventmanager) {}
		    ),
		));
		
		# Zend Cache
		$kernel->register(new Iset\Silex\Provider\ZendCacheServiceProvider(), array(
		    'cache.options'=>array(
                'zendcache'=>$kernel['config']['cache']['zendcache'],
		        'cache_dir'=>$kernel['cache_path'],
		    ),
		));
		
		# Register controllers
		$kernel->mount('/api', new ApiController())
		       ->before(function (Request $request) use ($kernel) {
		           # Validating request content-type
			       if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
			           $data = json_decode($request->getContent(), true);
			           $request->request->replace(is_array($data) ? $data : array());
			       } else {
			           $response = Response::create(null,Response::HTTP_BAD_REQUEST);
			           $kernel['monolog.api.service']($request,$response);
			           $response->send();
			           return $kernel->terminate($request,$response);
			       }
			       
			       # Getting authentication headers
			       $auth_service_key = $request->headers->get($kernel['config']['api']['auth_header']['service_key']);
			       $auth_token       = $request->headers->get($kernel['config']['api']['auth_header']['token']);
			       $auth_ip_address  = $_SERVER['REMOTE_ADDR'];
			       
			       # Creating session with auth headers
			       $kernel['session']->set($kernel['config']['api']['auth_session']['service'],$auth_service_key);
			       $kernel['session']->set($kernel['config']['api']['auth_session']['token'],$auth_token);
			       $kernel['session']->set($kernel['config']['api']['auth_session']['ipaddress'],$auth_ip_address);
		       });
		
		# Finish application flow
		$kernel->finish(function (Request $request, Response $response) use ($kernel) {
		    # Logging request
		    $kernel['monolog.api.service']($request,$response);
		    
		    # Cleaning session
			$kernel['session']->remove($kernel['config']['api']['auth_session']['service']);
			$kernel['session']->remove($kernel['config']['api']['auth_session']['token']);
			$kernel['session']->remove($kernel['config']['api']['auth_session']['ipaddress']);
		});
		
		return $kernel;
	}
}