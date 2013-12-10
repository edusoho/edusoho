<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{

    public function getQuestion($lessonQuizItemId);

    public function getCategory($id);

    public function findChoicesByQuestionIds(array $id);

    public function addQuestion($type,$question);

    public function getQuestionTarget($courseId);

    public function searchQuestionCount(array $conditions);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);

    public function searchCategoryCount(array $conditions);

    public function searchCategory(array $conditions, array $orderBy, $start, $limit);

    public function createCategory($courseId, $category);

	public function editCategory($courseId, $category);				

}