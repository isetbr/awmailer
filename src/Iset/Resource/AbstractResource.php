<?php

/**
 * M4A1 - The Awesome Mailer Service
 *
 * The M4A1 is a software developed for provide a mail service
 * which can be used by all services of iSET.
 *
 * The proposal of M4A1 is provide a mail tool that runs a daemon
 * as a observer for new services to be triggered, this services
 * runs natively on Linux servers independent of each others.
 *
 * This is a source code file, part of M4A1 product and this
 * source code is privately and only iSET and your developers
 * can use or distribute it.
 *
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 * @version $Id$
 *
 */

namespace Iset\Resource;

/**
 * Abstract Resource
 * 
 * This is a abstract class that provides a basic structur for an concrete
 * resource in API.
 * 
 * @package Iset
 * @subpackage Resource
 * @namespace Iset\Resource
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
abstract class AbstractResource
{
    /**
     * The resource name
     * @var string 
     */
    protected $_resourceName = null;
    
    /**
     * The Constructor
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        if (is_string($name))
            $this->_resourceName = $name;
    }
    
    /**
     * Get the name of resource that it implements
     * 
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }
}