<?php

namespace AppBundle\Component\MediaParser;

use Topxia\Service\Common\ServiceKernel;

class ParserProxy
{
    public function parseItem($url)
    {
        $parsers = array('YoukuVideo', 'QQVideo', 'NeteaseOpenCourse', 'TudouVideo');

        $kernel = ServiceKernel::instance();
        $extras = array();

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

        foreach ($parsers as $parserName) {
            $class = __NAMESPACE__."\\ItemParser\\{$parserName}ItemParser";
            $parser = new $class();

            if (!$parser->detect($url)) {
                continue;
            }

            return $parser->parse($url);
        }

        throw ParserException::PARSER_NOT_SUPPORT();
    }

    public function prepareMediaUriForPc($video)
    {
        if ('youku' == $video['mediaSource']) {
            $class = __NAMESPACE__.'\\ItemParser\\YoukuVideoItemParser';
            $parser = new $class();
        } elseif ('NeteaseOpenCourse' == $video['mediaSource']) {
            $class = __NAMESPACE__.'\\ItemParser\\NeteaseOpenCourseItemParser';
            $parser = new $class();
        } elseif ('qqvideo' == $video['mediaSource']) {
            $class = __NAMESPACE__.'\\ItemParser\\QQVideoItemParser';
            $parser = new $class();
        } else {
            throw ParserException::PARSER_NOT_SUPPORT();
        }

        return $parser->prepareMediaUri($video);
    }

    public function prepareMediaUriForMobile($video, $httpSchema = '')
    {
        if ('youku' == $video['mediaSource']) {
            $class = __NAMESPACE__.'\\ItemParser\\YoukuVideoItemParser';
            $parser = new $class();
        } else {
            throw ParserException::PARSER_NOT_SUPPORT();
        }

        return $parser->prepareMediaUriForMobile($video, $httpSchema);
    }
}
