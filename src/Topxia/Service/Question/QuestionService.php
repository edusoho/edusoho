<?php
namespace Topxia\Service\Question;

interface QuestionService
{
    const MAX_CATEGORY_QUERY_COUNT = 1000;

    public function getQuestion($id);

    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function findQuestionsByParentIds($ids);

    public function searchQuestions($conditions, $sort, $start, $limit);

    public function searchQuestionsCount($conditions);

    public function createQuestion($fields);

    public function updateQuestion($id, $fields);

    public function deleteQuestion($id);

    /**
     * 判断题目的回答是否正确
     *
     * 回答结果分4种情况：
     *   完全正确： 返回 array('status' => 'right')
     *   部分正确： 返回 array('status' => 'partRight', 'percentage' => '50')，percentage为正确的比例
     *   错误： 返回 array('status' => 'wrong')
     *   未回答： 返回 array('status' => 'noAnswer')
     *   无法判断： 返回 array('status' => 'none')
     *   题目不存在：返回 array('status' => 'notFound')
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

    public function getCategory($id);

    public function findCategoriesByTarget($target, $start, $limit);

    public function findCategoriesByIds($ids);

    public function createCategory($fields);

    public function updateCategory($id, $fields);

    public function deleteCategory($id);

    public function sortCategories($target, array $sortedIds);
}