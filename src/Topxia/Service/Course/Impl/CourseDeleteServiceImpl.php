<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseDeleteService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
    public function delete($courseId, $type)
    {
        try {
            $this->getCourseDao()->getConnection()->beginTransaction();
            $course = $this->getCourseService()->getCourse($courseId);
            $types  = array('questions', 'testpapers', 'materials', 'chapters', 'drafts', 'lessons', 'lessonLearns', 'lessonReplays', 'lessonViews', 'homeworks', 'exercises', 'favorites', 'notes', 'threads', 'reviews', 'announcements', 'statuses', 'members', 'course');

            if (!in_array($type, $types)) {
                throw $this->createServiceException('未知类型,删除失败');
            }

            $method = 'delete'.ucwords($type);
            $result = $this->$method($course);
            $this->getCourseDao()->getConnection()->commit();

            return $result;
        } catch (\Exception $e) {
            $this->getCourseDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function deleteLessonResult($lessonId)
    {
        $testpaper = $this->getTestpaperDao()->getTestpaper($lessonId);
        $status    = 'reviewing';
        $this->getTestpaperResultDao()->deleteTestpaperResultByTestpaperIdAndStatus($testpaper['id'], $status);
        return true;
    }

    protected function deleteQuestions($course)
    {
        $questionCount = $this->getQuestionDao()->searchQuestionsCount(array('targetPrefix' => "course-{$course['id']}"));
        $count         = 0;

        if ($questionCount > 0) {
            $questions = $this->getQuestionDao()->searchQuestions(array('targetPrefix' => "course-{$course['id']}"), array('createdTime', 'desc'), 0, 500);

            foreach ($questions as $question) {
                $result = $this->getQuestionDao()->deleteQuestion($question['id']);
                $this->getQuestionFavoriteDao()->deleteFavoriteByQuestionId($question['id']);
                $count += $result;
            }

            $questionLog = "删除课程《{$course['title']}》(#{$course['id']})的问题";
            $this->getLogService()->info('course', 'delete_question', $questionLog);
        }

        return $count;
    }

    protected function deleteTestpapers($course)
    {
        $testpaperCount = $this->getTestpaperDao()->searchTestpapersCount(array('target' => "course-{$course['id']}"));
        $count          = 0;

        if ($testpaperCount > 0) {
            $testpapers = $this->getTestpaperDao()->searchTestpapers(array('target' => "course-{$course['id']}"), array('createdTime', 'desc'), 0, 500);

            foreach ($testpapers as $testpaper) {
                $this->getTestpaperResultDao()->deleteTestpaperResultByTestpaperId($testpaper['id']);
                $this->getTestpaperItemResultDao()->deleteTestpaperItemResultByTestpaperId($testpaper['id']);
                $this->getTestpaperItemDao()->deleteItemsByTestpaperId($testpaper['id']);
                $result = $this->getTestpaperDao()->deleteTestpaper($testpaper['id']);
                $count += $result;
                //删除完成试卷动态
                $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0, 'finished_testpaper', 'testpaper', $testpaper['id']);
            }

            $testpaperLog = "删除课程《{$course['title']}》(#{$course['id']})的试卷";
            $this->getLogService()->info('course', 'delete_testpaper', $testpaperLog);
        }

        return $count;
    }

    protected function deleteMaterials($course)
    {
        $conditions    = array('courseId' => $course['id'], 'type' => 'course');
        $materialCount = $this->getMaterialService()->searchMaterialCount($conditions);
        $count         = 0;

        if ($materialCount > 0) {
            $materials = $this->getMaterialService()->searchMaterials($conditions, array('createdTime', 'DESC'), 0, $materialCount);

            foreach ($materials as $material) {
                $result = $this->getMaterialService()->deleteMaterial($course['id'], $material['id']);
                $count += $result;
            }

            $materialLog = "删除课程《{$course['title']}》(#{$course['id']})的课时资料";
            $this->getLogService()->info('course', 'delete_material', $materialLog);
        }

        return $count;
    }

    protected function deleteChapters($course)
    {
        $chapterCount = $this->getCourseChapterDao()->searchChapterCount(array('courseId' => $course['id']));
        $count        = 0;

        if ($chapterCount > 0) {
            $chapters = $this->getCourseChapterDao()->searchChapters(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($chapters as $chapter) {
                $result = $this->getCourseChapterDao()->deleteChapter($chapter['id']);
                $count += $result;
            }

            $chapterLog = "删除课程《{$course['title']}》(#{$course['id']})的课时章/节";
            $this->getLogService()->info('course', 'delete_chapter', $chapterLog);
        }

        return $count;
    }

    protected function deleteDrafts($course)
    {
        $draftCount = $this->getDraftDao()->searchDraftCount(array('courseId' => $course['id']));
        $count      = 0;

        if ($draftCount > 0) {
            $drafts = $this->getDraftDao()->searchDrafts(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($drafts as $draft) {
                $result = $this->getDraftDao()->deleteDraft($draft['id']);
                $count += $result;
            }

            $draftLog = "删除课程《{$course['title']}》(#{$course['id']})的草稿";
            $this->getLogService()->info('course', 'delete_draft', $draftLog);
        }

        return $count;
    }

    protected function deleteLessons($course)
    {
        $lessonCount = $this->getLessonDao()->searchLessonCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($lessonCount > 0) {
            $lessons = $this->getLessonDao()->searchLessons(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($lessons as $lesson) {
                if (!empty($lesson['mediaId'])) {
                    $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
                }

                $this->getCrontabService()->deleteJobs($lesson['id'], 'lesson');

                $result = $this->getLessonDao()->deleteLesson($lesson['id']);
                $this->getLessonExtendDao()->deleteLesson($lesson['id']);
                $count += $result;
            }

            //删除定时任务

            $lessonLog = "删除课程《{$course['title']}》(#{$course['id']})的课时";
            $this->getLogService()->info('course', 'delete_lesson', $lessonLog);
        }

        return $count;
    }

    protected function deleteLessonLearns($course)
    {
        $lessonLearnCount = $this->getLessonLearnDao()->searchLearnCount(array('courseId' => $course['id']));
        $count            = 0;

        if ($lessonLearnCount > 0) {
            $lessonLearns = $this->getLessonLearnDao()->searchLearns(array('courseId' => $course['id']), array('startTime', 'desc'), 0, 500);

            foreach ($lessonLearns as $lessonLearn) {
                $result = $this->getLessonLearnDao()->deleteLearn($lessonLearn['id']);
                $count += $result;
            }

            $lessonLearnLog = "删除课程《{$course['title']}》(#{$course['id']})的课时学习记录";
            $this->getLogService()->info('course', 'delete_lessonLearn', $lessonLearnLog);
        }

        return $count;
    }

    protected function deleteLessonReplays($course)
    {
        $lessonReplayCount = $this->getCourseLessonReplayDao()->searchCourseLessonReplayCount(array('courseId' => $course['id']));
        $count             = 0;

        if ($lessonReplayCount > 0) {
            $LessonReplays = $this->getCourseLessonReplayDao()->searchCourseLessonReplays(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($LessonReplays as $LessonReplay) {
                $result = $this->getCourseLessonReplayDao()->deleteCourseLessonReplay($LessonReplay['id']);
                $count += $result;
            }

            $LessonReplayLog = "删除课程《{$course['title']}》(#{$course['id']})的录播";
            $this->getLogService()->info('course', 'delete_lessonReplay', $LessonReplayLog);
        }

        return $count;
    }

    protected function deleteLessonViews($course)
    {
        $lessonViewCount = $this->getLessonViewDao()->searchLessonViewCount(array('courseId' => $course['id']));
        $count           = 0;

        if ($lessonViewCount > 0) {
            $lessonViews = $this->getLessonViewDao()->searchLessonView(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($lessonViews as $lessonView) {
                $result = $this->getLessonViewDao()->deleteLessonView($lessonView['id']);
                $count += $result;
            }

            $lessonViewLog = "删除课程《{$course['title']}》(#{$course['id']})的浏览记录";
            $this->getLogService()->info('course', 'delete_lessonView', $lessonViewLog);
        }

        return $count;
    }

    protected function deleteHomeworks($course)
    {
        $count    = 0;
        $homework = $this->getAppService()->findInstallApp('Homework');

        if (!empty($homework)) {
            $isDeleteHomework = $homework && version_compare($homework['version'], "1.3.1", ">=");

            if ($isDeleteHomework) {
                $HomeworkCount = $this->getHomeworkDao()->searchHomeworkCount(array('courseId' => $course['id']));

                if ($HomeworkCount > 0) {
                    $homeworks = $this->getHomeworkDao()->searchHomeworks(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

                    foreach ($homeworks as $homework) {
                        $this->getHomeworkResultDao()->deleteResultsByHomeworkId($homework['id']);
                        $this->getHomeworkItemResultDao()->deleteItemResultsByHomeworkId($homework['id']);
                        $this->getHomeworkItemDao()->deleteItemsByHomeworkId($homework['id']);
                        $result = $this->getHomeworkDao()->deleteHomework($homework['id']);
                        $count += $result;
                        //删除完成作业动态
                        $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0, 'finished_homework', 'homework', $homework['id']);
                    }

                    $homeworkLog = "删除课程《{$course['title']}》(#{$course['id']})的作业";
                    $this->getLogService()->info('homework', 'delete', $homeworkLog);
                }
            }
        }

        return $count;
    }

    protected function deleteExercises($course)
    {
        $count    = 0;
        $homework = $this->getAppService()->findInstallApp('Homework');

        if (!empty($homework)) {
            $isDeleteHomework = $homework && version_compare($homework['version'], "1.3.1", ">=");

            if ($isDeleteHomework) {
                $exerciseCount = $this->getExerciseDao()->searchExerciseCount(array('courseId' => $course['id']));

                if ($exerciseCount > 0) {
                    $exercises = $this->getExerciseDao()->searchExercises(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

                    foreach ($exercises as $exercise) {
                        $this->getExerciseResultDao()->deleteExerciseResultByExerciseId($exercise['id']);
                        $this->getExerciseItemResultDao()->deleteItemResultByExerciseId($exercise['id']);
                        $this->getExerciseItemDao()->deleteItemByExerciseId($exercise['id']);
                        $result = $this->getExerciseDao()->deleteExercise($exercise['id']);
                        $count += $result;
                        //删除完成练习的动态
                        $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0, 'finished_exercise', 'exercise', $exercise['id']);
                    }

                    $exerciseLog = "删除课程《{$course['title']}》(#{$course['id']})的练习";
                    $this->getLogService()->info('exercise', 'delete', $exerciseLog);
                }
            }
        }

        return $count;
    }

    protected function deleteFavorites($course)
    {
        $favoriteCount = $this->getFavoriteDao()->searchCourseFavoriteCount(array('courseId' => $course['id']));
        $count         = 0;

        if ($favoriteCount > 0) {
            $favorites = $this->getFavoriteDao()->searchCourseFavorites(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($favorites as $favorite) {
                $result = $this->getFavoriteDao()->deleteFavorite($favorite['id']);
                $count += $result;
            }

            $favoriteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程收藏";
            $this->getLogService()->info('course', 'delete_favorite', $favoriteLog);
        }

        return $count;
    }

    protected function deleteNotes($course)
    {
        $noteCount = $this->getCourseNoteDao()->searchNoteCount(array('courseId' => $course['id']));
        $count     = 0;

        if ($noteCount > 0) {
            $notes = $this->getCourseNoteDao()->searchNotes(array('courseId' => $course['id']), array('createdTime' => 'DESC'), 0, 500);

            foreach ($notes as $note) {
                $this->getCourseNoteLikeDao()->deleteNoteLikesByNoteId($note['id']);
                $result = $this->getCourseNoteDao()->deleteNote($note['id']);
                $count += $result;
            }

            $noteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程笔记";
            $this->getLogService()->info('course', 'delete_note', $noteLog);
        }

        return $count;
    }

    protected function deleteThreads($course)
    {
        $threadCount = $this->getThreadDao()->searchThreadCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($threadCount > 0) {
            $threads = $this->getThreadDao()->searchThreads(array('courseId' => $course['id']), array(array('createdTime', 'desc')), 0, 500);

            foreach ($threads as $thread) {
                $this->getThreadPostDao()->deletePostsByThreadId($thread['id']);
                $result = $this->getThreadDao()->deleteThread($thread['id']);
                $count += $result;
            }

            $threadLog = "删除课程《{$course['title']}》(#{$course['id']})的话题";
            $this->getLogService()->info('course', 'delete_thread', $threadLog);
        }

        return $count;
    }

    protected function deleteReviews($course)
    {
        $reviewCount = $this->getReviewDao()->searchReviewsCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($reviewCount > 0) {
            $reviews = $this->getReviewDao()->searchReviews(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($reviews as $review) {
                $result = $this->getReviewDao()->deleteReview($review['id']);
                $count += $result;
            }

            $reviewLog = "删除课程《{$course['title']}》(#{$course['id']})的评价";
            $this->getLogService()->info('course', 'delete_review', $reviewLog);
        }

        return $count;
    }

    protected function deleteAnnouncements($course)
    {
        $announcementCount = $this->getAnnouncementDao()->searchAnnouncementsCount(array('targetId' => $course['id'], 'targetType' => 'course'));
        $count             = 0;

        if ($announcementCount > 0) {
            $announcements = $this->getAnnouncementDao()->searchAnnouncements(array('targetType' => 'course', 'targetId' => $course['id']), array('createdTime', 'DESC'), 0, 500);

            foreach ($announcements as $announcement) {
                $result = $this->getAnnouncementDao()->deleteAnnouncement($announcement['id']);
                $count += $result;
            }

            $announcementLog = "删除课程《{$course['title']}》(#{$course['id']})的公告";
            $this->getLogService()->info('course', 'delete_announcement', $announcementLog);
        }

        return $count;
    }

    protected function deleteStatuses($course)
    {
        $statusCount = $this->getStatusDao()->searchStatusesCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($statusCount > 0) {
            $statuses = $this->getStatusDao()->searchStatuses(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($statuses as $status) {
                $result = $this->getStatusDao()->deleteStatus($status['id']);
                $count += $result;
            }

            $statusLog = "删除课程《{$course['title']}》(#{$course['id']})的动态";
            $this->getLogService()->info('course', 'delete_status', $statusLog);
        }

        return $count;
    }

    protected function deleteMembers($course)
    {
        $memberCount = $this->getCourseMemberDao()->searchMemberCount(array('courseId' => $course['id']));
        $count       = 0;

        if ($memberCount > 0) {
            $members = $this->getCourseMemberDao()->searchMembers(array('courseId' => $course['id']), array('createdTime', 'desc'), 0, 500);

            foreach ($members as $member) {
                $result = $this->getCourseMemberDao()->deleteMember($member['id']);
                $count += $result;
            }

            $memberLog = "删除课程《{$course['title']}》(#{$course['id']})的成员";
            $this->getLogService()->info('course', 'delete_member', $memberLog);
        }

        return $count;
    }

    protected function deleteCourse($course)
    {
        $this->getCourseDao()->deleteCourse($course['id']);
        $courseLog = "删除课程《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('course', 'delete', $courseLog);
        return 0;
    }

    protected function deleteJob($jobs)
    {
        foreach ($jobs as $key => $job) {
            $this->getCrontabService()->deleteJob($job['id']);
        }
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper.TestpaperService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getCrontabService()
    {
        return $this->createService('Crontab.CrontabService');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course.MaterialService');
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    protected function getDraftDao()
    {
        return $this->createDao('Course.CourseDraftDao');
    }

    protected function getLessonDao()
    {
        return $this->createDao('Course.LessonDao');
    }

    protected function getLessonExtendDao()
    {
        return $this->createDao('Course.LessonExtendDao');
    }

    protected function getLessonLearnDao()
    {
        return $this->createDao('Course.LessonLearnDao');
    }

    protected function getCourseLessonReplayDao()
    {
        return $this->createDao('Course.CourseLessonReplayDao');
    }

    protected function getLessonViewDao()
    {
        return $this->createDao('Course.LessonViewDao');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->createDao('Question.QuestionFavoriteDao');
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper.TestpaperResultDao');
    }

    protected function getTestpaperItemResultDao()
    {
        return $this->createDao('Testpaper.TestpaperItemResultDao');
    }

    protected function getTestpaperItemDao()
    {
        return $this->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    protected function getHomeworkItemResultDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemResultDao');
    }

    protected function getHomeworkResultDao()
    {
        return $this->createDao('Homework:Homework.HomeworkResultDao');
    }

    protected function getExerciseDao()
    {
        return $this->createDao('Homework:Homework.ExerciseDao');
    }

    protected function getExerciseItemDao()
    {
        return $this->createDao('Homework:Homework.ExerciseItemDao');
    }

    protected function getExerciseItemResultDao()
    {
        return $this->createDao('Homework:Homework.ExerciseItemResultDao');
    }

    protected function getExerciseResultDao()
    {
        return $this->createDao('Homework:Homework.ExerciseResultDao');
    }

    protected function getFavoriteDao()
    {
        return $this->createDao('Course.FavoriteDao');
    }

    protected function getCourseNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    protected function getCourseNoteLikeDao()
    {
        return $this->createDao('Course.CourseNoteLikeDao');
    }

    protected function getThreadDao()
    {
        return $this->createDao('Course.ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Course.ThreadPostDao');
    }

    protected function getReviewDao()
    {
        return $this->createDao('Course.ReviewDao');
    }

    protected function getAnnouncementDao()
    {
        return $this->createDao('Announcement.AnnouncementDao');
    }

    protected function getStatusDao()
    {
        return $this->createDao('User.StatusDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course.CourseMemberDao');
    }
}
