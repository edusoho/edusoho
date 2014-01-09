<?php
namespace Topxia\Service\Quiz;


interface TestPaperBuilder{
	public function prepareBuild($testPaper,$options);
	public function build();
	public function validate();
	public function getQuestions();
	public function getValidations();
}