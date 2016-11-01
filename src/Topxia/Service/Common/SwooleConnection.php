<?php

namespace Topxia\Service\Common;

class SwooleConnection
{
    private $client;
    private $isConnected = false;
    private $serverHost;
    private $serverPort;
    private $packageEOF = '\r\n\r\n';
    protected $clientConfig = array();
    public function setServerHost($host)
    {
        $this->serverHost = $host;
    }
    public function setServerPort($port)
    {
        $this->serverPort = $port;
    }
    public function setClientConfig($config)
    {
        $this->clientConfig = $config;
    }
    public function isConnected()
    {
        return $this->isConnected;
    }
    public function __call($name, $arguments)
    {
        if (!$this->isConnected) {
            $this->connect();
        }
        $data = array(
            'method' => $name,
            'args' => $arguments,
        );
        $this->client->send(json_encode($data).$this->packageEOF);
        $data = $this->client->recv();
        $data = json_decode($data, true);
        return $data['data'];
    }
    protected function close()
    {
        $this->client->close();
    }
    protected function connect()
    {
        $this->client = new \swoole_client(SWOOLE_TCP | SWOOLE_KEEP);
        if (!$this->client->connect($this->serverHost, $this->serverPort, -1)) {
            throw new Exception("Connect to pool server failed. Error: {$this->client->errCode}\n");
        }
        $defaultConfig = array(
            'open_eof_check' => true,
            'package_eof' => '\r\n\r\n'
        );
        $this->client->set(array_merge($defaultConfig, $this->clientConfig));
        $this->isConnected = true;
    }
}