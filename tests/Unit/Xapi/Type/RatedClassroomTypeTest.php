<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\RatedClassroomType;

class RatedClassroomTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));

        $type = new RatedClassroomType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_id' => 1, 'target_type' => 'classroom', 'occur_time' => time(), 'context' => array('name' => '高级Java班级', 'score' => array('raw' => 3, 'max' => 5, 'min' => 0))),
            array('user_id' => 2, 'uuid' => 20, 'target_id' => 2, 'target_type' => 'classroom', 'occur_time' => time()), 'context' => array('name' => '初级Java班级', 'score' => array('raw' => 2, 'max' => 5, 'min' => 0))
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'timestamp', 'object', 'result'), array_keys($pushStatements[0]));
        foreach ($statements as $index => $st) {
            $this->assertEquals(array('id' => 'http://id.tincanapi.com/verb/rated', 'display' => array(
                'zh-CN' => '评分了', 'en-US' => 'rated'
            )), $st['verb']);
        }

        $this->assertEquals(array('score' => array('raw' => 3, 'max' => 5, 'min' => 0)), $pushStatements[0]['result']);
        $this->assertEquals(array('score' => array('raw' => 2, 'max' => 5, 'min' => 0)), $pushStatements[1]['result']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}