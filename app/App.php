<?php

require_once __DIR__ . '/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Config\Reader\Ini as ConfigReader;
use Iset\Api\Controller\MainController as ApiController;

class App
{
	
	public static function configure()
	{
		$kernel = new AppKernel();
		
		# Configure application
		## Setting paths
		$kernel['root_path']        = dirname(__FILE__) . '/../';
		$kernel['application_path'] = $kernel['root_path'] . 'app/';
		$kernel['config_path']      = $kernel['application_path'] . 'config/';
		$kernel['cache_path']       = $kernel['application_path'] . 'cache/';
		$kernel['log_path']         = $kernel['application_path'] . 'logs/';
		$kernel['source_path']      = $kernel['root_path'] . 'src/';
		$kernel['public_path']      = $kernel['root_path'] . 'web/';
		
		## Setting environment configs
		$kernel['debug']            = true;
		$kernel['base_url']         = 'http://m4a1.localhost/';
		
		## Registering helpers
		$kernel['config_reader']    = new ConfigReader();
		
		# Register providers
		$kernel->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options'=>$kernel['config_reader']->fromFile($kernel['config_path'] . 'database.ini')['mysql']
		));
		
		# Register controllers
		$kernel->mount('/api', new ApiController())->before(function (Request $request) use ($kernel) {
			if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
			    $data = json_decode($request->getContent(), true);
			    $request->request->replace(is_array($data) ? $data : array());
			} else {
			    return $kernel->abort(Response::HTTP_BAD_REQUEST);
			}
		});
		
		return $kernel;
	}
}