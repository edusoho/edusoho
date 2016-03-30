<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Homework extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $idType = $request->query->get('_idType');
        if ('lesson' == $idType) {
            $homework = $this->getHomeworkService()->getHomeworkByLessonId($id);
        } else {
            $homework = $this->getHomeworkService()->getHomework($id);
        }

        if (empty($homework)) {
            return $this->error('404', '该作业不存在!');
        }

        $course = $this->getCorrseService()->getCourse($homework['courseId']);
        $homework['courseTitle'] = $course['title'];
        $lesson = $this->getCorrseService()->getLesson($homework['lessonId']);
        $homework['lessonTitle'] = $lesson['title'];

        if ('lesson' != $idType) {
            $items = $this->getHomeworkService()->findItemsByHomeworkId($homework['id']);
            $indexdItems = ArrayToolkit::index($items, 'questionId');
            $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));
            $homework['items'] = $this->filterItem($questions, null);
        }

        return $this->filter($homework);
    }

    public function result(Application $app, Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $homeworkResult = $this->getHomeworkService()->getResult($id);

        $homework = $this->getHomeworkService()->getHomework($homeworkResult['homeworkId']);

        if (empty($homework)) {
            return $this->error('500', '作业不存在！');
        }

        if (empty($currentUser) || (!$canCheckHomework && $homeworkResult['userId'] != $currentUser['id'])) {
            return $this->error('500', '不能查看该作业结果');
        }

        if ($homeworkResult['status'] != 'finished') {
            return $this->error('500', '作业还未批阅');
        }

        $course = $this->getCorrseService()->getCourse($homework['courseId']);
        $homework['courseTitle'] = $course['title'];
        $lesson = $this->getCorrseService()->getLesson($homework['lessonId']);
        if (empty($lesson)) {
            return $this->error('500', '作业所属课时不存在！');
        }

        $homework['lessonTitle'] = $lesson['title'];

        $items = $this->getHomeworkService()->findItemsByHomeworkId($homework['id']);
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));

        $itemSetResults = $this->getHomeworkService()->findItemResultsbyHomeworkResultId($homeworkResult['id']);
        $itemSetResults = ArrayToolkit::index($itemSetResults, 'questionId');
        $homework['items'] = $this->filterItem($questions, $itemSetResults);

        return $this->filter($homework);
    }

    private function filterItem($items, $itemSetResults)
    {
        $newItmes = array();
        $materialMap = array();
        foreach ($items as $item) {
            $item = ArrayToolkit::parts($item, array('id', 'type', 'stem', 'answer', 'analysis', 'metas', 'difficulty', 'parentId'));
            if (empty($item['metas'])) {
                $item['metas'] = array();
            }
            if (isset($item['metas']['choices'])) {
                $metas = array_values($item['metas']['choices']);
                $item['metas'] = $metas;
            }

            $item['answer'] = $this->filterAnswer($item, $itemSetResults);

            if ('material' == $item['type']) {
                $materialMap[$item['id']] = array();
            }

            if ($itemSetResults) {
                $item['result'] = $itemSetResults[$item['id']];
            }

            $item['stem'] = $this->coverDescription($item['stem']);
            if ($item['parentId'] != 0 && isset($materialMap[$item['parentId']])) {
                $materialMap[$item['parentId']][] = $item;
                continue;
            }

            $item['items'] = array();
            $newItmes[$item['id']] = $item;
        }

        foreach ($materialMap as $id => $material) {
            $newItmes[$id]['items'] = $material;
        }

        return array_values($newItmes);
    }

    public function filter($res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle'));

        return $res;
    }

    private function filterAnswer($item, $itemSetResults)
    {
        if (empty($itemSetResults)) {
            if ('fill' == $item['type']) {
                return array_map(function ($answer) {
                    return '';
                }, $item['answer']);
            }

            return;
        }

        return $this->coverAnswer($item['answer']);
    }

    private function coverAnswer($answer)
    {
        if (is_array($answer)) {
            $answer = array_map(function ($answerValue) {
                if (is_array($answerValue)) {
                    return implode('|', $answerValue);
                }

                return $answerValue;
            }, $answer);

            return $answer;
        }

        return array();
    }

    private function coverDescription($stem)
    {
        $ext = $this;
        $stem = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function ($matches) use ($ext) {
            $url = $ext->getFileUrl($matches[1]);

            return "<img src='{$url}' />";
        }, $stem);

        return $stem;
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCorrseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
