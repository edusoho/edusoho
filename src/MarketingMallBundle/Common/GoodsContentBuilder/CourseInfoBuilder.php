<?php


namespace MarketingMallBundle\Common\GoodsContentBuilder;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;

class CourseInfoBuilder extends AbstractBuilder
{
    const COURSE_ALLOWED_KEY = ['course_ids', 'title', 'sub_title', 'cover', 'summary', 'course_catalogue', 'teacher_list'];

    const TASKS_ALLOWED_KEY = ['title', 'type', 'number', 'counts', 'children', 'is_publish', 'activity_type'];

    public function build($id)
    {
        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE);
        }
        return $this->buildCourseData($course);
    }

    protected function buildCourseData($course)
    {
        $childrenCourseIds = [];
        $teachers = [];
        $courseSet = $this->getCourseSetService()->findCourseSetsByCourseIds([$course['id']])[1];
        if ($course['parentId'] == 0) {
            $childrenCourseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        }
        $teacherIds = ArrayToolkit::column($this->getCourseService()->findTeachersByCourseId($course['id']), 'id');
        foreach ($teacherIds as $teacherId) {
            $teachers[] = $this->getTeacherInfoBuilder()->build($teacherId);
        }
        $courseCatalogue = $this->buildCourseCatalogue($this->getCourseService()->findCourseItems($course['id']));
        return [
            'course_ids' => array_merge([$course['id']], $childrenCourseIds),
            'title' => $course['courseSetTitle'],
            'sub_title' => $courseSet['sub_title'],
            'cover' => $courseSet['cover'],
            'summary' => $courseSet['summary'],
            'course_catalogue' => $courseCatalogue,
            'teacher_list' => $teachers
        ];
    }

    protected function buildCourseCatalogue($courseItems)
    {
        $courseCatalogue = [];
        $chapterItems = [];
        $unitItems = [];
        $chapterIndex = -1;
        $unitIndex = -1;
        foreach ($courseItems as &$courseItem) {
            if ($courseItem['type'] == 'chapter') {
                ++$chapterIndex;
                $unitIndex = -1;
                $courseItem['is_publish'] = $courseItem['status'] == 'published' ? 1 : 0;
                $courseItem = ArrayToolkit::parts($courseItem, self::TASKS_ALLOWED_KEY);
                $chapterItems[] = [$chapterIndex => $courseItem];
            }
            if ($courseItem['type'] == 'unit') {
                ++$unitIndex;
                $courseItem['is_publish'] = $courseItem['status'] == 'published' ? 1 : 0;
                $courseItem = ArrayToolkit::parts($courseItem, self::TASKS_ALLOWED_KEY);
                $unitItems[$chapterIndex][] = [$unitIndex => $courseItem];
            }
            if (!empty($courseItem['tasks'])) {
                foreach ($courseItem['tasks'] as $key => &$tasks) {
                    $tasks['type'] = $key == 0 ? 'lesson' : 'tasks';
                    $tasks['activity_type'] = $tasks['activity']['mediaType'];
                    $tasks['is_publish'] = $tasks['status'] == 'published' ? 1 : 0;
                    $tasks = ArrayToolkit::parts($tasks, self::TASKS_ALLOWED_KEY);
                    if ($unitIndex == -1) {
                        $courseCatalogue[] = $tasks;
                    } else {
                        $unitItems[$chapterIndex][$unitIndex]['children'][] = $tasks;
                    }
                }
            }
        }
        foreach ($chapterItems as $key => &$chapter) {
            $tasksNum = 0;
            $lessonNum = 0;
            if (!empty($unitItems[$key])) {
                $chapter['children'] = $unitItems[$key];
                foreach ($chapter['children'] as $unit) {
                    if (!empty($unit['children'])) {
                        $groupedTasks = ArrayToolkit::group($unit['children'], 'type');
                        $tasksNum = empty($groupedTasks['tasks']) ? $tasksNum + 0 : $tasksNum + count($groupedTasks['tasks']);
                        $lessonNum = empty($groupedTasks['lesson']) ? $lessonNum + 0 : $lessonNum + count($groupedTasks['lesson']);
                    }
                }
            }
            $chapter['counts']['unit_num'] = count($chapter['children']);
            $chapter['counts']['tasks_num'] = $tasksNum;
            $chapter['counts']['lesson_num'] = $lessonNum;
            $chapter = ArrayToolkit::parts($chapter, self::TASKS_ALLOWED_KEY);
        }
        return array_merge($courseCatalogue, $chapterItems);
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->biz->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return TeacherInfoBuilder
     */
    protected function getTeacherInfoBuilder()
    {
        return new TeacherInfoBuilder($this->biz);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}