<?php
namespace Codeages\RestApiClient\HttpRequest;

use Codeages\RestApiClient\Exceptions\ServerException;
use Codeages\RestApiClient\Exceptions\ResponseException;

class CurlHttpRequest extends HttpRequest
{
    public function request($method, $url, $body, array $headers = array(), $requestId = '')
    {
        $this->debug && $this->logger && $this->logger->debug($this->message($requestId, "{$method} {$url}"), array('headers' => $headers, 'body' => $body));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $this->options['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->options['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->options['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($method == 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlinfo['header_size']);
        $body   = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        $context = array(
            'CURLINFO' => $curlinfo,
            'HEADER'   => $header,
            'BODY'     => $body
        );

        $this->debug && $this->logger && $this->logger->debug($this->message($requestId, 'Response context.'), $context);

        if (empty($curlinfo['namelookup_time'])) {
            $message = $this->message($requestId, "Dns look up timeout (url: {$url}).");
            $this->logger && $this->logger->error($message, $context);
            throw new ResponseException($message);
        }

        if (empty($curlinfo['connect_time'])) {
            $message = $this->message($requestId, "Connect timeout (url: {$url}).");
            $this->logger && $this->logger->error($message, $context);
            throw new ResponseException($message);
        }

        if (empty($curlinfo['starttransfer_time'])) {
            $message = $this->message($requestId, "Request timeout (url: {$url}).");
            $this->logger && $this->logger->error($message, $context);
            throw new ResponseException($message);
        }

        if ($curlinfo['http_code'] >= 500) {
            $message = $this->message($requestId, "Server internal error (url: {$url}).");
            $this->logger && $this->logger->error($message, $context);
            throw new ServerException($message);
        }

        return $body;
    }

    protected function message($requestId, $message)
    {
        return "[CurlHttpRequest #$requestId] {$message}";
    }
}
