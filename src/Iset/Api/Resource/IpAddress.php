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

namespace Iset\Api\Resource;

use Iset\Resource\AbstractResource;
use Iset\Model\ModelInterface;
use Iset\Db\TableGatewayAbstract;
use Zend\Validator\Ip as IpAddressValidator;

/**
 * IpAddress
 *
 * This is a object representation of an IpAddress
 *
 * @package Iset\Api
 * @subpackage Resource
 * @namespace Iset\Api\Resource
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class IpAddress extends AbstractResource implements ModelInterface
{
    /**
     * The resource name
     * @var string
     */
    const RESOURCE_NAME = 'ipaddress';

    /**
     * The IP address
     * @var string
     */
    public $ipaddress = null;

    /**
     * The instance of TableGateway
     * @var \Iset\Db\TableGatewayAbstract
     */
    private $gateway = null;

    /**
     * The Constructor
     *
     * @param  TableGatewayAbstract         $gateway
     * @return \Iset\Api\Resource\IpAddress
     */
    public function __construct(TableGatewayAbstract $gateway = null)
    {
        parent::__construct($this::RESOURCE_NAME);

        if (!is_null($gateway)) {
            $this->gateway = $gateway;
        }

        return $this;
    }

    /**
     * Fill object with an configured associative array
     *
     * @param  array                        $data
     * @see \Iset\Model\ModelInterface::exchangeArray()
     * @return \Iset\Api\Resource\IpAddress
     */
    public function exchangeArray(array $data)
    {
        $this->ipaddress = (!empty($data['ipaddress']) && !is_null($data['ipaddress'])) ? $data['ipaddress'] : null;

        return $this;
    }

    /**
     * Get the array representation of object
     *
     * @see \Iset\Model\ModelInterface::asArray()
     * @return array
     */
    public function asArray()
    {
        return array('ipaddress'=>$this->ipaddress);
    }

    /**
     * Validate the IpAddress
     *
     * @see \Iset\Model\ModelInterface::validate()
     * @return mixed
     */
    public function validate()
    {
        # Validating ip address
        $validator = new IpAddressValidator();
        if (!$validator->isValid($this->ipaddress)) {
            return array('error'=>'Invalid ip address');
        }

        return true;
    }

    /**
     * Save IpAddress
     *
     * @see \Iset\Model\ModelInterface::save()
     * @return mixed
     */
    public function save()
    {
        $response = $this->gateway->saveIpAddress($this);
        if (!is_null($response) && !is_array($response)) {
            return true;
        } else {
            return $response;
        }
    }

    /**
     * Delete IpAddress
     *
     * @see \Iset\Model\ModelInterface::delete()
     * @return mixed
     */
    public function delete()
    {
        $response = $this->gateway->deleteIpAddress($this);
        if ($response) {
            unset($this);

            return true;
        } else {
            return false;
        }
    }
}
