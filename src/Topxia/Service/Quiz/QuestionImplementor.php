<?php
namespace Topxia\Service\Quiz;

interface QuestionImplementor
{   
	public function getQuestion($question);

	public function createQuestion($question, $questionField);

    public function updateQuestion($question, $questionField);
}