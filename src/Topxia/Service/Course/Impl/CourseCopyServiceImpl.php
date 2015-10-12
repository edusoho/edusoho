<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseCopyService;

class CourseCopyServiceImpl extends BaseService implements CourseCopyService
{
    public function copy($course, $link = false)
    {
        $this->getCourseDao()->getConnection()->beginTransaction();
        try {
            $newCourse = $this->copyCourse($course, $link);

            $newTeachers = $this->copyTeachers($course['id'], $newCourse);

            $newChapters = $this->copyChapters($course['id'], $newCourse);

            $newLessons = $this->copyLessons($course['id'], $newCourse, $newChapters);

            $newQuestions = $this->copyQuestions($course['id'], $newCourse, $newLessons);

            $newTestpapers = $this->copyTestpapers($course['id'], $newCourse, $newQuestions);

            $this->convertTestpaperLesson($newLessons, $newTestpapers);

            $newMaterials = $this->copyMaterials($course['id'], $newCourse, $newLessons);

            $code = 'Homework';
            $homework = $this->getAppService()->findInstallApp($code);
            $isCopyHomework = $homework && version_compare($homework['version'], "1.0.4", ">=");

            if ($isCopyHomework) {
                $newHomeworks = $this->copyHomeworks($course['id'], $newCourse, $newLessons, $newQuestions);
                $newExercises = $this->copyExercises($course['id'], $newCourse, $newLessons);
            }

            $this->getCourseDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getCourseDao()->getConnection()->rollback();
            throw $e;
        }
        
        return $newCourse;
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
            $fields['pId'] = $testpaper['id'];
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
                    'pId'=>$item['id']
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
            $fields = ArrayToolkit::parts($question, array('type', 'stem', 'score', 'answer', 'analysis', 'metas', 'categoryId', 'difficulty', 'parentId', 'subCount', 'userId','pId'));
            if (strpos($question['target'], 'lesson') > 0) {
                $pos = strrpos($question['target'], '-');
                $oldLessonId = substr($question['target'], $pos+1);
                $fields['target'] = "course-{$newCourse['id']}/lesson-".$newLessons[$oldLessonId]['id'];
            } else {
                $fields['target'] = "course-{$newCourse['id']}";
            }

            $fields['updatedTime'] = time();
            $fields['createdTime'] = time();
            $fields['pId'] = $question['id'];
            $question = $this->getQuestionDao()->addQuestion($fields);

            $map[$oldQuestionId] = $question;

            if ($question['subCount'] > 0) {
                $subQuestions = $this->getQuestionDao()->findQuestionsByParentId($oldQuestionId);
                foreach ($subQuestions as $subQuestion) {
                    $fields = ArrayToolkit::parts($subQuestion, array('type', 'stem', 'score', 'answer', 'analysis', 'metas', 'categoryId', 'difficulty', 'subCount', 'userId'));
                    $fields['parentId'] = $question['id'];
                    $fields['updatedTime'] = time();
                    $fields['createdTime'] = time();
                    $fields['pId'] = $subQuestion['id'];
                    if (strpos($subQuestion['target'], 'lesson') > 0) {
                        $pos = strrpos($subQuestion['target'], '-');
                        $oldLessonId = substr($subQuestion['target'], $pos+1);
                        $fields['target'] = "course-{$newCourse['id']}/lesson-".$newLessons[$oldLessonId]['id'];
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
            $fields = ArrayToolkit::parts($lesson, array('number', 'seq', 'free', 'status', 'title', 'summary', 'tags', 'type', 'content', 'giveCredit', 'requireCredit', 'mediaId', 'mediaSource', 'mediaName', 'mediaUri', 'length', 'materialNum', 'startTime', 'endTime', 'liveProvider', 'userId','replayStatus'));
            $fields['courseId'] = $newCourse['id'];
            if ($lesson['chapterId']) {
                $fields['chapterId'] = $chapters[$lesson['chapterId']]['id'];
            } else {
                $fields['chapterId'] = 0;
            }

            $fields['createdTime'] = time();

            $fields['parentId'] = $lesson['id'];
            $copiedLesson = $this->getLessonDao()->addLesson($fields);
            $map[$lesson['id']] = $copiedLesson;
            if (array_key_exists("mediaId", $copiedLesson) && $copiedLesson["mediaId"]>0 && in_array($copiedLesson["type"], array('video', 'audio', 'ppt'))) {
                $this->getUploadFileDao()->waveUploadFile($copiedLesson["mediaId"],'usedCount',1);
            }
            if(array_key_exists('type', $lesson) && $lesson['type'] == 'live' && $lesson['replayStatus'] == 'generated' && !empty($copiedLesson)){
                $courseLessonReplay = $this->getCourseService()->getCourseLessonReplayByCourseIdAndLessonId($courseId,$lesson['id']);
                $courseLessonReplay = array('title'=>$courseLessonReplay['title'],'replayId'=>$courseLessonReplay['replayId'],'userId'=>$courseLessonReplay['userId']);
                $courseLessonReplay['courseId'] = $copiedLesson['courseId'];
                $courseLessonReplay['lessonId'] = $copiedLesson['id'];
                $courseLessonReplay['createdTime'] = time();
                $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);
            }
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
            $chapter['pId'] = $chapter['id'];

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

    protected function copyCourse($course, $link = false)
    {
        $fields = ArrayToolkit::parts($course, array('coinPrice', 'originCoinPrice', 'price', 'originPrice', 'title', 'status', 'subtitle', 'type', 'maxStudentNum', 'price', 'coinPrice', 'expiryDay', 'serializeMode', 'lessonNum', 'giveCredit', 'vipLevelId', 'categoryId', 'tags', 'smallPicture', 'middlePicture', 'largePicture', 'about', 'teacherIds', 'goals', 'audiences', 'userId'));
        $fields['createdTime'] = time();
        if ($link) {
            $fields['status'] = empty($fields['status']) ? 'draft' : $fields['status'];
            $fields['parentId'] = $course['id'];
            $fields['locked'] = 1;
        } else {
            $fields['status'] = 'draft';
        }
        $fields["coinPrice"] = $fields["originCoinPrice"];
        $fields["price"] = $fields["originPrice"];
        return $this->getCourseDao()->addCourse(CourseSerialize::serialize($fields));
    }

    protected function copyMaterials($courseId, $newCourse, $newLessons)
    {
        $count = $this->getMaterialDao()->getMaterialCountByCourseId($courseId);
        $materials = $this->getMaterialDao()->findMaterialsByCourseId($courseId, 0, $count);

        $map = array();

        foreach ($materials as $material) {
            $fields = ArrayToolkit::parts($material, array('title', 'description', 'link', 'fileId', 'fileUri', 'fileMime', 'fileSize', 'userId'));

            $fields['courseId'] = $newCourse['id'];
            $fields['pId'] = $material['id'];
            if ($material['lessonId']) {
                $fields['lessonId'] = $newLessons[$material['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();

            $copiedMaterial = $this->getMaterialDao()->addMaterial($fields);
            $map[$material['id']] = $copiedMaterial;

            if (array_key_exists("fileId", $copiedMaterial) && $copiedMaterial["fileId"]>0) {
                $this->getUploadFileDao()->waveUploadFile($copiedMaterial["fileId"],'usedCount',1);
            }
        }

        return $map;
    }

    protected function copyHomeworks($courseId, $newCourse, $newLessons, $newQuestions)
    {
        $homeworks = $this->getHomeworkDao()->findHomeworksByCourseId($courseId);

        $map = array();
        foreach ($homeworks as $homework) {
            $fields = ArrayToolkit::parts($homework, array('description', 'itemCount', 'createdUserId', 'updatedUserId', 'correctPercent'));

            $fields['courseId'] = $newCourse['id'];
            if ($homework['lessonId']) {
                $fields['lessonId'] = $newLessons[$homework['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();
            $fields['updatedTime'] = time();
            $fields['pId'] = $homework['id'];
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
                    'pId'=>$item['id']
                );

                $this->getHomeworkItemDao()->addItem($fields);
            }
        }

        return $map;
    }

    protected function copyExercises($courseId, $newCourse, $newLessons)
    {
        $exercises = $this->getExerciseDao()->findExercisesByCourseId($courseId);

        $map = array();

        foreach ($exercises as $exercise) {
            $fields = ArrayToolkit::parts($exercise, array('itemCount', 'source', 'difficulty', 'questionTypeRange', 'createdUserId'));

            $fields['courseId'] = $newCourse['id'];
            if ($exercise['lessonId']) {
                $fields['lessonId'] = $newLessons[$exercise['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();

            $fields['pId'] = $exercise['id'];

            $map[$exercise['id']] = $this->getExerciseDao()->addExercise($fields);
        }

        return $map;
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    protected function getTestpaperItemDao()
    {
        return $this->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    protected function getLessonDao()
    {
        return $this->createDao('Course.LessonDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getMaterialDao()
    {
        return $this->createDao('Course.CourseMaterialDao');
    }

    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    protected function getExerciseDao()
    {
        return $this->createDao('Homework:Homework.ExerciseDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }
}
