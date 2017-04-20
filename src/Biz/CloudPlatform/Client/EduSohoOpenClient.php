<?php

namespace Biz\CloudPlatform\Client;

class EduSohoOpenClient
{
    public function getArticles()
    {
        $url = 'http://open.edusoho.com/api/v1/context/articles';
        $articles = $this->getContents($url);

        return $articles;
    }

    public function getNotices()
    {
        $url = 'http://open.edusoho.com/api/v1/context/notice';
        $notices = $this->getContents($url);

        return $notices;
    }

    protected function getContents($url)
    {
        $userAgent = 'Open Edusoho App Client 1.0';
        $connectTimeout = 10;
        $timeout = 10;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        $contents = curl_exec($curl);
        curl_close($curl);

        return $contents;
    }
}
