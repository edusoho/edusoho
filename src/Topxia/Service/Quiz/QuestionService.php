<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{
    /*
     *  quiz_question
     */

    public function getQuestionTarget($courseId);

    public function getQuestion($lessonQuizItemId);

    public function addQuestion($type,$question);

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

    public function createCategory($courseId, $category);

	public function editCategory($courseId, $category);		

    public function findCategoryByCourseIds(array $id);

    public function searchCategory(array $conditions, array $orderBy, $start, $limit);

    public function searchCategoryCount(array $conditions);

}