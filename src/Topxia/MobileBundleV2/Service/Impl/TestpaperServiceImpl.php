<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\TestpaperService;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
	public function uploadQuestionImage()
	{
		$user = $this->controller->getUserByToken($this->request);
                        if (!$user->isLogin()) {
                            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
                        }
		$url = "";
		try {
			$file = $this->request->files->get('file');
			$group = $this->getParam("group", 'course');
			$record = $this->getFileService()->uploadFile($group, $file);
			$url = $this->controller->get('topxia.twig.web_extension')->getFilePath($record['uri']);
		} catch (\Exception $e) {
			$url = "";
		}

		$baseUrl = $this->request->getSchemeAndHttpHost();
		$url = empty($url) ? "" : $baseUrl . '/' .$url;
        		return $url;
	}

	public function finishTestpaper()
	{
		$id = $this->getParam("id");
		$testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

	        	if (!empty($testpaperResult) and !in_array($testpaperResult['status'], array('doing', 'paused'))) {
	            	return true;
	        	}

	        	$data = $this->request->request->all();
	            $answers = array_key_exists('data', $data) ? $data['data'] : array();
	            $usedTime = $data['usedTime'];
	            $user = $this->controller->getUserByToken($this->request);

	            //提交变化的答案
	            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

	            //完成试卷，计算得分
	            $testResults = $this->getTestpaperService()->makeTestpaperResultFinish($id);

	            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

	            $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
	            //试卷信息记录
	            $this->getTestpaperService()->finishTest($id, $user['id'], $usedTime);

	            $targets = $this->controller->get('topxia.target_helper')->getTargets(array($testpaper['target']));

	            $course = $this->controller->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

	            if ($this->getTestpaperService()->isExistsEssay($testResults)) {
	            	$userUrl = $this->controller->generateUrl('user_show', array('id'=>$user['id']), true);
                		$teacherCheckUrl = $this->controller->generateUrl('course_manage_test_teacher_check', array('id'=>$testpaperResult['id']), true);

		            foreach ($course['teacherIds'] as $receiverId) {
		                $result = $this->getNotificationService()->notify($receiverId, 'default', "【试卷已完成】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚完成了 {$testpaperResult['paperName']} ，<a href='{$teacherCheckUrl}' target='_blank'>请点击批阅</a>");
		            }
	            }

	            // @todo refactor. , wellming
	            $targets = $this->controller->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));

	            if ($targets[$testpaperResult['target']]['type'] == 'lesson' and !empty($targets[$testpaperResult['target']]['id'])) {
	                $lessons = $this->controller->getCourseService()->findLessonsByIds(array($targets[$testpaperResult['target']]['id']));
	                if (!empty($lessons[$targets[$testpaperResult['target']]['id']])) {
	                    $lesson = $lessons[$targets[$testpaperResult['target']]['id']];
	                    $this->controller->getCourseService()->finishLearnLesson($lesson['courseId'], $lesson['id']);
	                }
	            }

	            return true;
	}

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
            		return array(
            		    'testpaperResult'=>$testpaperResult,
	                            'testpaper'=>$testpaper,
	                            'items'=>$this->getTestpaperItem($testpaperResult)
	                            );
            	}
            	if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            		return array(
            		    'testpaperResult'=>$testpaperResult,
	                            'testpaper'=>$testpaper,
	                            'items'=>$this->getTestpaperItem($testpaperResult)
	                            );
	        	} else {
	            	return $this->createErrorResponse('error', '试卷正在批阅！不能重新考试!');
	        	}
	}

	public function getTestpaperResult()
	{
	        $id = $this->getParam("id");
	        $user = $this->controller->getUserByToken($this->request);
	        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
	        if (!$testpaperResult) {
	            return $this->createErrorResponse('error', '不可以访问其他学生的试卷哦!');
	        }
	        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

	        $targets = $this->controller->get('topxia.target_helper')->getTargets(array($testpaper['target']));
	       
	        if ($testpaperResult['userId'] != $user['id']){
	            $course = $this->controller->getCourseService()->tryManageCourse($targets[$testpaper['target']]['id']);
	        }

	        if (empty($course) and $testpaperResult['userId'] != $user['id']){
	                        return $this->createErrorResponse('error', '不可以访问其他学生的试卷哦!');
	        }

	        $result = $this->getTestpaperService()->showTestpaper($id, true);
	        $items = $result['formatItems'];
	        $accuracy = $result['accuracy'];
	        $student = $this->controller->getUserService()->getUser($testpaperResult['userId']);

	        return array(
	        	"testpaper"=>$testpaper,
	        	"items"=>$this->filterResultItems($items),
	        	"accuracy"=>$accuracy,
            	'paperResult' => $testpaperResult
	        	);
	}

	private function filterResultItems($items)
	{
		$controller = $this;
		$newItems = array_map(function($item){
			return array_values($item);
		}, $items);

		return $this->coverTestpaperItems($newItems);
	}

	private function getTestpaperItem($testpaperResult)
	{
		$result = $this->getTestpaperService()->showTestpaper($testpaperResult['id']);
        		$items = $result['formatItems'];

        		return $this->coverTestpaperItems($items);
	}

	private function coverTestpaperItems($items)
	{
		$controller = $this;
		$result = array_map(function($item) use ($controller){
			$item = array_map(function($itemValue) use ($controller){
				$question = $itemValue['question'];
				if (isset($question['isDeleted']) && $question['isDeleted'] == true) {
					return null;
				}
				if (isset($itemValue['items'])) {
					$filterItems = array_values($itemValue['items']);
					$itemValue['items'] = array_map(function($filterItem)use ($controller){
						return $controller->filterMetas($filterItem);
					}, $filterItems);
				}

				$itemValue = $controller->filterMetas($itemValue);
				return $itemValue;
				
			}, $item);
			if ($controller->arrayIsEmpty($item)) {
				return;
			}
			return array_values($item);
		}, $items);
		foreach ($result as $key => $value) {
			if (empty($value)) {
				unset($result[$key]);
			}
		}
		return $result;
	}

	public function arrayIsEmpty($array)
	{
		foreach ($array as $key => $value) {
			if (!empty($value)) {
				return false;
			}
		}

		return true;
	}

	public function filterMetas($itemValue)
	{
		$question = $itemValue['question'];
		if (isset($question['metas'])) {
			$metas= $question['metas'];
			if (isset($metas['choices'])) {
				$metas= array_values($metas['choices']);
				$itemValue['question']['metas'] = $metas;
			}
		}

		$answer = $question['answer'];
		if (is_array($answer)) {
			$itemValue['question']['answer'] = array_map(function($answerValue){
				if (is_array($answerValue)) {
					return implode('|', $answerValue);
				}
				return $answerValue;
			}, $answer);
			return $itemValue;
		}

		return $itemValue;
	}
}