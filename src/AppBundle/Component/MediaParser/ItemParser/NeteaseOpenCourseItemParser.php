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

        $matched = preg_match('/getCurrentMovie.*?id\s*:\s*\'(.*?)\'.*?image\s*:\s*\'(.*?)\'\s*\+\s*\'(.*?)\'\s*\+\s*\'(.*?)\'.*?title\s*:\s*\'(.*?)\'.*?appsrc\s*:\s*\'(.*?)\'.*?src\s*:\s*\'(.*?)\'/s', $response['content'], $matches);

        if (!$matched) {
            throw ParserException::PARSED_FAILED_NETEASE();
        }

        $item['id'] = $matches[1];
        $item['uuid'] = 'NeteaseOpenCourse:'.$item['id'];
        $item['name'] = iconv('gbk', 'utf-8', $matches[5]);
        $item['page'] = $url;
        $item['pictures'] = array(
            array('url' => $matches[2].$matches[3].$matches[4]),
        );

        $item['files'] = array(
            array('type' => 'swf', 'url' => $matches[7]),
            array('type' => 'mp4', 'url' => str_replace('.m3u8', '.mp4', $matches[6])),
            array('type' => 'm3u8', 'url' => $matches[6]),
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
