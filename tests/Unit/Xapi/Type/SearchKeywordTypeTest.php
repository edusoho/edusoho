<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\SearchKeywordType;

class SearchKeywordTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));

        $type = new SearchKeywordType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'occur_time' => time(), 'data' => array('q' => 'PHP基础入门', 'type' => 'course')),
            array('user_id' => 2, 'uuid' => 20, 'occur_time' => time(), 'data' => array('q' => 'Java入门班', 'type' => 'classroom')),
            array('user_id' => 3, 'uuid' => 30, 'occur_time' => time(), 'data' => array('q' => '张艺谋', 'type' => 'teacher')),
            array('user_id' => 4, 'uuid' => 40, 'occur_time' => time(), 'data' => array('q' => '如何拥有超能力', 'type' => 'article')),
            array('user_id' => 5, 'uuid' => 50, 'occur_time' => time(), 'data' => array('q' => '怎么走上人生巅峰', 'type' => 'thread')),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'result', 'timestamp'), array_keys($pushStatements[0]));
        foreach ($statements as $index => $st) {
            $this->assertEquals($st['data']['q'], $pushStatements[$index]['result']['response']);
            $this->assertEquals('/search?q='.$st['data']['q'].'&type='.$st['data']['type'], $pushStatements[$index]['object']['id']);
        }

        $this->assertEquals('http://adlnet.gov/expapi/activities/course', $pushStatements[0]['object']['definition']['type']);
        $this->assertEquals('Agent', $pushStatements[2]['object']['objectType']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
