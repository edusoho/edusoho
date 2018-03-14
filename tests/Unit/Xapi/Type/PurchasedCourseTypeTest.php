<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\PurchasedCourseType;

class PurchasedCourseTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));

        $type = new PurchasedCourseType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_id' => 1, 'target_type' => 'course', 'occur_time' => time(), 'context' => array('title' => 'PHP基础入门', 'pay_amount' => 399.99)),
            array('user_id' => 2, 'uuid' => 20, 'target_id' => 2, 'target_type' => 'classroom', 'occur_time' => time(), 'context' => array('title' => 'Java入门班', 'pay_amount' => 1024.10)),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'result', 'timestamp'), array_keys($pushStatements[0]));
        foreach ($statements as $index => $st) {
            $this->assertEquals($st['target_id'], $pushStatements[$index]['object']['id']);
            $this->assertEquals($st['context']['title'], $pushStatements[$index]['object']['definition']['name']['zh-CN']);
            $this->assertEquals($st['context']['pay_amount'], $pushStatements[$index]['result']['extensions']['http://xapi.edusoho.com/extensions/amount']);
        }

        $this->assertEquals('http://adlnet.gov/expapi/activities/course', $pushStatements[0]['object']['definition']['type']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/class-online', $pushStatements[1]['object']['definition']['type']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
