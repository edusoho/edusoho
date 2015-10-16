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
            $homework = array();
            return $homework;
        }

        $items = $this->getHomeworkService()->findItemsByHomeworkId($homework['id']);

        $course = $this->getCorrseService()->getCourse($homework['courseId']);
        $homework['courseTitle'] = $course['title'];
        $lesson = $this->getCorrseService()->getLesson($homework['lessonId']);
        $homework['lessonTitle'] = $lesson['title'];
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));
        $homework['items'] = $questions;
        return $this->filter($homework);
    }

    public function filter(&$res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle'));
        $items = $res['items'];
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

            if ('material' == $item['type']) {
                $materialMap[$item['id']] = array();
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

        $res['items'] = array_values($newItmes);
        return $res;
    }

    private function coverDescription($stem)
    {
        $ext = $this;
        $stem = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function($matches) use ($ext) {
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
