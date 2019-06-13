<?php

namespace Biz\CloudPlatform\Client;

class FailoverCloudAPI extends AbstractCloudAPI
{
    const FAILOVER_COUNT = 3;

    protected $servers = array();

    protected $serverConfigPath = null;

    protected $apiType = null;

    protected $rootApiUrl = null;

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->rootApiUrl = $this->apiUrl;
    }

    protected function _request($method, $uri, $params, $headers)
    {
        try {
            $result = parent::_request($method, $uri, $params, $headers);

            if ($this->servers['next_refresh_time'] < time()) {
                $self = $this;
                $this->servers = $this->refreshServerConfigFile(function () use ($self) {
                    return $self->getServerList(0);
                }, 'noneblocking');
            }

            return $result;
        } catch (CloudAPIIOException $e) {
            if ($this->apiType !== 'leaf') {
                throw $e;
            }

            $that = $this;
            $this->refreshServerConfigFile(function ($fp, $data, $maxFailoverCount) use ($that) {
                if (($data['failed_expired'] > 0) && ($data['failed_expired'] > time())) {
                    ++$data['failed_count'];
                } else {
                    $data['failed_count'] = 1;
                    $data['failed_expired'] = time() + 120;
                }

                if ($data['failed_count'] == $maxFailoverCount) {
                    $data = $that->voteLeafServer($data);
                }

                return $data;
            }, 'nonblocking');

            throw $e;
        }
    }

    public function refreshServerConfigFile($callback, $lockMode = 'blocking')
    {
        $fp = fopen($this->serverConfigPath, 'r+');

        if ($lockMode == 'blocking') {
            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                throw new \RuntimeException('Lock server config file failed.');
            }
        } elseif ($lockMode == 'nonblocking') {
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                fclose($fp);

                return;
            }
        }

        if (filesize($this->serverConfigPath) > 0) {
            $data = json_decode(fread($fp, filesize($this->serverConfigPath)), true);
        } else {
            $data = array();
        }

        $data = $callback($fp, $data, self::FAILOVER_COUNT);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));

        if ($lockMode != 'none') {
            flock($fp, LOCK_UN);
        }

        fclose($fp);

        return $data;
    }

    /**
     * 选举新的Leaf Server.
     */
    public function voteLeafServer($servers)
    {
        $leafs = $servers['leafs'];

        if (empty($leafs)) {
            throw new \RuntimeException('No leafs server.');
        }

        $newLeaf = array();

        uksort($leafs, function ($key1, $key2) {
            $results = array(true, false);

            return $results[rand(0, 1)];
        }
        );

        foreach ($leafs as $i => $leaf) {
            if ($leaf['url'] == $servers['current_leaf']) {
                ++$servers['leafs'][$i]['used_count'];
                continue;
            }

            if (empty($newLeaf) || ($leaf['used_count'] < $newLeaf['used_count'])) {
                $newLeaf = $leaf;
            }
        }

        if (empty($newLeaf)) {
            throw new \RuntimeException('New leaf server is empty.');
        }

        if ($newLeaf['used_count'] > 3) {
            // 确保1小时后更新地址列表
            $nextRefreshTime = time() + 3600;
            $servers = $this->getServerList($nextRefreshTime);

            return $servers;
        }

        $servers['current_leaf'] = $newLeaf['url'];
        $servers['failed_count'] = 0;
        $servers['failed_expired'] = 0;

        return $servers;
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
        }
    }

    public function setApiServerConfigPath($path)
    {
        $this->serverConfigPath = $path;

        if (!file_exists($path)) {
            $self = $this;
            touch($path);
            $this->servers = $this->refreshServerConfigFile(function () use ($self) {
                return $self->getServerList();
            }, 'blocking');
        } else {
            $data = file_get_contents($path);

            if (trim($data) == '') {
                $self = $this;
                $this->servers = $this->refreshServerConfigFile(function () use ($self) {
                    return $self->getServerList();
                }, 'blocking');
            } else {
                $this->servers = json_decode($data, true);
            }
        }
    }

    public function getServerList($nextRefreshTime = 0)
    {
        $prevApiUrl = $this->apiUrl;
        $this->setApiUrl($this->rootApiUrl);

        $servers = parent::_request('GET', '/server_list', array(), array());
        $this->setApiUrl($prevApiUrl);

        if (empty($servers) || empty($servers['root']) || empty($servers['current_leaf']) || empty($servers['leafs'])) {
            $servers = $this->getServerListFromCdn();

            if (empty($servers) || empty($servers['root']) || empty($servers['leafs'])) {
                throw new \RuntimeException('Requested API Server list from CDN failed.');
            }
        }

        if (empty($servers['current_leaf'])) {
            $servers['current_leaf'] = $servers['leafs'][array_rand($servers['leafs'])]['url'];
        }

        foreach ($servers['leafs'] as &$leaf) {
            $leaf['used_count'] = 0;
            unset($leaf);
        }

        $servers['failed_count'] = 0;
        $servers['failed_expired'] = 0;

        if (empty($nextRefreshTime)) {
            //确保每天的凌晨0~5点之间的时间内更新
            $hour = rand(0, 5);
            $minute = rand(0, 59);
            $second = rand(0, 59);
            $nextRefreshTime = strtotime(date('Y-m-d 0:0:0', strtotime('+1 day'))) + $hour * 3600 + $minute * 60 + $second;
        }

        $servers['next_refresh_time'] = $nextRefreshTime;

        return $servers;
    }

    protected function getServerListFromCdn()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, 'http://serverlist.edusoho.net/serverList.json');

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}
