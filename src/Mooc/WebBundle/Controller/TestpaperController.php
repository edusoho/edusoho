<?php
namespace Mooc\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\TestpaperController as BaseTestpaperController;

class TestpaperController extends BaseTestpaperController
{
    public function teacherCheckAction(Request $request, $id)
    {
        //身份校验?

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createNotFoundException("试卷不存在");
        }

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])) {
            throw $this->createAccessDeniedException('无权批阅试卷！');
        }

        if ($request->getMethod() == 'POST') {
            $form = $request->request->all();

            $testpaperResult = $this->getTestpaperService()->makeTeacherFinishTest($id, $testpaper['id'], $teacherId, $form);

            $user = $this->getCurrentUser();

            $message = array(
                'id'       => $testpaperResult['id'],
                'name'     => $testpaperResult['paperName'],
                'userId'   => $user['id'],
                'userName' => $user['nickname'],
                'type'     => 'read'
            );

            $result = $this->getNotificationService()->notify($testpaperResult['userId'], 'test-paper', $message);

            return $this->createJsonResponse(true);
        }

        $result   = $this->getTestpaperService()->showTestpaper($id, true);
        $items    = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = $this->makeTestpaperTotal($testpaper, $items);

        $types = array();

        if (in_array('essay', $testpaper['metas']['question_type_seq'])) {
            array_push($types, 'essay');
        }

        if (in_array('fill', $testpaper['metas']['question_type_seq'])) {
            array_push($types, 'fill');
        }

        if (in_array('material', $testpaper['metas']['question_type_seq'])) {
            foreach ($items['material'] as $key => $item) {
                $questionTypes = ArrayToolkit::index(empty($item['items']) ? array() : $item['items'], 'questionType');

                if (array_key_exists('essay', $questionTypes) || array_key_exists('fill', $questionTypes)) {
                    if (!in_array('material', $types)) {
                        array_push($types, 'material');
                    }
                }
            }
        }

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        $questionsSetting = $this->getSettingService()->get('questions', array());

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-review.html.twig', array(
            'items'            => $items,
            'accuracy'         => $accuracy,
            'paper'            => $testpaper,
            'paperResult'      => $testpaperResult,
            'id'               => $id,
            'total'            => $total,
            'types'            => $types,
            'student'          => $student,
            'questionsSetting' => $questionsSetting,
            'source'           => $request->query->get('source', 'course'),
            'targetId'         => $request->query->get('targetId', 0)
        ));
    }
}
