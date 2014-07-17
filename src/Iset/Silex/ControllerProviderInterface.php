<?php

namespace Iset\Silex;

use Silex\Application;
use Silex\ControllerProviderInterface as SilexControllerProviderInterface;

interface ControllerProviderInterface extends SilexControllerProviderInterface
{
    
    private function register();
    
    public static function factory(Application &$app);
}