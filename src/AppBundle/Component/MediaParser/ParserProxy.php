<?php

namespace AppBundle\Component\MediaParser;

use AppBundle\Component\MediaParser\ItemParser\AbstractItemParser;
use Topxia\Service\Common\ServiceKernel;

class ParserProxy
{
    private $mockedParser = null;

    public function getParsers()
    {
        return [
            'youku' => 'YoukuVideo',
            'qqvideo' => 'QQVideo',
            'NeteaseOpenCourse' => 'NeteaseOpenCourse',
            'bilibili' => 'BiLiBiLiVideo',
            'iqiyi' => 'IQiYiVideo',
        ];
    }

    public function parseItem($url)
    {
        $kernel = ServiceKernel::instance();
        $extras = [];

        if ($kernel->hasParameter('media_parser')) {
            $extras = $kernel->getParameter('media_parser');
        }

        if (isset($extras['item'])) {
            $extrasParsers = $extras['item'];

            foreach ($extrasParsers as $extrasParser) {
                $class = $extrasParser['class'];
                $parser = new $class();

                if (!$parser->detect($url)) {
                    continue;
                }

                return $parser->parse($url);
            }
        }

        foreach ($this->getParsers() as $parserName) {
            /** @var AbstractItemParser $parser */
            $parser = $this->createParser("{$parserName}ItemParser");

            if (!$parser->detect($url)) {
                continue;
            }

            return $parser->parse($url);
        }

        throw ParserException::PARSER_NOT_SUPPORT();
    }

    public function prepareMediaUri($video)
    {
        if ('self' == $video['mediaSource']) {
            return $video;
        }

        $parsers = $this->getParsers();
        if (empty($parsers[$video['mediaSource']])) {
            throw ParserException::PARSER_NOT_SUPPORT();
        }
        $parser = $this->createParser($parsers[$video['mediaSource']].'ItemParser');

        return $parser->prepareMediaUri($video);
    }

    public function prepareYoukuMediaUri($video)
    {
        if ('youku' == $video['mediaSource']) {
            return $this->prepareMediaUri($video);
        }

        return $video;
    }

    public function prepareMediaUriForMobile($video, $httpSchema = '')
    {
        if ('youku' == $video['mediaSource']) {
            $parser = $this->createParser('YoukuVideoItemParser');
        } elseif ('qq' == $video['mediaSource']) {
            $parser = $this->createParser('QQVideoItemParser');
        } else {
            return $video;
        }

        return $parser->prepareMediaUriForMobile($video, $httpSchema);
    }

    private function createParser($parserName)
    {
        if (empty($this->mockedParser)) {
            $class = __NAMESPACE__.'\\ItemParser\\'.$parserName;

            return new $class();
        }

        return $this->mockedParser;
    }
}
