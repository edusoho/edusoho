<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthClient\AppleAuthClient;
use AppBundle\Common\ReflectionUtils;

class AppleAuthClientTest extends BaseTestCase
{
    public function testGetAccessToken()
    {
        $config = [
            'clientId' => 'com.edusoho.kuozhi',
            'teamId' => 'ZKQ4HP5426',
            'keyId' => '8568372JXY',
            'secretKey' => '-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQg6JmVKrowJrSEAOde
k7H0VYACLtPpr518n81qSv5KQ/2gCgYIKoZIzj0DAQehRANCAAQUxJrR1CjAcQKy
Kq9UfMu+BC7kzCstyWzli+S2J1Rezezq7xMhAaBE2wRTUcg3mv6Wj07abaQ4V0M7
/ZN7RsCI
-----END PRIVATE KEY-----',
        ];
        $client = new AppleAuthClient($config);

//        $result = $client->getAccessToken('ca97270a9af9846789035a1b548e028e7.0.mszt.V168sDykb6LMudCv3Od2tA', '');

    }

    public function testParseToken()
    {
        var_dump(json_decode(base64_decode('eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLmVkdXNvaG8ua3VvemhpIiwiZXhwIjoxNTkyNTMzODU3LCJpYXQiOjE1OTI1MzMyNTcsInN1YiI6IjAwMDI5My4wNWE0ZjIyYmRjNDg0OThiYTBhMzE4ZjMyOGFkOGMzZC4wMTU1IiwiY19oYXNoIjoiNEpEVXdRdWxtQVRXR29xZWY0Q2VPdyIsImVtYWlsIjoiZmllc3ZoN3J4eUBwcml2YXRlcmVsYXkuYXBwbGVpZC5jb20iLCJlbWFpbF92ZXJpZmllZCI6InRydWUiLCJpc19wcml2YXRlX2VtYWlsIjoidHJ1ZSIsImF1dGhfdGltZSI6MTU5MjUzMzI1Nywibm9uY2Vfc3VwcG9ydGVkIjp0cnVlfQ')));
    }
}