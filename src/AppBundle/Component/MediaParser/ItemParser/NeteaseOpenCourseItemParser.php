<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ParserException;

class NeteaseOpenCourseItemParser extends AbstractItemParser
{
    protected function parseForWebUrl($item, $url)
    {
        $response = $this->fetchUrl($url);

        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_NETEASE();
        }

        $matched = preg_match('/getCurrentMovie.*?id\s*:\s*\'(.*?)\'.*?image\s*:\s*\'(.*?)\'.*?title\s*:\s*\'(.*?)\'.*?host\s*\+\s*\'(.*?)\',/s', $response['content'], $matches);
        $parseUrl = parse_url($url);

        if (!$matched || empty($parseUrl['host'])) {
            throw ParserException::PARSED_FAILED_NETEASE();
        }

        $item['id'] = $matches[1];
        $item['uuid'] = 'NeteaseOpenCourse:'.$item['id'];
        $item['name'] = $matches[3];
        $item['page'] = $url;
        $item['pictures'] = array(
            array('url' => $matches[2]),
        );

        $item['files'] = array(
            array(
                'url' => '//'.$parseUrl['host'].$matches[4],
                'type' => 'swf',
            ),
        );

        return $item;
    }

    protected function getUrlPrefixes()
    {
        return array('v.163.com/movie/', 'open.163.com/movie/');
    }

    protected function convertMediaUri($video)
    {
        $matched = preg_match('/^(http|https):(\S*)/s', $video['mediaUri'], $matches);
        if ($matched) {
            $video['mediaUri'] = $matches[2];
        }

        return $video;
    }

    protected function getDefaultParsedInfo()
    {
        return array(
            'source' => 'NeteaseOpenCourse',
            'name' => '网易公开课视频',
        );
    }
}
