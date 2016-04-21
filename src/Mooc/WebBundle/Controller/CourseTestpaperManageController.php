<?php
namespace Mooc\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseTestpaperManageController as BaseCourseTestpaperManageController;

class CourseTestpaperManageController extends BaseCourseTestpaperManageController
{
    public function itemsAction(Request $request, $courseId, $testpaperId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException('试卷不存在');
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            if (empty($data['questionId']) || empty($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }

            if (count($data['questionId']) != count($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目数据不正确');
            }

            $data['questionId'] = array_values($data['questionId']);
            $data['scores']     = array_values($data['scores']);

            $items = array();

            foreach ($data['questionId'] as $index => $questionId) {
                $items[] = array('questionId' => $questionId, 'score' => $data['scores'][$index]);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);

            if (isset($data['passedScore'])) {
                $this->getTestpaperService()->updateTestpaper($testpaperId, array('passedScore' => $data['passedScore']));
            }

            $this->setFlashMessage('success', '试卷题目保存成功！');
            return $this->redirect($this->generateUrl('course_manage_testpaper', array('courseId' => $courseId)));
        }

        $items     = $this->getTestpaperService()->getTestpaperItems($testpaper['id']);
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        $subItems   = array();
        $hasEssay   = false;
        $scoreTotal = 0;

        foreach ($items as $key => $item) {
            if ($item['questionType'] == 'essay' || $item['questionType'] == 'fill') {
                $hasEssay = true;
            }

            $scoreTotal = $scoreTotal + $item['score'];

            if ($item['parentId'] > 0) {
                $subItems[$item['parentId']][] = $item;
                unset($items[$key]);
            }
        }

        $passedScoreDefault = ceil($scoreTotal * 0.6);
        return $this->render('TopxiaWebBundle:CourseTestpaperManage:items.html.twig', array(
            'course'             => $course,
            'testpaper'          => $testpaper,
            'items'              => ArrayToolkit::group($items, 'questionType'),
            'subItems'           => $subItems,
            'questions'          => $questions,
            'targets'            => $targets,
            'hasEssay'           => $hasEssay,
            'passedScoreDefault' => $passedScoreDefault
        ));
    }
}
