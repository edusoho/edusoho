<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseCopyService;
use Topxia\Common\StringToolkit;
use Topxia\Service\Util\LiveClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CourseCopyServiceImpl extends BaseService implements CourseCopyService
{
    public function copyTeachers($courseId, $newCourse)
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

    public function copyTestpapers($courseId, $newCourse, $newQuestions)
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
                    'score' => $item['score'],
                    'missScore' => $item['missScore'],
                );

                $this->getTestpaperItemDao()->addItem($fields);
            }

        }

        return $map;
    }

    public function convertTestpaperLesson($newLessons, $newTestpapers)
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

    public function copyQuestions($courseId, $newCourse, $newLessons)
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

    public function copyLessons($courseId, $newCourse, $chapters)
    {
        $lessons = $this->getLessonDao()->findLessonsByCourseId($courseId);
        $map = array();

        foreach ($lessons as $lesson) {

            $fields = ArrayToolkit::parts($lesson, array('number', 'seq', 'free', 'status', 'title', 'summary', 'tags', 'type', 'content', 'giveCredit', 'requireCredit', 'mediaId', 'mediaSource', 'mediaName', 'mediaUri', 'length', 'materialNum','startTime', 'endTime', 'replayStatus', 'liveProvider', 'userId'));
            $fields['courseId'] = $newCourse['id'];
            if ($lesson['chapterId']) {
                $fields['chapterId'] = $chapters[$lesson['chapterId']]['id'];
            } else {
                $fields['chapterId'] = 0;
            }

            $fields['createdTime'] = time();

            $copiedLesson = $this->getLessonDao()->addLesson($fields);
            $map[$lesson['id']] = $copiedLesson;
            if(array_key_exists("mediaId", $copiedLesson) && $copiedLesson["mediaId"]>0 && in_array($copiedLesson["type"], array('video', 'audio','ppt'))) {
                $this->getUploadFileDao()->updateFileUsedCount(array($copiedLesson["mediaId"]), 1);
            }
        }

        return $map;
    }

    public function copyChapters($courseId, $newCourse)
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

    public function copyCourse($course)
    {
        $fields = ArrayToolkit::parts($course, array('title', 'subtitle', 'type', 'maxStudentNum', 'price','coinPrice', 'expiryDay', 'serializeMode', 'lessonNum', 'giveCredit', 'vipLevelId', 'categoryId', 'tags', 'smallPicture', 'middlePicture', 'largePicture', 'about', 'teacherIds', 'goals', 'audiences', 'userId'));
        $fields['status'] = 'draft';
        $fields['createdTime'] = time();

        return $this->getCourseDao()->addCourse(CourseSerialize::serialize($fields));
    }

    public function copyMaterials($courseId, $newCourse, $newLessons)
    {
        $count = $this->getMaterialDao()->getMaterialCountByCourseId($courseId);
        $materials = $this->getMaterialDao()->findMaterialsByCourseId($courseId, 0, $count);
        
        $map = array();

        foreach ($materials as $material) {

            $fields = ArrayToolkit::parts($material, array('title','description','link','fileId','fileUri','fileMime','fileSize','userId'));

            $fields['courseId'] = $newCourse['id'];
            if ($material['lessonId']) {
                $fields['lessonId'] = $newLessons[$material['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();

            $copiedMaterial = $this->getMaterialDao()->addMaterial($fields);
            $map[$material['id']] = $copiedMaterial;

            if(array_key_exists("fileId", $copiedMaterial) && $copiedMaterial["fileId"]>0 ) {
                $this->getUploadFileDao()->updateFileUsedCount(array($copiedMaterial["fileId"]), 1);
            }
        }

        return $map;
    }

    public function copyHomeworks($courseId, $newCourse, $newLessons,$newQuestions)
    {
        $homeworks = $this->getHomeworkDao()->findHomeworksByCourseId($courseId);
        
        $map = array();
        foreach ($homeworks as $homework) {

            $fields = ArrayToolkit::parts($homework, array('description','itemCount','createdUserId','updatedUserId'));

            $fields['courseId'] = $newCourse['id'];
            if ($homework['lessonId']) {
                $fields['lessonId'] = $newLessons[$homework['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();
            $fields['updatedTime'] = time();
            $newHomework = $this->getHomeworkDao()->addHomework($fields);
            $map[$homework['id']] =  $newHomework;

            $items = $this->getHomeworkItemDao()->findItemsByHomeworkId($homework['id']);
            foreach ($items as $item) {
                $fields = array(
                    'homeworkId' => $newHomework['id'],
                    'seq' => $item['seq'],
                    'questionId' => empty($newQuestions[$item['questionId']]['id']) ? 0 : $newQuestions[$item['questionId']]['id'],
                    'score' => $item['score'],
                    'missScore' => $item['missScore'],
                    'parentId' => empty($newQuestions[$item['parentId']]['id']) ? 0 : $newQuestions[$item['parentId']]['id'],
                );

                $this->getHomeworkItemDao()->addItem($fields);
            }

        }

        return $map;
    }

     public function copyExercises($courseId, $newCourse, $newLessons)
    {
        $exercises = $this->getExerciseDao()->findExercisesByCourseId($courseId);
        
        $map = array();

        foreach ($exercises as $exercise) {

            $fields = ArrayToolkit::parts($exercise, array('itemCount','source','difficulty','questionTypeRange','createdUserId'));

            $fields['courseId'] = $newCourse['id'];
            if ($exercise['lessonId']) {
                $fields['lessonId'] = $newLessons[$exercise['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();

            $map[$exercise['id']] = $this->getExerciseDao()->addExercise($fields);
        }

        return $map;
    }

    public function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    public function getCourseMemberDao()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    public function getTestpaperItemDao()
    {
        return $this->createDao('Testpaper.TestpaperItemDao');
    }

    public function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    public function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    public function getCourseChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    public function getLessonDao()
    {
        return $this->createDao('Course.LessonDao');
    }

    public function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

     private function getMaterialDao()
    {
        return $this->createDao('Course.CourseMaterialDao');
    }

    private function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    private function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    private function getExerciseDao()
    {
        return $this->createDao('Homework:Homework.ExerciseDao');
    }

    public function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}
