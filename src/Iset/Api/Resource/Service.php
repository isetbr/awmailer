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
use Zend\Validator\Uri as UriValidator;

/**
 * Service
 *
 * This is a object representation of an Service
 *
 * @package Iset\Api
 * @subpackage Resource
 * @namespace Iset\Api\Resource
 * @author Lucas Mendes de Freitas <devsdmf>
 * @copyright AwMailer (c) iSET - Internet, Soluções e Tecnologia LTDA.
 *
 */
class Service extends AbstractResource implements ModelInterface
{
    /**
     * The resource name
     * @var string
     */
    const RESOURCE_NAME = 'service';

    /**
     * The ID of service
     * @var integer
     */
    public $id = null;

    /**
     * The name of service
     * @var string
     */
    public $name = null;

    /**
     * The key of service
     * @var string
     */
    public $key = null;

    /**
     * The Token of service
     * @var string
     */
    private $token = null;

    /**
     * The URL that API will send notifications
     * @var string
     */
    public $notification_url = null;

    /**
     * The instance of TableGateway
     * @var \Iset\Db\TableGatewayAbstract
     */
    private $gateway = null;

    /**
     * The Constructor
     *
     * @param  TableGatewayAbstract       $gateway
     * @return \Iset\Api\Resource\Service
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
     * Get the token of service
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Fill object with an configured associative array
     *
     * @param  array                      $data
     * @see \Iset\Model\ModelInterface::exchangeArray()
     * @return \Iset\Api\Resource\Service
     */
    public function exchangeArray(array $data)
    {
        $this->id               = (!empty($data['idservice'])) ? (int) $data['idservice'] : null;
        $this->name             = (!empty($data['name'])) ? $data['name'] : null;
        $this->key              = (!empty($data['key'])) ? $data['key'] : null;
        $this->token            = (!empty($data['token'])) ? $data['token'] : null;
        $this->notification_url = (!empty($data['notification_url'])) ? $data['notification_url'] : null;

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
        $data = array(
            'id'=>$this->id,
            'name'=>$this->name,
            'key'=>$this->key,
            'token'=>$this->token,
            'notification_url'=>$this->notification_url,
        );

        return $data;
    }

    /**
     * Validate the Service
     *
     * @see \Iset\Model\ModelInterface::validate()
     * @return mixed
     */
    public function validate()
    {
        # Validating service name
        if (is_null($this->name)) {
            return array('error'=>'A service name must be specified');
        } elseif (!is_string($this->name)) {
            return array('error'=>'A service name must be an string');
        }

        # Validating service key
        if (is_null($this->key)) {
            return array('error'=>'A service key must be specified');
        } elseif (!is_string($this->key)) {
            return array('error'=>'A service key must be an string');
        } else {
            $this->key = strtolower($this->key);
        }

        # Validating token
        if (is_null($this->token)) {
            if (is_null($this->id)) {
                $this->generateServiceToken();
            } else {
                return array('error'=>'Service token cannot be regenerated.');
            }
        }

        # Validating notification URL
        if (!is_null($this->notification_url)) {
            $validator = new UriValidator(array('allowRelative'=>false));
            if (!$validator->isValid($this->notification_url)) {
                return array('error'=>'The notification url is not a valid URI');
            }
        }

        return true;
    }

    /**
     * Save Service
     *
     * @see \Iset\Model\ModelInterface::save()
     * @return mixed
     */
    public function save()
    {
        $response = $this->gateway->saveService($this);
        if (!is_null($this->id) && !is_array($response)) {
            return true;
        } else {
            return $response;
        }
    }

    /**
     * Delete Service
     *
     * @see \Iset\Model\ModelInterface::delete()
     * @return mixed
     */
    public function delete()
    {
        $response = $this->gateway->deleteService($this);
        if ($response) {
            unset($this);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate a token for a new service
     */
    private function generateServiceToken()
    {
        $this->token = hash('ripemd320',bin2hex($this->key.'?'.rand(111111,999999).'#'.time()));
    }
}
