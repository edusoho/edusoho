<?php

namespace Codeages\Beanstalk;

use Psr\Log\LoggerInterface;
use Codeages\Beanstalk\Exception\SocketException;
use Codeages\Beanstalk\Exception\ConnectionException;

class ClientProxy
{
    protected $client;
    protected $logger;
    protected $maxReconnectTimes;
    protected $reconnectSleep;

    /**
     * ClientProxy构造
     * 
     * @param Client               $client            Client对象
     * @param LoggerInterface|null $logger            日志对象
     * @param integer              $maxReconnectTimes 最大重试连接的次数
     * @param integer              $reconnectSleep    重试间隔，单位秒
     */
    public function __construct(Client $client, LoggerInterface $logger = null, $maxReconnectTimes = 8, $reconnectSleep = 2)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->maxReconnectTimes = $maxReconnectTimes;
        $this->reconnectSleep = $reconnectSleep;
    }

    public function __call($method, $arguments)
    {
        $ok = true;
        $exception = null;
        $reconnectTimes = 0;

        do {

            try {
                if ($ok === false) {
                    $reconnectTimes ++;
                    $this->client->reconnect();
                    $ok = true;
                }

                try {
                    return call_user_func_array([$this->client, $method], $arguments);
                } catch (SocketException $e) {
                    $ok = false;
                    $exception = $e;
                    $message = sprintf('Beanstalk reconnect happened(%s), when call %s(%s).', json_encode($this->client->getConfig()), $method, substr(json_encode($arguments), 0, 100));
                    $this->logger->notice($message);
                }

            } catch (ConnectionException $e) {
                $ok = false;
                $exception = $e;
                $messge = sprintf('Beanstalk reconnect error(retry %d times), sleep 2 seconds, try again.', $reconnectTimes);
                $this->logger->notice($messge);
                sleep($this->reconnectSleep);
            }

        } while($ok === false && $reconnectTimes < $this->maxReconnectTimes);

        if ($exception) {
            throw $exception;
        }

    }
}
