<?php
namespace Topxia\Service\CloudPlatform\Client;

use Psr\Log\LoggerInterface;

class FailoverCloudAPI extends AbstractCloudAPI
{
    const FAILOVER_COUNT = 3;

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

            $this->refreshServerConfigFile(function($fp, $data, $maxFailoverCount) {
                if (($data['failed_expired'] > 0) && ($data['failed_expired'] > time())) {
                    $data['failed_count'] ++;
                } else {
                    $data['failed_count'] = 1;
                    $data['failed_expired'] = time() + 10;
                }

                if ($data['failed_count'] == $maxFailoverCount) {
                   $data = $this->voteLeafServer($data);
                }

                return $data;
            });
        }
    }

    public function refreshServerConfigFile($callback)
    {
        $fp = fopen($this->serverConfigPath, 'r+');
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            throw new \RuntimeException("Lock server config file failed.");
        }

        $data = json_decode(fread($fp, filesize($this->serverConfigPath)), true);
        $data = $callback($fp, $data, self::FAILOVER_COUNT);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 选举新的Leaf Server
     */
    public function voteLeafServer($servers)
    {
        $leafs = $servers['leafs'];
        if (empty($leafs)) {
            throw new \RuntimeException("No leafs server.");
        }

        $newLeaf = array();

        uksort($leafs, function($key1, $key2) {
            $results = array(true, false);
            return $results[rand(0, 1)];
        });
        foreach ($leafs as $i => $leaf) {
            if ($leaf['url'] == $servers['current_leaf']) {
                $servers['leafs'][$i]['used_count'] ++;
                continue;
            }
            if (empty($newLeaf) || ($leaf['used_count'] < $newLeaf['used_count'])) {
                $newLeaf = $leaf;
            }
        }

        if (empty($newLeaf)) {
            throw new \RuntimeException("New leaf server is empty.");
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
        
        if (!file_exists($path)) {

            $servers = parent::_request('GET', '/server_list', array(), array());
            if (empty($servers) or empty($servers['root']) or empty($servers['current_leaf']) or empty($servers['leafs'])) {
                $servers = $this->getServerListFromCdn();
                if (empty($servers) || empty($servers['root']) || empty($servers['leafs'])) {
                    throw new \RuntimeException("Requested API Server list from CDN failed.");
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
            $servers['next_refresh_time'] = 0;

            file_put_contents($path, json_encode($servers));
        }

        $this->serverConfigPath = $path;
        $this->servers = json_decode(file_get_contents($path), true);
    }

    protected function getServerListFromCdn()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, 'http://api-common.b0.upaiyun.com/serverList.json');

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

}
