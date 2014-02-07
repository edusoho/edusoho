<?php
namespace Topxia\Service\Question;

interface QuestionService
{
    public function getQuestion($id);

    public function findQuestionsByIds(array $ids);

    public function searchQuestions($conditions, $sort, $start, $limit);

    public function searchQuestionsCount($conditions);

    public function createQuestion($fields);

    public function updateQuestion($id, $fields);

    /**
     * 判断题目的回答是否正确
     *
     * 回答结果分4种情况：
     *   完全正确： 返回 array('status' => 'right')
     *   部分正确： 返回 array('status' => 'partRight', 'percentage' => '50%')，percentage为正确的比例
     *   错误： 返回 array('status' => 'wrong')
     *   未回答： 返回 array('status' => 'noAnswer')
     *   无法判断，题目不存在： 返回 array('status' => 'error', 'reason' => 'notFound')，题目不存在reason为notFound，无法判断reason为unable
     * @param integer $id 题目ID
     * @param mixed $answer 题目的回答
     * @param boolean  $refreshStats $refreshStats为true的话，更新题目做题统计信息
     * @return array 回答结果
     */
    public function judgeQuestion($id, $answer, $refreshStats = false);

    /**
     * 判断一批题目的回答是否正确
     * 
     * 参数$answers是一个一维数组，key为题目的ID，value为该题目的回答
     *
     * 返回结果 参考judgeQuestion的返回结果
     * 
     * @param  array  $answers 一批题目的回答
     * @param  boolean  $refreshStats $refreshStats为true的话，更新题目做题统计信息
     * @return array 回答结果
     */
    public function judgeQuestions(array $answers, $refreshStats = false);
}