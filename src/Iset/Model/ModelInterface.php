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

namespace Iset\Model;

use Iset\Db\TableGatewayAbstract;

/**
 * Model Interface
 * 
 * This is a interface that provides a list of methods that must be
 * implemented by model objects.
 * 
 * @package Iset
 * @subpackage Model
 * @namespace Iset\Model
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright M4A1 (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
interface ModelInterface
{
    /**
     * The Constructor
     * 
     * @param TableGatewayAbstract $gateway
     */
    public function __construct(TableGatewayAbstract $gateway = null);
    
    /**
     * Fill object properties with an configured associative array
     * 
     * @param array $data
     */
    public function exchangeArray(array $data);
    
    /**
     * Get object in a array representation
     * 
     * @return array
     */
    public function asArray();
    
    /**
     * Validate object
     * 
     * @return mixed
     */
    public function validate();
    
    /**
     * Save object in database
     * 
     * @return mixed
     */
    public function save();
    
    /**
     * Remove object
     * 
     * @return mixed
     */
    public function delete();
}