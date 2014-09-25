<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\TestpaperService;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
	public function doTestpaper()
	{
		$testId = $this->getParam("testId");
		$targetType = $this->getParam("targetType");
		$targetId= $this->getParam("targetId");
		$user = $this->controller->getUserByToken($this->request);
                        if (!$user->isLogin()) {
                            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
                        }

                        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

	        	$targets = $this->controller->get('topxia.target_helper')->getTargets(array($testpaper['target']));
	        	if ($targets[$testpaper['target']]['type'] != 'course') {
                        	return $this->createErrorResponse('error', '试卷只能属于课程');
	        	}

	        	$course = $this->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

	        	if (empty($course)) {
                        	return $this->createErrorResponse('error', '试卷所属课程不存在！');
	        	}

	        	if (!$this->getCourseService()->canTakeCourse($course)) {
                        	return $this->createErrorResponse('error', '不是试卷所属课程老师或学生');
	        	}

	        	if (empty($testpaper)) {
                        	return $this->createErrorResponse('error', '试卷不存在！或已删除!');
	        	}

	        	$testpaperResult = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive($testId, $user['id']);

        		if (empty($testpaperResult)) {
	            	if ($testpaper['status'] == 'draft') {
	                        	return $this->createErrorResponse('error', '该试卷未发布，如有疑问请联系老师！!');
	            	}
	            	if ($testpaper['status'] == 'closed') {
	                        	return $this->createErrorResponse('error', '该试卷已关闭，如有疑问请联系老师！!');
	            	}

	            	$testpaperResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));
            	}

            	$result = $this->getTestpaperService()->showTestpaper($testId);
        		$items = $result['formatItems'];
        		unset($testpaper['metas']);
                        return array(
                            'testpaper'=>$testpaper,
                            'items'=>$this->coverTestpaperItems($items)
                            );
	}

	private function coverTestpaperItems($items)
	{
		return array_map(function($item){
			$item = array_map(function($itemValue){
				$question = $itemValue['question'];
				if (isset($question['metas'])) {
					$metas= $question['metas'];
					if (isset($metas['choices'])) {
						$metas= array_values($metas['choices']);
						$itemValue['question']['metas'] = $metas;
					}
				}
				$answer = $question['answer'];
				$itemValue['question']['answer'] = array_map(function($answerValue){
					if (is_array($answerValue)) {
						return implode('|', $answerValue);
					}
					return $answerValue;
				}, $answer);
				return $itemValue;
			}, $item);
			return array_values($item);
		}, $items);
	}
}