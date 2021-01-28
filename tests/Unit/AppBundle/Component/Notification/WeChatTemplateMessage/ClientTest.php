<?php

namespace Tests\Unit\AppBundle\Component\Notification\WeChatTemplateMessage;

use AppBundle\Component\Notification\WeChatTemplateMessage\Client;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class ClientTest extends BaseTestCase
{
    public function testGetAccessToken()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result1 = $client->getAccessToken();

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/token',
                        array(
                            'appid' => 'auth_key',
                            'secret' => 'auth_secret',
                            'grant_type' => 'client_credential',
                        ),
                    ),
                    'returnValue' => '{"access_token":"ACCESS_TOKEN","expires_in":7200}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken();
        $this->assertEmpty($result1);
        $this->assertEquals('ACCESS_TOKEN', $result['access_token']);
    }

    public function testGetUserInfo()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/user/info',
                        array(
                            'openid' => 'o6_bmjrPTlm6_2sgVt7hMZOPfL2M',
                            'lang' => 'zh_CN',
                        ),
                    ),
                    'returnValue' => '{"subscribe": 1,"openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M", "nickname": "Band", "subscribe_scene": "ADD_SCENE_QR_CODE","qr_scene": 98765}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getUserInfo('o6_bmjrPTlm6_2sgVt7hMZOPfL2M');
        $this->assertEquals('Band', $result['nickname']);
    }

    public function testBatchGetUserInfo()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/user/info/batchget',
                        array(
                            'user_list' => array(
                                array(
                                    'openid' => 'otvxTs4dckWG7imySrJd6jSi0CWE',
                                    'lang' => 'zh_CN',
                                ),
                            ),
                        ),
                    ),
                    'returnValue' => '{"user_info_list":[{"openid": "otvxTs4dckWG7imySrJd6jSi0CWE","nickname": "iWithery"}]}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->batchGetUserInfo(array(array(
            'openid' => 'otvxTs4dckWG7imySrJd6jSi0CWE',
            'lang' => 'zh_CN',
        )));

        $this->assertEquals('otvxTs4dckWG7imySrJd6jSi0CWE', $result[0]['openid']);
    }

    public function testGetUserList()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/user/get',
                        array(
                              'next_openid' => 'NEXT_OPENID',
                            ),
                        ),
                'returnValue' => '{"total":2,"count":2,"data":{"openid":["OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}',
                'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getUserList('NEXT_OPENID');
        $this->assertEquals(2, $result['total']);
    }

    public function testSetIndustry()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/template/api_set_industry',
                        array(
                            'industry_id1' => '1',
                            'industry_id2' => '4',
                        ),
                    ),
                    'returnValue' => '{"errcode" : 0,"errmsg" : "ok"}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->setIndustry('1', '4');
        $this->assertEquals('ok', $result['errmsg']);
    }

    public function testGetIndustry()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/template/get_industry',
                        array(
                        ),
                    ),
                    'returnValue' => '{"primary_industry":{"first_class":"运输与仓储","second_class":"快递"},"secondary_industry":{"first_class":"IT科技","second_class":"互联网|电子商务"}}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getIndustry();
        $this->assertEquals('运输与仓储', $result['primary_industry']['first_class']);
    }

    public function testAddTemplate()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result1 = $client->addTemplate('EDUSOHOTEST');

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/template/api_add_template',
                        array(
                            'template_id_short' => 'TM00015',
                        ),
                    ),
                    'returnValue' => '{"errcode":0,"errmsg":"ok","template_id":"Doclyl5uP7Aciu-qZ7mJNPtWkbkYnWBWVja26EGbNyk"}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->addTemplate('TM00015');
        $this->assertEmpty($result1);
        $this->assertEquals('Doclyl5uP7Aciu-qZ7mJNPtWkbkYnWBWVja26EGbNyk', $result['template_id']);
    }

    public function testGetTemplateList()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template',
                        array(
                        ),
                    ),
                    'returnValue' => '{"template_list": [{"template_id": "iPk5sOIt5X_flOVKn5GrTFpncEYTojx6ddbt8WYoV5s","title": "领取奖金提醒","primary_industry": "IT科技","deputy_industry": "互联网|电子商务","content": "{ {result.DATA} }\n\n领奖金额:{ {withdrawMoney.DATA} }\n领奖  时间:    { {withdrawTime.DATA} }\n银行信息:{ {cardInfo.DATA} }\n到账时间:  { {arrivedTime.DATA} }\n{ {remark.DATA} }","example": "您已提交领奖申请\n\n领奖金额：xxxx元\n领奖时间：2013-10-10 12:22:22\n银行信息：xx银行(尾号xxxx)\n到账时间：预计xxxxxxx\n\n预计将于xxxx到达您的银行卡"}]}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getTemplateList();
        $this->assertEquals('iPk5sOIt5X_flOVKn5GrTFpncEYTojx6ddbt8WYoV5s', $result['template_list'][0]['template_id']);
    }

    public function testDeleteTemplate()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/template/del_private_template',
                        array(
                            'template_id' => 'Dyvp3-Ff0cnail_CDSzk1fIc6-9lOkxsQE7exTJbwUE',
                        ),
                    ),
                    'returnValue' => '{"errcode" : 0,"errmsg" : "ok"}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->deleteTemplate('Dyvp3-Ff0cnail_CDSzk1fIc6-9lOkxsQE7exTJbwUE');
        $this->assertEquals('ok', $result['errmsg']);
    }

    public function testSendTemplateMessage()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/message/template/send',
                        array(
                            'touser' => 'OPENID',
                            'template_id' => 'ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY',
                            'data' => array('first' => array('value' => '恭喜你购买成功！', 'color' => '#173177')),
                            'url' => 'http://weixin.qq.com/download',
                            'miniprogram' => array('appid' => 'xiaochengxuappid12345', 'pagepath' => 'index?foo=bar'),
                        ),
                    ),
                    'returnValue' => '{"errcode" : 0,"errmsg" : "ok","msgid":200228332}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->sendTemplateMessage('OPENID', 'ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY', array('first' => array('value' => '恭喜你购买成功！', 'color' => '#173177')), $options = array('url' => 'http://weixin.qq.com/download', 'miniprogram' => array('appid' => 'xiaochengxuappid12345', 'pagepath' => 'index?foo=bar')));

        $this->assertEquals('200228332', $result['msgid']);
    }
}
