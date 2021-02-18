<?php

namespace Tests\Answer\Service;

use Tests\IntegrationTestCase;

class AnswerSceneServiceTest extends IntegrationTestCase
{
    public function testGet()
    {
        $fakeAnswerScene = $this->fakeAnswerScene();

        $answerScene = $this->getAnswerSceneService()->get(1);

        $this->assertEquals($fakeAnswerScene['id'], $answerScene['id']);
    }

    public function testGet_whenIdMiss_thenReturnEmpty()
    {
        $fakeAnswerScene = $this->fakeAnswerScene();

        $answerScene = $this->getAnswerSceneService()->get(2);

        $this->assertEmpty($answerScene);
    }

    public function testCreate()
    {
        $answerScene = [
            'name' => '考试',
            'limited_time' => 30,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => time(),
            'manual_marking' => 1,
            'doing_look_analysis' => 1,
            'pass_score' => 1.0,
        ];

        $result = $this->getAnswerSceneService()->create($answerScene);

        $this->assertEquals($result['name'], $answerScene['name']);
        $this->assertEquals($result['limited_time'], $answerScene['limited_time']);
        $this->assertEquals($result['do_times'], $answerScene['do_times']);
        $this->assertEquals($result['redo_interval'], $answerScene['redo_interval']);
        $this->assertEquals($result['need_score'], $answerScene['need_score']);
        $this->assertEquals($result['pass_score'], $answerScene['pass_score']);
        $this->assertEquals($result['doing_look_analysis'], $answerScene['doing_look_analysis']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testCreate_whenParamsMiss_thenThrowException()
    {
        $answerScene = [
            'name' => '',
            'limited_time' => '-1',
            'do_times' => '-1',
            'redo_interval' => '-1',
            'need_score' => '-1',
        ];

        $this->getAnswerSceneService()->create($answerScene);
    }

    public function testUpdate()
    {
        $fakeAnswerScene = $this->fakeAnswerScene();

        $answerScene = [
            'name' => '考试',
            'limited_time' => 10,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => time(),
            'manual_marking' => 1,
            'doing_look_analysis' => 0,
            'pass_score' => 1.0,
        ];

        $result = $this->getAnswerSceneService()->update(1, $answerScene);

        $this->assertEquals($result['name'], $answerScene['name']);
        $this->assertEquals($result['limited_time'], $answerScene['limited_time']);
        $this->assertEquals($result['do_times'], $answerScene['do_times']);
        $this->assertEquals($result['redo_interval'], $answerScene['redo_interval']);
        $this->assertEquals($result['need_score'], $answerScene['need_score']);
        $this->assertEquals($result['pass_score'], $answerScene['pass_score']);
        $this->assertEquals($result['doing_look_analysis'], $answerScene['doing_look_analysis']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException
     * @expectedExceptionCode 40495201
     */
    public function testUpdate_whenIdMiss_thenThrowException()
    {
        $fakeAnswerScene = $this->fakeAnswerScene();

        $this->getAnswerSceneService()->update(2, [
            'name' => '考试',
            'limited_time' => 10,
            'do_times' => 1,
            'redo_interval' => 1,
            'need_score' => 1,
            'start_time' => -1,
            'manual_marking' => -1,
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testUpdate_whenParamsMiss_thenThrowException()
    {
        $fakeAnswerScene = $this->fakeAnswerScene();

        $answerScene = [
            'limited_time' => -1,
            'do_times' => -1,
            'redo_interval' => -1,
            'need_score' => -1,
            'start_time' => -1,
            'manual_marking' => -1,
        ];

        $this->getAnswerSceneService()->update(1, $answerScene);
    }

    public function testCanStart()
    {
        $answerScene = [
            'id' => 1,
            'name' => '考试',
            'limited_time' => 1,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => 0,
            'manual_marking' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $answerScene = $this->getAnswerSceneDao()->create($answerScene);

        $result = $this->getAnswerSceneService()->canStart(1, 1);

        $this->assertEquals($result, true);
    }

    public function testCanStart_whenStartTimeInvalid_thenReturnFalse()
    {
        $answerScene = [
            'id' => 1,
            'name' => '考试',
            'limited_time' => 1,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => time() + 100,
            'manual_marking' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $answerScene = $this->getAnswerSceneDao()->create($answerScene);

        $result = $this->getAnswerSceneService()->canStart(1, 1);

        $this->assertEquals($result, false);
    }

    public function testCanStart_whenIdMiss_thenReturnFalse()
    {
        $result = $this->getAnswerSceneService()->canStart(1, 1);

        $this->assertEquals($result, false);
    }

    public function testCanStart_whenDoTimesEQ1_thenReturnFalse()
    {
        $answerScene = [
            'id' => 1,
            'name' => '考试',
            'limited_time' => 1,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => 0,
            'manual_marking' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $answerScene = $this->getAnswerSceneDao()->create($answerScene);

        $answerRecord = $this->getAnswerRecordDao()->create([
            'answer_scene_id' => 1,
            'user_id' => 1,
            'status' => 'finished',
        ]);

        $result = $this->getAnswerSceneService()->canStart(1, 1);

        $this->assertEquals($result, false);
    }

    public function testCanStart_whenRedoIntervaliInvalid_thenReturnFalse()
    {
        $answerScene = [
            'id' => 1,
            'name' => '考试',
            'limited_time' => 1,
            'do_times' => 0,
            'redo_interval' => 30,
            'need_score' => 1,
            'start_time' => 0,
            'manual_marking' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $answerScene = $this->getAnswerSceneDao()->create($answerScene);

        $answerRecord = $this->getAnswerRecordDao()->create([
            'answer_scene_id' => 1,
            'answer_report_id' => 1,
            'user_id' => 1,
            'status' => 'finished',
            'end_time' => time(),
        ]);

        $answerReport = $this->getAnswerReportDao()->create([
            'id' => 1,
            'answer_record_id' => 1,
            'review_time' => time(),
        ]);

        $result = $this->getAnswerSceneService()->canStart(1, 1);

        $this->assertEquals($result, false);
    }

    public function testCount()
    {
        $this->fakeAnswerScene();

        $count = $this->getAnswerSceneService()->count([]);

        $this->assertEquals($count, 1);
    }

    public function testSearch()
    {
        $this->fakeAnswerScene();

        $answerScenes = $this->getAnswerSceneService()->search([], [], 0, 1);

        $this->assertEquals(count($answerScenes), 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException
     * @expectedExceptionCode 40495201
     */
    public function testBuildAnswerSceneReport_whenIdMiss_thenThrowException()
    {
        $this->getAnswerSceneService()->buildAnswerSceneReport(1);
    }

    public function testGetAnswerSceneReport()
    {
        $this->fakeAnswerScene();

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerReportService', [
            [
                'functionName' => 'findByAnswerSceneId',
                'returnValue' => [
                    ['score' => 1.0],
                    ['score' => 2.0],
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'findByAnswerSceneId',
                'returnValue' => [
                    ['user_id' => 1, 'status' => 'doing'],
                    ['user_id' => 2, 'status' => 'finished'],
                ],
            ],
            [
                'functionName' => 'count',
                'returnValue' => 100
            ],
            [
                'functionName' => 'search',
                'returnValue' => ['id' => 1]
            ],
        ]);

        $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport(1);

        $this->assertEquals($answerSceneReport['answer_scene_id'], 1);
        $this->assertEquals($answerSceneReport['joined_user_num'], 2);
        $this->assertEquals($answerSceneReport['finished_user_num'], 1);
        $this->assertEquals($answerSceneReport['avg_score'], 1.5);
        $this->assertEquals($answerSceneReport['max_score'], 2.0);
        $this->assertEquals($answerSceneReport['min_score'], 1.0);
    }

    protected function fakeAnswerScene()
    {
        $answerScene = [
            'id' => 1,
            'name' => '考试',
            'limited_time' => 1,
            'do_times' => 1,
            'redo_interval' => 0,
            'need_score' => 1,
            'start_time' => 0,
            'manual_marking' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];

        return $this->getAnswerSceneDao()->create($answerScene);
    }

    protected function getAnswerSceneDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerSceneDao');
    }

    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAnswerRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRecordDao');
    }

    protected function getAnswerReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReportDao');
    }
}
