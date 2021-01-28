<?php

namespace AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\MediaParser\ParserException;

class NeteaseOpenCourseItemParser extends AbstractItemParser
{
    const API_URL = 'https://c.open.163.com';

    protected function parseForWebUrl($item, $url)
    {
        $parseUrl = parse_url($url);
        $query = empty($parseUrl['query']) ? array() : $this->convertUrlQuery($parseUrl['query']);
        if (!empty($query['pid'])) {
            $response = $this->fetchUrl(self::API_URL."/mob/{$query['pid']}/getMoviesForAndroid.do");
        }

        if (200 != $response['code']) {
            throw ParserException::PARSED_FAILED_NETEASE();
        }

        $content = json_decode($response['content'], true);
        $data = $content['data'];
        $videoList = ArrayToolkit::index($data['videoList'], 'plid');
        $video = $videoList[$query['pid']];

        $item['id'] = $query['pid'];
        $item['uuid'] = 'NeteaseOpenCourse:'.$item['id'];
        $item['name'] = $video['title'];
        $item['page'] = $url;
        $item['pictures'] = array(
            array('url' => empty($video['imgPath']) ? '' : $video['imgPath']),
        );

        $item['files'] = array(
            array(
                'url' => $this->getMp4Url($video),
                'type' => 'mp4',
            ),
        );

        return $item;
    }

    protected function getUrlPrefixes()
    {
        return array('v.163.com/movie/', 'open.163.com/movie/', 'open.163.com/newview/movie');
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

    protected function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    protected function getMp4Url($video)
    {
        $urlList = array(
            'mp4ShdUrl',
            'mp4HdUrl',
            'mp4SdUrl',
            'mp4ShdUrlOrign',
            'mp4HdUrlOrign',
            'mp4SdUrlOrign',
        );

        foreach ($urlList as $urlKey) {
            if (!empty($video[$urlKey])) {
                $matched = preg_match('/^(http|https):(\S*)/s', $video[$urlKey], $matches);
                if ($matched) {
                    return $matches[2];
                }

                return $video[$urlKey];
            }
        }

        return '';
    }
}
