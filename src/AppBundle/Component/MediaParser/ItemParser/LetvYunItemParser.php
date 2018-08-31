<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class LetvYunItemParser extends AbstractItemParser
{
    private $patterns = array(
        'p1' => '/^http:\/\/yuntv\.letv\.com\/bcloud\.html/s',
    );

    protected function parseForWebUrl($item, $url)
    {
        $response = $this->fetchUrl($url);

        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_LETV();
        }

        $url = str_replace('width=640&height=360', 'width=100%&height=100%', $url);
        $parsedInfo = array(
            'uuid' => '',
            'name' => 'letv'.md5(time().mt_rand(0, 1000)),
            'summary' => '',
            'page' => '',
            'pictures' => array(
                'url' => '',
            ),
            'files' => array(
                array(
                    'url' => $url,
                    'type' => 'swf',
                ),
            ),
        );

        return array_merge($item, $parsedInfo);
    }

    public function detect($url)
    {
        return (bool) preg_match($this->patterns['p1'], $url);
    }

    protected function convertMediaUri($video)
    {
        throw \Exception('not implemented');
    }

    protected function getUrlPrefixes()
    {
        return array('yuntv.letv.com');
    }

    protected function getDefaultParsedInfo()
    {
        return array(
            'source' => 'letv',
            'name' => '乐视视频',
        );
    }
}
