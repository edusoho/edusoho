<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

class CourseCopyCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:course-copy' )
            ->addArgument('courseId', InputArgument::REQUIRED, 'courseId');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $courseId = $input->getArgument('courseId');

        $course = $this->getCourseDao()->getCourse($courseId);

        $newCourse = $this->copyCourse($course);

        $newTeachers = $this->copyTeachers($courseId, $newCourse);

        $newChapters = $this->copyChapters($courseId, $newCourse);

        $newLessons = $this->copyLessons($courseId, $newCourse, $newChapters);

        $newQuestions = $this->copyQuestions($courseId, $newCourse, $newLessons);

        $newTestpapers = $this->copyTestpapers($courseId, $newCourse, $newQuestions);

        $this->convertTestpaperLesson($newLessons, $newTestpapers);

        $output->writeln("New course id: {$newCourse['id']}");
    }

    protected function copyTeachers($courseId, $newCourse)
    {
        $count = $this->getCourseMemberDao()->findMemberCountByCourseIdAndRole($courseId, 'teacher');
        $members = $this->getCourseMemberDao()->findMembersByCourseIdAndRole($courseId, 'teacher', 0, $count);
        foreach ($members as $member) {
            $fields = array(
                'courseId' => $newCourse['id'],
                'userId' => $member['userId'],
                'isVisible' =>  $member['isVisible'],
                'role' => $member['role'],
                'createdTime' => time(),
            );
            $this->getCourseMemberDao()->addMember($fields);
        }

    }

    protected function copyTestpapers($courseId, $newCourse, $newQuestions)
    {
        $testpapers = $this->getTestpaperDao()->searchTestpapers(array('target' => "course-{$courseId}"), array('createdTime', 'DESC'), 0, 100000);

        $map = array();
        foreach ($testpapers as $testpaper) {
            $fields = $testpaper;
            $fields['target'] = "course-{$newCourse['id']}";
            $fields['createdTime'] = time();
            $fields['updatedTime'] = time();
            unset($fields['id']);
            $newTestpaper = $this->getTestpaperDao()->addTestpaper($fields);
            $map[$testpaper['id']] = $newTestpaper;

            $items = $this->getTestpaperItemDao()->findItemsByTestPaperId($testpaper['id']);
            foreach ($items as $item) {
                $fields = array(
                    'testId' => $newTestpaper['id'],
                    'seq' => $item['seq'],
                    'questionId' => empty($newQuestions[$item['questionId']]['id']) ? 0 : $newQuestions[$item['questionId']]['id'],
                    'questionType' => $item['questionType'],
                    'parentId' => empty($newQuestions[$item['parentId']]['id']) ? 0 : $newQuestions[$item['parentId']]['id'],
                    'score' => 0,
                    'missScore' => 0,
                );

                $this->getTestpaperItemDao()->addItem($fields);
            }

        }

        return $map;
    }

    protected function convertTestpaperLesson($newLessons, $newTestpapers)
    {
        foreach ($newLessons as $lesson) {
            if ($lesson['type'] != 'testpaper') {
                continue;
            }

            $fields = array(
                'mediaId' => empty($newTestpapers[$lesson['mediaId']]['id']) ? 0 : $newTestpapers[$lesson['mediaId']]['id'],
            );

            $this->getLessonDao()->updateLesson($lesson['id'], $fields);
        }
    }

    protected function copyQuestions($courseId, $newCourse, $newLessons)
    {
        $conditions = array('targetPrefix' => "course-{$courseId}", 'parentId' => 0);
        $count = $this->getQuestionDao()->searchQuestionsCount($conditions);
        $questions = $this->getQuestionDao()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $count);

        $map = array();
        foreach ($questions as $question) {
            $oldQuestionId = $question['id'];
            $fields = ArrayToolkit::parts($question, array('type', 'stem', 'score', 'answer', 'analysis', 'metas', 'categoryId', 'difficulty', 'parentId', 'subCount', 'userId'));
            if (strpos($question['target'], 'lesson') > 0) {
                $pos = strrpos($question['target'], '-');
                $oldLessonId = substr($question['target'], $pos+1);
                $fields['target'] = "course-{$newCourse['id']}/lesson-" . $newLessons[$oldLessonId]['id'];
            } else {
                $fields['target'] = "course-{$newCourse['id']}";
            }

            $fields['updatedTime'] = time();
            $fields['createdTime'] = time();

            $question = $this->getQuestionDao()->addQuestion($fields);

            $map[$oldQuestionId] = $question;

            if ($question['subCount'] > 0) {
                $subQuestions = $this->getQuestionDao()->findQuestionsByParentId($oldQuestionId);
                foreach ($subQuestions as $subQuestion) {
                    $fields = ArrayToolkit::parts($subQuestion, array('type', 'stem', 'score', 'answer', 'analysis', 'metas', 'categoryId', 'difficulty', 'subCount', 'userId'));
                    $fields['parentId'] = $question['id'];
                    $fields['updatedTime'] = time();
                    $fields['createdTime'] = time();
                    if (strpos($subQuestion['target'], 'lesson') > 0) {
                        $pos = strrpos($subQuestion['target'], '-');
                        $oldLessonId = substr($subQuestion['target'], $pos+1);
                        $fields['target'] = "course-{$newCourse['id']}/lesson-" . $newLessons[$oldLessonId]['id'];
                    } else {
                        $fields['target'] = "course-{$newCourse['id']}";
                    }

                    $map[$subQuestion['id']] = $this->getQuestionDao()->addQuestion($fields);
                }

            }

        }

        return $map;
    }

    protected function copyLessons($courseId, $newCourse, $chapters)
    {
        $lessons = $this->getLessonDao()->findLessonsByCourseId($courseId);
        $map = array();

        foreach ($lessons as $lesson) {

            $fields = ArrayToolkit::parts($lesson, array('number', 'seq', 'free', 'status', 'title', 'summary', 'tags', 'type', 'content', 'giveCredit', 'requireCredit', 'mediaId', 'mediaSource', 'mediaName', 'mediaUri', 'length', 'startTime', 'endTime', 'replayStatus', 'liveProvider', 'userId'));
            $fields['courseId'] = $newCourse['id'];
            if ($lesson['chapterId']) {
                $fields['chapterId'] = $chapters[$lesson['chapterId']]['id'];
            } else {
                $fields['chapterId'] = 0;
            }

            $fields['createdTime'] = time();

            $map[$lesson['id']] = $this->getLessonDao()->addLesson($fields);
        }

        return $map;
    }

    protected function copyChapters($courseId, $newCourse)
    {
        $chapters = $this->getCourseChapterDao()->findChaptersByCourseId($courseId);

        $map = array();

        foreach ($chapters as $chapter) {
            if (!empty($chapter['parentId'])) {
                continue;
            }

            $orgChapterId = $chapter['id'];

            $chapter['courseId'] = $newCourse['id'];
            $chapter['createdTime'] = time();
            unset($chapter['id']);

            $map[$orgChapterId] = $this->getCourseChapterDao()->addChapter($chapter);
        }

        foreach ($chapters as $chapter) {
            if (empty($chapter['parentId'])) {
                continue;
            }

            $orgChapterId = $chapter['id'];

            $chapter['courseId'] = $newCourse['id'];
            $chapter['parentId'] = $map[$chapter['parentId']]['id'];
            $chapter['createdTime'] = time();
            unset($chapter['id']);
            $map[$orgChapterId] = $this->getCourseChapterDao()->addChapter($chapter);
        }

        return $map;
    }

    private function copyCourse($course)
    {
        $fields = ArrayToolkit::parts($course, array('title', 'subtitle', 'type', 'maxStudentNum', 'price', 'expiryDay', 'showStudentNumType', 'serializeMode', 'lessonNum', 'giveCredit', 'vipLevelId', 'categoryId', 'tags', 'smallPicture', 'middlePicture', 'largePicture', 'about', 'teacherIds', 'goals', 'audiences', 'userId', 'deadlineNotify', 'daysOfNotifyBeforeDeadline'));

        $fields['status'] = 'draft';
        $fields['createdTime'] = time();

        return $this->getCourseDao()->addCourse($fields);
    }

    protected function getCourseMemberDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMemberDao');
    }

    protected function getTestpaperItemDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperDao');
    }

    protected function getQuestionDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionDao');
    }

    protected function getUploadFileDao()
    {
        return $this->getServiceKernel()->createDao('File.UploadFileDao');
    }

    protected function getCourseChapterDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseChapterDao');
    }

    protected function getLessonDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonDao');
    }

    protected function getCourseDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDao');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);

    }

}