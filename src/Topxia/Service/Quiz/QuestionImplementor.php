<?php
namespace Topxia\Service\Quiz;

interface QuestionImplementor
{   
	public function getQuestion($question);

	public function createQuestion($question);

    public function updateQuestion($id, $fields);

}