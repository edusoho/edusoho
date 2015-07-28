<?php
namespace Topxia\Service\CloudPlatform\Client;

use Psr\Log\LoggerInterface;

class FailoverCloudAPI extends AbstractCloudAPI
{
    const FAILOVER_COUNT = 5;

    protected $servers = array();

    protected $serverConfigPath = null;

    protected $apiType = null;

    protected function _request($method, $uri, $params, $headers)
    {
        try {
            return parent::_request($method, $uri, $params, $headers);
        } catch (CloudAPIIOException $e) {
            if ($this->apiType !== 'leaf') {
                throw $e;
            }

            $this->refreshServerConfigFile(function($fp, $data) {
                if ($data['failed_expired'] < time()) {
                    $data['failed_count'] ++;
                } else {
                    $data['failed_count'] = 1;
                }
                $data['failed_expired'] = time() + 10;

                if ($data['failed_count'] == self::FAILOVER_COUNT) {
                   $this->voteLeafServer($data);
                }

                return $data;
            });
        }
    }

    public function refreshServerConfigFile($callback)
    {
        $fp = fopen($this->serverConfigPath, 'w');
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            throw new \RuntimeException("Lock server config file failed.");
        }

        $data = json_decode(stream_get_contents($fp), true);
        $data = $callback($fp, $data);

        ftruncate($fp, 0);
        fwrite($fp, json_encode($data));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 选举新的Leaf Server
     */
    public function voteLeafServer($data)
    {
        $leafs = $data['leafs'];
        if (empty($leafs)) {
            throw new \RuntimeException("No leafs server.");
        }

        unset($leafs[$data['current_leaf']]);
        if (count($leafs) === 0) {
            throw new \RuntimeException("Not enough leaf servers to vote.");
        }
        
        $newLeafUrl = null;
        $newLeafUsedCount = 0;
        shuffle($leafs);
        foreach ($leafs as $url => $usedCount) {
            if (empty($newLeafUrl) || ($errorCount < $newLeafUsedCount)) {
                $newLeafUrl = $url;
                $newLeafUsedCount = $errorCount;
            }
        }

        if (empty($newLeafUrl)) {
            throw new \RuntimeException("New leaf server url is empty.");
        }

        $data['leafs'][$data['current_leaf']] ++;
        $data['current_leaf'] = $newLeafUrl;
        $data['failed_count'] = 0;

        return $data;
    }

    public function setApiType($type)
    {
        $types = array('root', 'leaf');
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("Api type `{$type}` is not allowed.");
        }

        $this->apiType = $type;

        if ($type == 'leaf') {
            $this->apiUrl = $this->servers['current_leaf'];
        } else {
            $this->apiUrl = $this->servers['root'];
        }

    }

    public function setApiServerConfigPath($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Cloud api server config file is not exist.");
        }

        $this->serverConfigPath = $path;
        $this->servers = include $path;
    }

}
