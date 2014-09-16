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

# Importing composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Application Kernel
 * 
 * This is a extended version of Silex Application class that provides
 * some customizations for core of application.
 * 
 * @package App
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class AppKernel extends Application
{
    /**
     * Override default json method from Silex Application class with prepared data to send
     * in response.
     * 
     * @param array   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     * 
     * @return JsonResponse
     */
    public function json($data = array(), $status = 200, array $headers = array())
    {
        // Parsing data with utf8_encode
        $data = $this->prepareJsonData($data);
        return parent::json($data,$status,$headers);
    }

    /**
     * Recursive function to json data into utf8 encoding
     * 
     * @param array $data   The data to be parsed
     * @param bool  $encode Defaults true to encode or set false to decode data
     * 
     * @return array
     */
    public function prepareJsonData($data = array(), $encode = true)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->prepareJsonData($value,$encode);
            } else {
                $data[$key] = ($encode) ? utf8_encode($value) : utf8_decode($value);
            }
        }

        return $data;
    }

    /**
     * Override the default run method to a prepared method to handle trailing slashes
     * at the end of URI.
     * 
     * @param Request $request The request object
     */
    public function run(Request $request = null)
    {
        # Verifying if request is null
        if (is_null($request)) {
            # Override request uri by the redirect url
            if (isset($_SERVER['REDIRECT_URL'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_URL'];
            }

            # Creating a request object from server globals
            $request = Request::createFromGlobals();
        }

        # Handling request
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request,$response);
    }
}