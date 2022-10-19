<?php

namespace ESLive\SDK;

use Firebase\JWT\JWT;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class ESLiveApi
{
    private $accessKey;

    private $secretKey;

    private $options;

    private $client;

    public function __construct(string $accessKey, string $secretKey, array $options = [])
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->options = $options;

        if (empty($this->options['endpoint'])) {
            $this->options['endpoint'] = "https://live.edusoho.com/";
        }
    }

    public function createMemberGroupBundle(string $name): array {
        return $this->request('POST', '/api-v2/memberGroup/createBundle', [
            'json' => [
                'name' => $name,
            ]
        ]);
    }

    public function createMemberGroup(string $bundleNo, string $name): array {
        return $this->request('POST', '/api-v2/memberGroup/create', [
            'json' => [
                'bundleNo' => $bundleNo,
                'name' => $name,
            ]
        ]);
    }

    public function batchCreateMemberGroup(string $bundleNo, array $names): array {
        return $this->request('POST', '/api-v2/memberGroup/batchCreate', [
            'json' => [
                'bundleNo' => $bundleNo,
                'names' => $names,
            ]
        ]);
    }

    public function updateMemberGroupName(string $groupNo, string $name): array {
        return $this->request('POST', '/api-v2/memberGroup/updateName', [
            'json' => [
                'no' => $groupNo,
                'name' => $name,
            ]
        ]);
    }

    public function deleteMemberGroup(string $groupNo): void {
        $this->request('POST', '/api-v2/memberGroup/delete', [
            'json' => [
                'no' => $groupNo,
            ]
        ]);
    }

    public function batchDeleteMemberGroups(array $groupNos): void {
        $this->request('POST', '/api-v2/memberGroup/batchDelete', [
            'json' => [
                'nos' => $groupNos,
            ]
        ]);
    }

    public function createRtmpPushUrl(int $roomId, int $expireTime): array {
        return $this->request('POST', '/api-v2/stream/createRtmpPushUrl', [
            'json' => [
                'roomId' => $roomId,
                'expireTime' => $expireTime,
            ]
        ]);
    }

    public function getRtmpPlayUrl(int $roomId, int $expireTime): array {

        return $this->request('GET', '/api-v2/stream/getRtmpPlayUrl', [
            'query' => [
                'roomId' => $roomId,
                'expireTime' => $expireTime,
            ]
        ]);
    }

    private function request(string $method, string $uri, array $options = []): array {
        if (!$this->client) {
            $this->client = HttpClient::create([
                'http_version' => '1.1',
                'base_uri' => $this->options['endpoint'],
                'timeout' => 15,
            ]);
        }

        $token = JWT::encode([
            'iss' => 'live api',
            'exp' => time() + 600,
        ], $this->secretKey, 'HS256', $this->accessKey);

        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }
        $options['headers'][] = 'Authorization: Bearer ' . $token;

        try {
            $response =  $this->client->request($method, $uri, $options);
            $status = $response->getStatusCode();
            $content = $response->toArray(false);
        } catch (Throwable $e) {
            throw new SDKException($e->getMessage(), 'HTTP_ERROR', '');
        }

        if ($status >= 300 || $status < 200) {
            throw new SDKException($content['message'] ?? '', $content['code'] ?? 'UNKNOWN', $content['traceId'] ?? '');
        }

        return $content;
    }
}