<?php

namespace Iset\Silex\Auth;

use Silex\Application;

interface AuthInterface
{
    
    public static function authenticate(Application &$app);
}