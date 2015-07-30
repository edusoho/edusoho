<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\CourseLessonManageController as BaseCourseLessonManageController;

class CourseLessonManageController extends BaseCourseLessonManageController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$courseItems = $this->getCourseService()->getCourseItems($course['id']);

		$lessonIds = ArrayToolkit::column($courseItems, 'id');

		if ($this->isPluginInstalled('Homework')) {
			$exercises = $this->getServiceKernel()->createService('Homework:Homework.ExerciseService')->findExercisesByLessonIds($lessonIds);
			$homeworks = $this->getServiceKernel()->createService('Homework:Homework.HomeworkService')->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
		}

		$mediaMap = array();
		foreach ($courseItems as $item) {
			if ($item['itemType'] != 'lesson') {
				continue;
			}

			if (empty($item['mediaId'])) {
				continue;
			}

			if (empty($mediaMap[$item['mediaId']])) {
				$mediaMap[$item['mediaId']] = array();
			}
			$mediaMap[$item['mediaId']][] = $item['id'];
		}

		$mediaIds = array_keys($mediaMap);

		$files = $this->getUploadFileService()->findFilesByIds($mediaIds);

		foreach ($files as $file) {
			$lessonIds = $mediaMap[$file['id']];
			foreach ($lessonIds as $lessonId) {
				$courseItems["lesson-{$lessonId}"]['mediaStatus'] = $file['convertStatus'];
			}
		}
		$default = $this->getSettingService()->get('default', array());
		return $this->render('CustomWebBundle:CourseLessonManage:index.html.twig', array(
			'course' => $course,
			'items' => $courseItems,
			'exercises' => empty($exercises) ? array() : $exercises,
			'homeworks' => empty($homeworks) ? array() : $homeworks,
			'files' => ArrayToolkit::index($files,'id'),
			'default'=> $default
		));
	}
}
