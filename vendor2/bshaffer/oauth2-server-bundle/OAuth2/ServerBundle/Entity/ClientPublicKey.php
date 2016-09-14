<?php

namespace OAuth2\ServerBundle\Entity;

/**
 * Client
 */
class ClientPublicKey
{
    /**
     * @var \OAuth2\ServerBundle\Entity\Client
     */
    private $client;

    /**
     * @var integer
     */
    private $client_id;

    /**
     * @var string
     */
    private $public_key;

    /**
     * Set client
     *
     * @param  \OAuth2\ServerBundle\Entity\Client $client
     * @return ClientPublicKey
     */
    public function setClient(\OAuth2\ServerBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        // this is necessary as the client_id is the primary key
        $this->client_id = $client->getClientId();

        return $this;
    }

    /**
     * Get client
     *
     * @return \OAuth2\ServerBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set public key
     *
     * @param  string  $public_key
     * @return Client
     */
    public function setPublicKey($public_key)
    {
        $this->public_key = $public_key;

        return $this;
    }

    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }
}
