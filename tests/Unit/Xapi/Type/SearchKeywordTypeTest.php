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
            array('user_id' => 1, 'uuid' => 10, 'occur_time' => time(), 'context' => array('q' => 'PHP基础入门', 'type' => 'course', 'uri' => '/cloud/search?q=PHP基础入门&type=course')),
            array('user_id' => 2, 'uuid' => 20, 'occur_time' => time(), 'context' => array('q' => 'Java入门班', 'type' => 'classroom', 'uri' => '/cloud/search?q=Java入门班&type=classroom')),
            array('user_id' => 3, 'uuid' => 30, 'occur_time' => time(), 'context' => array('q' => '张艺谋', 'type' => 'teacher', 'uri' => '/cloud/search?q=张艺谋&type=teacher')),
            array('user_id' => 4, 'uuid' => 40, 'occur_time' => time(), 'context' => array('q' => '如何拥有超能力', 'type' => 'article', 'uri' => '/search?q=如何拥有超能力&type=article')),
            array('user_id' => 5, 'uuid' => 50, 'occur_time' => time(), 'context' => array('q' => '怎么走上人生巅峰', 'type' => 'thread', 'uri' => '/cloud/search?q=怎么走上人生巅峰&type=thread')),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'result', 'timestamp'), array_keys($pushStatements[0]));
        foreach ($statements as $index => $st) {
            $this->assertEquals($st['context']['q'], $pushStatements[$index]['result']['response']);
            $this->assertTrue(strpos($pushStatements[$index]['object']['id'], $st['context']['q']) > 0);
        }

        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/search-engine', $pushStatements[0]['object']['definition']['type']);
        $this->assertEquals('http://id.tincanapi.com/activitytype/user-profile',
            $pushStatements[2]['result']['extensions']['https://w3id.org/xapi/acrossx/extensions/type']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/message',
            $pushStatements[3]['result']['extensions']['https://w3id.org/xapi/acrossx/extensions/type']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
