<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Taxonomy\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $course = $this->getCourseService()->getCourse($courseSet['defaultCourseId']);
        $previewAs = $request->query->get('previewAs');
        if (empty($course)) {
            throw $this->createNotFoundException('No Avaliable Course in CourseSet#{$id}');
        }

        $this->getCourseSetService()->hitCourseSet($id);

        return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'], 'previewAs' => $previewAs)));
    }

    public function courseSetsBlockAction(array $courseSets, $view = 'list', $mode = 'default')
    {
        $userIds = array();

        $service = $this->getCourseService();

        $courseSets = array_map(function ($set) use (&$userIds, $service) {
            $set['course'] = $service->getFirstPublishedCourseByCourseSetId($set['id']);
            if (!empty($set['course']['teacherIds']) && is_array($set['course']['teacherIds'])) {
                $userIds = array_merge($userIds, $set['course']['teacherIds']);
            }

            return $set;
        }, $courseSets);

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("course-set/block/course-block-{$view}.html.twig", array(
            'courseSets' => $courseSets,
            'users' => $users,
            'mode' => $mode,
        ));
    }

    public function favoriteAction($id)
    {
        $success = $this->getCourseSetService()->favorite($id);

        return $this->createJsonResponse($success);
    }

    public function unfavoriteAction($id)
    {
        $success = $this->getCourseSetService()->unfavorite($id);

        return $this->createJsonResponse($success);
    }

    public function archiveAction()
    {
        $conditions = array(
            'status' => 'published',
            'parentId' => '0',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            30
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();

        foreach ($courseSets as &$courseSet) {
            $tagIds = $this->getTagIdsByCourseSet($courseSet);
            $courseSet['tags'] = $this->getTagService()->findTagsByIds($tagIds);
            $userIds = array_merge($userIds, array($courseSet['creator']));
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('course-set/archive/index.html.twig', array(
            'courseSets' => $courseSets,
            'paginator' => $paginator,
            'users' => $users,
        ));
    }

    public function archiveDetailAction($courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->getFirstPublishedCourseByCourseSetId($courseSet['id']);

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        $tagIds = $this->getTagIdsByCourseSet($courseSet);
        $tags = $this->getTagService()->findTagsByIds($tagIds);
        $category = $this->getCategoryService()->getCategory($courseSet['categoryId']);
        if (!$course) {
            $courseDescription = array();
        } else {
            $courseDescription = $course['about'];
            $courseDescription = strip_tags($courseDescription, '');
            $courseDescription = preg_replace('/ /', '', $courseDescription);
            $courseDescription = substr($courseDescription, 0, 100);
        }

        return $this->render('course-set/archive/course.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'tasks' => $tasks,
            'tags' => $tags,
            'category' => $category,
            'courseDescription' => $courseDescription,
        ));
    }

    public function archiveTaskAction($courseSetId, $taskId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->getFirstPublishedCourseByCourseSetId($courseSet['id']);

        $tagIds = $this->getTagIdsByCourseSet($courseSet);
        $tags = $this->getTagService()->findTagsByIds($tagIds);

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        if ($taskId == '' && $tasks != null) {
            $currentTask = current($tasks);
        } else {
            $currentTask = $this->getTaskService()->getTask($taskId);
        }

        return $this->render('course-set/archive/task.html.twig', array(
            'course' => $course,
            'courseSet' => $courseSet,
            'tasks' => $tasks,
            'currentTask' => $currentTask,
            'tags' => $tags,
        ));
    }

    protected function getTagIdsByCourseSet(array $courseSet)
    {
        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course-set', 'ownerId' => $courseSet['id']));

        return ArrayToolkit::column($tags, 'id');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
