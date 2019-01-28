<?php

namespace AppBundle\Twig;

use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestpaperExtension extends \Twig_Extension
{
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parse_exercise_range', array($this, 'parseRange')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('find_course_set_testpapers', array($this, 'findTestpapersByCourseSetId')),
            new \Twig_SimpleFunction('get_features', array($this, 'getFeatures')),
            new \Twig_SimpleFunction('show_answers', array($this, 'canShowAnswers')),
        );
    }

    public function parseRange($range)
    {
        $rangeDefault = array('courseId' => 0);
        $range = empty($range) ? $rangeDefault : $range;

        if (is_array($range)) {
            return $range;
        } elseif ('course' == $range) {
            return $rangeDefault;
        } elseif ('lesson' == $range) {
            //兼容老数据
            $conditions = array(
                'activityId' => $activity['id'],
                'type' => 'exercise',
                'courseId' => $activity['fromCourseId'],
            );
            $task = $this->getCourseTaskService()->searchTasks($conditions, null, 0, 1);

            if (!$task) {
                return $rangeDefault;
            }

            $conditions = array(
                'categoryId' => $task[0]['categoryId'],
                'mode' => 'lesson',
            );
            $lessonTask = $this->getCourseTaskService()->searchTasks($conditions, null, 0, 1);
            if ($lessonTask) {
                return array('courseId' => $lessonTask[0]['courseId'], 'lessonId' => $lessonTask[0]['id']);
            }

            return $rangeDefault;
        }

        return $rangeDefault;
    }

    public function findTestpapersByCourseSetId($id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $conditions = array(
            'courseSetId' => $id,
            'status' => 'open',
            'type' => 'testpaper',
        );

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    public function getFeatures()
    {
        return $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();
    }

    public function canShowAnswers($testpaperResult)
    {
        if (empty($testpaperResult)) {
            return false;
        }

        if (!in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return false;
        }

        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);
        if (!$canLookTestpaper) {
            return false;
        }

        $user = $this->biz['user'];
        if ($user->isAdmin()) {
            return true;
        }

        $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($testpaperResult['courseId'], $user['id']);
        if ($isCourseTeacher) {
            return true;
        }

        $answersShowMode = $this->getSettingService()->node('questions.testpaper_answers_show_mode', 'submitted');
        if ('submitted' == $answersShowMode) {
            return true;
        }

        if ('reviewed' == $answersShowMode && 'finished' == $testpaperResult['status']) {
            return true;
        }

        if ('hide' == $answersShowMode && 'testpaper' != $testpaperResult['type']) {
            return true;
        }

        return false;
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getName()
    {
        return 'web_testpaper_twig';
    }
}
