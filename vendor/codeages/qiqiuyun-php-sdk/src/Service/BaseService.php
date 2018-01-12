<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Auth;
use QiQiuYun\SDK\HttpClient\Client;

abstract class BaseService
{
    /**
     * QiQiuYun auth
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Service options
     *
     * @var string[]
     */
    protected $options;

    /**
     * Http client
     *
     * @var Client
     */
    protected $client;

    /**
     * API base uri
     *
     * @var string
     */
    protected $baseUri;

    public function __construct(Auth $auth, $options = array())
    {
        $this->auth = $auth;
        $this->options = $options;
        $this->client = $this->createClient();
    }

    protected function createClient()
    {
        if (!empty($this->options['base_uri'])) {
            $this->baseUri = $this->options['base_uri'];
        }

        return new Client(array(
            'base_uri' => $this->baseUri,
        ));
    }
}
