<?php

namespace AppBundle\Component\MediaParser;

abstract class AbstractParser
{
    protected $mockedSender = null;

    abstract public function parse($url);

    abstract public function detect($url);

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
