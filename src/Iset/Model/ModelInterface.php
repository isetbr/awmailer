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
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
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
