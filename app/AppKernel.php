<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;

class AppKernel extends Application
{
	 use Application\TwigTrait;
	 use Application\MonologTrait;
	 use Application\UrlGeneratorTrait;
}