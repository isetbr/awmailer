<?php

# Importing composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;

/**
 * Application Kernel
 * 
 * This is a extended version of Silex Application class that provides
 * some customizations for core of application.
 * 
 * @package App
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class AppKernel extends Application{}