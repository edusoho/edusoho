<?php

namespace Custom\Service\Homework\Impl;

use Homework\Service\Homework\Impl\HomeworkServiceImpl as BaseHomeworkServiceImpl;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseHomeworkServiceImpl implements HomeworkService
{
	public function randomizeHomeworkResultForPairReview($homeworkId,$userId){
		$homework=$this->getHomeworkDao()->
	}

	private function getReviewDao(){
	    	return $this->createDao('Homework:Homework.ReviewDao');
	}
}