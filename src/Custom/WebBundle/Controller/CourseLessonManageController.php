<?php 

namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Util\CCVideoClientFactory;

class CourseLessonManageController extends BaseController
{
    public function createAction(Request $request,$id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $categoryId = $course['subjectIds'][0];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            throw $this->createNotFoundException("科目(#{$categoryId})不存在，创建课时失败！");
        }

        if ($request->getMethod() == "POST") {

            $formData = $request->request->all();

            if (!empty($formData['tab'])) {
                $courseware = $formData;
                $videoMeta = $this->getVideoMeta($courseware['url']);
                $courseware = $this->filterVideoField($videoMeta,$courseware);
                $courseware['categoryId'] = $categoryId;
                $courseware = $this->getCoursewareService()->createCourseware($courseware);
                $formData['coursewareId'] = $courseware['id'];
            }

            $lesson = $this->getCourseService()->createLesson($formData);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson,
            ));
        }

        return $this->render('CustomWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
            'course' => $course,
            'category' => $category
        ));
    }

    public function editAction(Request $request,$courseId,$lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $categoryId = $course['subjectIds'][0];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            throw $this->createNotFoundException("科目(#{$categoryId})不存在，编辑课时失败！");
        }

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        $courseware = $this->getCoursewareService()->getCourseware($lesson['coursewareId']);

        if($request->getMethod() == 'POST'){

            $fields = $request->request->all();

            if (!empty($fields['tab'])) {
                $courseware = $fields;
                $videoMeta = $this->getVideoMeta($courseware['url']);
                $courseware = $this->filterVideoField($videoMeta,$courseware);
                $courseware['categoryId'] = $categoryId;
                $courseware = $this->getCoursewareService()->createCourseware($courseware);
                $fields['coursewareId'] = $courseware['id'];
            }

            $lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson
            ));
        }

        return $this->render('CustomWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
            'course' => $course,
            'category' => $category,
            'lesson' => $lesson,
            'courseware' => $courseware
        ));
    }

    private function filterVideoField($videoMeta,$courseware)
    {
        $courseware['title'] = $videoMeta['title'];
        $courseware['image'] = $videoMeta['image'];
        $courseware['knowledgeIds'] = $courseware['mainKnowledgeId'];
        if (!empty($courseware['relatedKnowledgeIds'])){
            $courseware['knowledgeIds'] = $courseware['relatedKnowledgeIds'].",".$courseware['mainKnowledgeId'];
            $courseware['relatedKnowledgeIds'] = array_filter(explode(',', $courseware['relatedKnowledgeIds']));
        }
        $courseware['knowledgeIds'] = array_filter(explode(',', $courseware['knowledgeIds']));
        $courseware['tagIds'] = array_filter(explode(',', $courseware['tagIds']));
        return $courseware;
    }

    private function getVideoMeta($videoUrl)
    {
        $factory = new CCVideoClientFactory();
        $client = $factory->createClient();
        $userIdAndVideoId = $this->getUserIdAndVideoId($videoUrl);
        $videoInfo = $client->getVideoInfo($userIdAndVideoId['userId'],$userIdAndVideoId['videoId']);
        $videoInfo = json_decode($videoInfo);
        return array(
            'title' => $videoInfo->video->title,
            'image' => $videoInfo->video->image,
            'duration' => $videoInfo->video->duration
        );
    }

    private function getUserIdAndVideoId($url)
    {
        $query = parse_url($url);
        $querys = $this->convertUrlQuery($query['query']);
        $queryArr = explode('_', $querys['videoID']);
        return array(
            'userId' => $queryArr[0],
            'videoId' => $queryArr[1]
        );
    }

    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    private function getCoursewareService()
    {
        return $this->getServiceKernel()->createService('Courseware.CoursewareService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}