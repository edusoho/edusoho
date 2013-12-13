<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{
    /*
     *  quiz_question
     */

    public function getQuestion($lessonQuizItemId);

    public function createQuestion($question);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);

    public function searchQuestionCount(array $conditions);

    /*
     *  quiz_question_choice
     */

    public function findChoicesByQuestionIds(array $id);


    /*
     *  quiz_question_category
     */
    
    public function getCategory($id);

    public function createCategory($category);

	public function editCategory($category);		

    public function findCategoryByCourseIds(array $id);

    public function sortCategory($courseId, array $categoryIds);

}