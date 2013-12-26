<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{
    /*
     *  quiz_question
     */

    public function getQuestion($id);

    public function createQuestion($question);

    public function updateQuestion($id, $question);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);

    public function searchQuestionCount(array $conditions);

    public function findQuestionsByIds(array $ids);

    public function findQuestionsForTestPaper($field, $courseId);
    
    public function getQuestionsNumberByCourseId($courseId);

    /*
     *  quiz_question_choice
     */

    public function findChoicesByQuestionIds(array $id);

    /*
     *  quiz_question_category
     */
    
    public function getCategory($id);

    public function createCategory($category);

	public function updateCategory($categoryId, $category);		

    public function findCategorysByCourseIds(array $id);

    public function sortCategories($courseId, array $categoryIds);

}