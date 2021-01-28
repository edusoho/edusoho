<?php

namespace AppBundle\Component\MediaParser;

abstract class AbstractParser
{
    protected $mockedSender = null;

    public function parse($url)
    {
        $isSuccess = false;
        $urlSuffix = null;
        try {
            if (false !== strpos($url, '<iframe')) {
                $urlSegs = explode('src=', $url);
                $strAfterSrc = $urlSegs[1]; // 获取src 后面的内容
                $segs = explode('\'', $strAfterSrc);
                if (count($segs) < 2) { //说明不是以单引号分割
                    $segs = explode('"', $strAfterSrc);
                }

                $iframeSrc = $segs[1]; //获取src内容
                $srcContentSegs = explode('//', $iframeSrc);
                $urlSuffix = '//'.$srcContentSegs[1];
                $isSuccess = true;
            }
        } catch (\Exception $e) {
            $isSuccess = false;
        }

        $parsedInfo = $this->getDefaultParsedInfo();
        $parsedInfo['type'] = 'video';

        if ($isSuccess) {
            $parsedInfo['files'] = array(
                array(
                    'url' => $urlSuffix,
                ),
            );

            return $parsedInfo;
        } else {
            return $this->parseForWebUrl($parsedInfo, $url);
        }
    }

    public function detect($url)
    {
        foreach ($this->getUrlPrefixes() as $urlPrefix) {
            if (false !== strpos($url, $urlPrefix)) {
                return true;
            }
        }

        return false;
    }

    public function prepareMediaUri($video)
    {
        if ($this->detect($video['mediaUri'])) {
            $video = $this->convertMediaUri($video);
            $video['mediaSource'] = 'iframe';
        }

        return $video;
    }

    public function prepareMediaUriForMobile($video, $httpSchema)
    {
        $result = $this->prepareMediaUri($video);
        $defaultParsedInfo = $this->getDefaultParsedInfo();
        $result['mediaSource'] = $defaultParsedInfo['source'];
        $result['mediaUri'] = $httpSchema.':'.$result['mediaUri'];
        $result['hlsEncryption'] = true;

        return $result;
    }

    abstract protected function parseForWebUrl($parsedInfo, $url);

    /**
     * 格式为数组，所给的url包含任何一个 urlPrefix，视为当前parser
     */
    abstract protected function getUrlPrefixes();

    abstract protected function convertMediaUri($video);

    abstract protected function getDefaultParsedInfo();

    protected function fetchUrl($url)
    {
        if (!empty($this->mockedSender)) {
            return $this->mockedSender->fetchUrl($url);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_USERAGENT, $this->options['user_agent']);

        $content = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return array('code' => $code, 'content' => $content);
    }
}
