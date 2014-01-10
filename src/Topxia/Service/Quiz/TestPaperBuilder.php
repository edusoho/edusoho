<?php
namespace Topxia\Service\Quiz;

interface TestPaperBuilder{
	
	public function prepare($testPaper, $options);


	public function build();

	public function validate();


	public function getQuestions();

	public function getMessage();
	

}