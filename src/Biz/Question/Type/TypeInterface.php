<?php

namespace Biz\Question\Type;

interface TypeInterface
{
    public function create($fields);

    public function update($id, $fields);

    public function delete($id);

    public function get($id);

    public function judge($question, $answer);

    public function filter(array $fields);

    /**
     * [getAnswerStructure 题目分析的答案结构]
     *
     * @param [type] $question [description]
     *
     * @return [type] [description]
     */
    public function getAnswerStructure($question);

    /**
     * [analysisAnswerIndex 用户答案在正确答案里的索引值]
     *
     * @param [type] $question   [description]
     * @param [type] $userAnswer [description]
     *
     * @return [type] [description]
     */
    public function analysisAnswerIndex($question, $userAnswer);
}
