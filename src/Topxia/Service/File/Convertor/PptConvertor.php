<?php

namespace Topxia\Service\File\Convertor;

class PptConvertor
{
    const NAME = 'ppt';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {
        $this->client = $client;
        $this->config = $config[self::NAME];
    }

    public function getCovertParams($params)
    {
        $params = array('convertor' => self::NAME);

        return array_merge($params, $this->config);
    }

    public function saveConvertResult($file, $result)
    {
        if (!empty($result['nextConvertCallbackUrl'])) {
            $items = (empty($result['items']) || !is_array($result['items'])) ? array() : $result['items'];

            $types = array('pdf');
            $metas = array();

            foreach (array_values($items) as $index => $item) {
                $type         = $types[$index];
                $metas[$type] = array(
                    'type' => $type,
                    'cmd'  => $item['cmd'],
                    'key'  => $item['key']
                );
            }

            if (isset($result['type']) && isset($result['type']) == "ppt") {
                $metas['length'] = empty($result['length']) ? 0 : $result['length'];

                $metas['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];

                $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];

                $file['metas2'] = array_merge($file['metas2'], $metas);

                $file['convertStatus'] = 'success';

                return $file;
            }

            $result = $this->client->convertPPT($metas['pdf']['key'], $result['nextConvertCallbackUrl']);

            $metas['length']      = empty($result['length']) ? 0 : $result['length'];
            $metas['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];

            $file['metas2']        = empty($file['metas2']) ? array() : $file['metas2'];
            $file['metas2']        = array_merge($file['metas2'], $metas);
            $file['convertStatus'] = 'doing';
        } else {
            $file['convertStatus'] = 'success';
        }

        return $file;
    }
}