<?php

namespace Tests\Item\Service;

use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;
use Codeages\Biz\ItemBank\Item\Dao\QuestionFavoriteDao;
use Tests\IntegrationTestCase;

class QuestionFavoriteServiceTest extends IntegrationTestCase
{
    public function testCreate()
    {
        $this->mockObjectIntoBiz('ItemBank:Item:QuestionDao', [[
            'functionName' => 'get',
            'returnValue' => ['id' => 1, 'item_id' => 1],
        ]]);

        $questionFavorite = [
            'target_type' => 1,
            'target_id' => 1,
            'question_id' => 1,
            'user_id' => 1,
        ];

        $questionFavorite = $this->getQuestionFavoriteService()->create($questionFavorite);

        $this->assertEquals($questionFavorite['target_type'], 1);
        $this->assertEquals($questionFavorite['target_id'], 1);
        $this->assertEquals($questionFavorite['question_id'], 1);
        $this->assertEquals($questionFavorite['user_id'], 1);
        $this->assertEquals($questionFavorite['item_id'], 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testCreate_whenParamsMiss_thenThrowException()
    {
        $questionFavorite = [
            'target_type' => 'test',
            'target_id' => 'test',
            'user_id' => 'test',
            'question_id' => 'test',
        ];

        $this->getQuestionFavoriteService()->create($questionFavorite);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     * @expectedExceptionCode 40495008
     */
    public function testCreate_whenQuestionNotFound_thenThrowException()
    {
        $questionFavorite = [
            'target_type' => 1,
            'target_id' => 1,
            'question_id' => 1,
            'user_id' => 1,
        ];

        $this->getQuestionFavoriteService()->create($questionFavorite);
    }

    public function testDelete()
    {
        $this->mockQuestionFavorite();
        $result = $this->getQuestionFavoriteService()->delete($id);
        $this->assertEmpty($result);
    }

    public function testDeleteByQuestionFavorite()
    {
        $questionFavorit = $this->mockQuestionFavorite();
        $result = $this->getQuestionFavoriteService()->deleteByQuestionFavorite($questionFavorit);
        $this->assertEquals($result, 1);
    }

    public function testSearch()
    {
        $this->mockQuestionFavorite();

        $questionFavorites = $this->getQuestionFavoriteService()->search(['id' => '1'], [], 0, 1);

        $this->assertEquals($questionFavorites[0]['target_type'], 1);
        $this->assertEquals($questionFavorites[0]['target_id'], 1);
        $this->assertEquals($questionFavorites[0]['question_id'], 1);
        $this->assertEquals($questionFavorites[0]['user_id'], 1);
    }

    public function testCount()
    {
        $this->mockQuestionFavorite();

        $count = $this->getQuestionFavoriteService()->count(['id' => '1']);

        $this->assertEquals($count, 1);
    }

    protected function mockQuestionFavorite()
    {
        return $this->getQuestionFavoriteDao()->create([
            'id' => 1,
            'target_type' => 1,
            'target_id' => 1,
            'question_id' => 1,
            'user_id' => 1,
        ]);
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->biz->service('ItemBank:Item:QuestionFavoriteService');
    }

    /**
     * @return QuestionFavoriteDao
     */
    protected function getQuestionFavoriteDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionFavoriteDao');
    }
}
