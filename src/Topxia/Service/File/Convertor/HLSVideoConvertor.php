<?php

namespace Topxia\Service\File\Convertor;

class HLSVideoConvertor
{
    const NAME = 'HLSVideo';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {
        $this->client = $client;
        $this->config = $config[self::NAME];
    }

    public function getCovertParams($params)
    {
        $videoQuality     = empty($params['videoQuality']) ? 'low' : $params['videoQuality'];
        $videoDefinitions = $this->config['video'][$videoQuality];

        $audioQuality     = empty($params['audioQuality']) ? 'low' : $params['audioQuality'];
        $audioDefinitions = $this->config['audio'][$audioQuality];

        return array(
            'convertor'    => self::NAME,
            'segtime'      => $this->config['segtime'],
            'videoQuality' => $videoQuality,
            'audioQuality' => $audioQuality,
            'video'        => $videoDefinitions,
            'audio'        => $audioDefinitions
        );
    }

    public function saveConvertResult($file, $result)
    {
        $items = (empty($result['items']) || !is_array($result['items'])) ? array() : $result['items'];

        $types = array('sd', 'hd', 'shd');
        $metas = array();

        foreach (array_values($items) as $index => $item) {
            $type         = $types[$index];
            $metas[$type] = array(
                'type' => $type,
                'cmd'  => $item['cmd'],
                'key'  => $item['key']
            );
        }

        $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];
        unset($file['metas2']['sd']);
        unset($file['metas2']['hd']);
        unset($file['metas2']['shd']);
        $file['metas2']        = array_merge($file['metas2'], $metas);
        $file['convertStatus'] = 'success';

        return $file;
    }
}
