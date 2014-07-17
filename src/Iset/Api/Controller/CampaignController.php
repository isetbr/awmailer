<?php

namespace Iset\Api\Controller;

use Silex\Application;
use Iset\Silex\ControllerProviderInterface;

class CampaignController implements ControllerProviderInterface
{
    
    protected $_app = null;
    
    public function __construct(){}
    
    public function connect(Application $app)
    {
        $this->_app = $app;
        return $this->register();
    }
    
    public function register()
    {
        $container = $this->_app['controllers_factory'];
        
        # Retrieve all campaigns
        $container->get('/', function () {
        	return 'Retrieve all campaigns';
        });
        
        # Create a campaign
        $container->post('/', function () {
        	return 'Create a campaign';
        });
        
        # Get details from an campaign
        $container->get('/{idcampaign}', function ($idcampaign) {
        	return 'Get details from an campaign';
        });
        
        # Update a campaign
        $container->put('/{idcampaign}', function ($idcampaign) {
        	return 'Update a campaign';
        });
        
        # Remove a campaign
        $container->delete('/{idcampaign}', function ($idcampaign) {
        	return 'Remove a campaign';
        });
        
        # Get current queue list from a campaign
        $container->get('/{idcampaing}/queue', function ($idcampaing) {
        	return 'Queue list of an campaign';
        });
        
        # Add destinations to an queue list from campaign
        $container->put('/{idcampaign}/queue', function ($idcampaign) {
        	return 'Add destinations';
        });
        
        # Remove destinations from an queue list
        $container->delete('/{idcampaign}/queue', function ($idcampaign) {
        	return 'Remove destinations from queue list';
        });
        
        # Start process
        $container->post('/{idcampaign}/start', function ($idcampaign) {
        	return 'Start process';
        });
        
        # Pause process
        $container->post('/{idcampaign}/pause', function ($idcampaign) {
        	return 'Pause process';
        });
        
        # Stop process
        $container->post('/{idcampaign}/stop', function ($idcampaign) {
        	return 'Stop process';
        });
        
        return $container;
    }
    
    public static function factory(Application &$app)
    {
        $instance = new self();
        return $instance->connect($app);
    }
}