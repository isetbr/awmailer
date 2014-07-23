<?php

namespace Iset\Silex;

use Silex\Application;
use Silex\ControllerProviderInterface as SilexControllerProviderInterface;

interface ControllerProviderInterface extends SilexControllerProviderInterface
{
    
    public function getRequest();
    
    public function getTableGateway();
    
    public function register();
    
    public function lock();
    
    public static function factory(Application &$app);
}