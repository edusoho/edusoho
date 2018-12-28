<?php

namespace Biz\Course\Service\Impl;

use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\FavoriteDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\MemberException;
use Biz\Exception\UnableJoinException;
use Biz\File\UploadFileException;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Visitor\CourseItemPagingVisitor;
use Biz\Task\Visitor\CourseItemSortingVisitor;
use Biz\User\Service\UserService;
use Biz\System\Service\LogService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReviewService;
use Biz\System\Service\SettingService;
use Biz\Course\Service\MaterialService;
use Biz\Task\Service\TaskResultService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\CourseNoteService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseDeleteService;
use Biz\Activity\Service\Impl\ActivityServiceImpl;
use AppBundle\Common\TimeMachine;
use AppBundle\Common\CourseToolkit;

class CourseServiceImpl extends BaseService implements CourseService
{
    const MAX_REWARD_POINT = 100000;

    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByIds($ids)
    {
        $courses = $this->getCourseDao()->findCoursesByIds($ids);

        return ArrayToolkit::index($courses, 'id');
    }

    public function findCoursesByCourseSetIds(array $setIds)
    {
        return $this->getCourseDao()->findByCourseSetIds($setIds);
    }

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($courseSetId, null);
    }

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        return $this->getCourseDao()->findCoursesByParentIdAndLocked($parentId, $locked);
    }

    public function findPublishedCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetIdAndStatus($courseSetId, 'published');
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getDefaultCourseByCourseSetId($courseSetId);
    }

    public function getDefaultCoursesByCourseSetIds($courseSetIds)
    {
        return $this->getCourseDao()->getDefaultCoursesByCourseSetIds($courseSetIds);
    }

    public function setDefaultCourse($courseSetId, $id)
    {
        $course = $this->getDefaultCourseByCourseSetId($courseSetId);
        $this->getCourseDao()->update($course['id'], array('isDefault' => 0, 'courseType' => 'normal'));
        $this->getCourseDao()->update($id, array('isDefault' => 1, 'courseType' => 'default'));
    }

    public function getSeqMinPublishedCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
                'status' => 'published',
            ),
            array('seq' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function getFirstPublishedCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
                'status' => 'published',
            ),
            array('seq' => 'ASC', 'createdTime' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function getFirstCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
            ),
            array('seq' => 'ASC', 'createdTime' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function getLastCourseByCourseSetId($courseSetId)
    {
        $courses = $this->searchCourses(
            array(
                'courseSetId' => $courseSetId,
            ),
            array('seq' => 'DESC', 'createdTime' => 'DESC'),
            0,
            1
        );

        return array_shift($courses);
    }

    public function createCourse($course)
    {
        if (!ArrayToolkit::requireds($course, array('title', 'courseSetId', 'expiryMode', 'learnMode'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        if (!in_array($course['learnMode'], static::learnModes())) {
            $this->createNewException(CourseException::LEARNMODE_INVALID());
        }

        if (!in_array($course['courseType'], static::courseTypes())) {
            $this->createNewException(CourseException::COURSETYPE_INVALID());
        }

        if (!isset($course['isDefault'])) {
            $course['isDefault'] = 0;
        }

        $count = $this->searchCourseCount(
            array(
                'courseSetId' => $course['courseSetId'],
            )
        );
        if ($count > 9) {
            $this->createNewException(CourseException::COURSE_NUM_LIMIT());
        }

        $course = ArrayToolkit::parts(
            $course,
            array(
                'title',
                'about',
                'courseSetId',
                'learnMode',
                'expiryMode',
                'expiryDays',
                'expiryStartDate',
                'serializeMode',
                'expiryEndDate',
                'isDefault',
                'isFree',
                'seq',
                'serializeMode',
                'courseType',
                'type',
                'enableAudio',
                'showServices',
            )
        );

        if (isset($course['about'])) {
            $course['about'] = $this->purifyHtml($course['about'], true);
        }

        if (!isset($course['isFree'])) {
            $course['isFree'] = 1; //默认免费
        }

        $course = $this->validateExpiryMode($course);

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $lastCourse = $this->getLastCourseByCourseSetId($courseSet['id']);

        $course['seq'] = $lastCourse['seq'] + 1;
        $course['maxRate'] = $courseSet['maxRate'];
        $course['courseSetTitle'] = empty($courseSet['title']) ? '' : $courseSet['title'];

        $course['status'] = 'draft';
        $course['creator'] = $this->getCurrentUser()->getId();
        try {
            $this->beginTransaction();

            $created = $this->getCourseDao()->create($course);
            $currentUser = $this->getCurrentUser();
            //set default teacher
            $this->getMemberService()->setDefaultTeacher($created['id']);
            $this->commit();
            $this->dispatchEvent('course.create', new Event($created));

            return $created;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function copyCourse($newCourse)
    {
        $sourceCourse = $this->tryManageCourse($newCourse['copyCourseId']);
        $newCourse = ArrayToolkit::parts(
            $newCourse,
            array(
                'title',
                'courseSetId',
                'learnMode',
                'expiryMode',
                'expiryDays',
                'expiryStartDate',
                'expiryEndDate',
                'courseType',
            )
        );

        $newCourse = $this->validateExpiryMode($newCourse);

        return $this->biz['course_copy']->copy($sourceCourse, $newCourse);
    }

    public function updateBaseInfo($id, $fields)
    {
        $oldCourse = $this->canUpdateCourseBaseInfo($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($oldCourse['courseSetId']);
        $this->validatie($id, $fields);

        if (empty($fields['enableBuyExpiryTime'])) {
            $fields['buyExpiryTime'] = 0;
        }
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'subtitle',
                'originPrice',
                'enableAudio',
                'tryLookable',
                'enableFinish',
                'vipLevelId',
                'buyExpiryTime',
                'learnMode',
                'buyable',
                'expiryStartDate',
                'expiryEndDate',
                'expiryMode',
                'expiryDays',
                'maxStudentNum',
                'services',
                'tryLookLength',
                'watchLimit',
            )
        );
        if (!empty($fields['services'])) {
            $fields['showServices'] = 1;
        } else {
            $fields['showServices'] = 0;
        }

        if ('published' != $courseSet['status'] || 'published' != $oldCourse['status']) {
            $fields['expiryMode'] = isset($fields['expiryMode']) ? $fields['expiryMode'] : $oldCourse['expiryMode'];
        }

        if ('draft' == $oldCourse['status']) {
            $fields['learnMode'] = isset($fields['learnMode']) ? $fields['learnMode'] : $oldCourse['learnMode'];
        } else {
            $fields['learnMode'] = $oldCourse['learnMode'];
        }
        $fields = $this->validateExpiryMode($fields);
        $fields = $this->processFields($oldCourse, $fields, $courseSet);
        $course = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($course));
        $this->dispatchEvent('course.marketing.update', array('oldCourse' => $oldCourse, 'newCourse' => $course));
    }

    public function updateCourse($id, $fields)
    {
        $oldCourse = $this->tryManageCourse($id);

        $this->validatie($id, $fields);

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'subtitle',
                'courseSetTitle',
                'about', //@todo 目前没有这个字段
                'courseSetId',
                'summary',
                'goals',
                'audiences',
                'enableFinish',
                'serializeMode',
                'maxStudentNum',
                'locked',
                'enableAudio',
            )
        );

        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        if (isset($fields['summary'])) {
            $fields['summary'] = $this->purifyHtml($fields['summary'], true);
        }

        $course = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function recommendCourseByCourseSetId($courseSetId, $fields)
    {
        $requiredKeys = array('recommended', 'recommendedSeq', 'recommendedTime');
        $fields = ArrayToolkit::parts($fields, $requiredKeys);
        if (!ArrayToolkit::requireds($fields, array('recommended', 'recommendedSeq', 'recommendedTime'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $this->getCourseDao()->updateCourseRecommendByCourseSetId($courseSetId, $fields);
    }

    public function cancelRecommendCourseByCourseSetId($courseSetId)
    {
        $fields = array(
            'recommended' => 0,
            'recommendedTime' => 0,
            'recommendedSeq' => 0,
        );
        $this->getCourseDao()->updateCourseRecommendByCourseSetId($courseSetId, $fields);
    }

    public function updateMaxRate($id, $maxRate)
    {
        $course = $this->getCourseDao()->update($id, array('maxRate' => $maxRate));
        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function updateCategoryByCourseSetId($courseSetId, $categoryId)
    {
        $this->getCourseDao()->updateCategoryByCourseSetId($courseSetId, array('categoryId' => $categoryId));
    }

    public function updateMaxRateByCourseSetId($courseSetId, $maxRate)
    {
        $this->getCourseDao()->updateMaxRateByCourseSetId(
            $courseSetId,
            array('updatedTime' => time(), 'maxRate' => $maxRate)
        );
    }

    public function updateCourseMarketing($id, $fields)
    {
        $oldCourse = $this->tryManageCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($oldCourse['courseSetId']);

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'summary',
                'goals',
                'audiences',
                'isFree',
                'originPrice',
                'vipLevelId',
                'buyable',
                'tryLookable',
                'tryLookLength',
                'watchLimit',
                'buyExpiryTime',
                'showServices',
                'services',
                'approval',
                'coinPrice',
                'expiryMode', //days、end_date、date、forever
                'expiryDays',
                'expiryStartDate',
                'expiryEndDate',
                'taskRewardPoint',
                'rewardPoint',
            )
        );

        if ('published' != $courseSet['status'] || 'published' != $oldCourse['status']) {
            $fields['expiryMode'] = isset($fields['expiryMode']) ? $fields['expiryMode'] : $oldCourse['expiryMode'];
        }

        if (!$this->isTeacherAllowToSetRewardPoint()) {
            unset($fields['taskRewardPoint']);
            unset($fields['rewardPoint']);
        }

        $requireFields = array('title', 'isFree', 'buyable');

        if ('normal' == $courseSet['type'] && $this->isCloudStorage()) {
            array_push($requireFields, 'tryLookable');
        } else {
            $fields['tryLookable'] = 0;
        }

        if (!ArrayToolkit::requireds($fields, $requireFields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->validateExpiryMode($fields);

        $fields = $this->processFields($oldCourse, $fields, $courseSet);

        $newCourse = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($newCourse));
        $this->dispatchEvent('course.marketing.update', array('oldCourse' => $oldCourse, 'newCourse' => $newCourse));

        return $newCourse;
    }

    public function batchConvert($courseId)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'video', true);
        $medias = ArrayToolkit::column($activities, 'ext');
        $this->getUploadFileService()->batchConvertByIds(array_unique(ArrayToolkit::column($medias, 'mediaId')));
    }

    public function isSupportEnableAudio($enableAudioStatus = false)
    {
        if (empty($enableAudioStatus)) {
            return false;
        }

        $setting = $this->getSettingService()->get('storage', array());

        if (!empty($setting['upload_mode']) && 'cloud' != $setting['upload_mode']) {
            return false;
        }

        return true;
    }

    public function convertAudioByCourseIdAndMediaId($courseId, $mediaId)
    {
        $course = $this->tryManageCourse($courseId);
        $storage = $this->getSettingService()->get('storage', array('upload_mode' => 'local'));

        if (empty($course['enableAudio']) || 'local' == $storage['upload_mode']) {
            return false;
        }

        $media = $this->getUploadFileService()->getFile($mediaId);

        if (empty($media)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if ('cloud' != $media['storage'] || in_array($media['audioConvertStatus'], array('doing', 'success'))) {
            return false;
        }

        $this->getUploadFileService()->retryTranscode(array($media['globalId']));
        $this->getUploadFileService()->setAudioConvertStatus($media['id'], 'doing');

        return true;
    }

    public function updateCourseRewardPoint($id, $fields)
    {
        $oldCourse = $this->tryManageCourse($id);

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'taskRewardPoint',
                'rewardPoint',
            )
        );

        $newCourse = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($newCourse));
        $this->dispatchEvent('course.reward_point.update', array('oldCourse' => $oldCourse, 'newCourse' => $newCourse));

        return $newCourse;
    }

    public function validateCourseRewardPoint($fields)
    {
        if (isset($fields['taskRewardPoint'])) {
            if ((!preg_match('/^\+?[0-9][0-9]*$/', $fields['taskRewardPoint'])) || ($fields['taskRewardPoint'] > self::MAX_REWARD_POINT)) {
                return true;
            }
        }

        if (isset($fields['rewardPoint'])) {
            if ((!preg_match('/^\+?[0-9][0-9]*$/', $fields['rewardPoint'])) || ($fields['rewardPoint'] > self::MAX_REWARD_POINT)) {
                return true;
            }
        }

        return false;
    }

    protected function isTeacherAllowToSetRewardPoint()
    {
        $rewardPointSetting = $this->getSettingService()->get('reward_point', array());

        return !empty($rewardPointSetting) && $rewardPointSetting['enable'] && $rewardPointSetting['allowTeacherSet'];
    }

    protected function isCloudStorage()
    {
        $storage = $this->getSettingService()->get('storage', array());

        return !empty($storage['upload_mode']) && 'cloud' === $storage['upload_mode'];
    }

    /**
     * 计算教学计划价格和虚拟币价格
     *
     * @param  $id
     * @param int|float $originPrice 教学计划原价
     *
     * @return array (number, number)
     */
    protected function calculateCoursePrice($id, $originPrice)
    {
        $course = $this->getCourse($id);
        $price = $originPrice;
        $coinPrice = $course['originCoinPrice'];
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (!empty($courseSet['discountId'])) {
            $price = $price * $courseSet['discount'] / 10;
            $coinPrice = $coinPrice * $courseSet['discount'] / 10;
        }

        return array($price, $coinPrice);
    }

    protected function validatie($id, &$fields)
    {
        if (!empty($fields['enableAudio'])) {
            $audioServiceStatus = $this->getUploadFileService()->getAudioServiceStatus();
            if ('opened' != $audioServiceStatus) {
                $this->getCourseDao()->update($id, array('enableAudio' => '0'));
                unset($fields['enableAudio']);
            }
        }
    }

    public function updateCourseStatistics($id, $fields)
    {
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $updateFields = array();
        foreach ($fields as $field) {
            if ('studentNum' === $field) {
                $updateFields['studentNum'] = $this->countStudentsByCourseId($id);
            } elseif ('taskNum' === $field) {
                $updateFields['taskNum'] = $this->getTaskService()->countTasks(
                    array('courseId' => $id, 'isOptional' => 0)
                );
            } elseif ('compulsoryTaskNum' === $field) {
                $updateFields['compulsoryTaskNum'] = $this->getTaskService()->countTasks(
                    array('courseId' => $id, 'isOptional' => 0)
                );
            } elseif ('discussionNum' === $field) {
                $updateFields['discussionNum'] = $this->countThreadsByCourseIdAndType($id, 'discussion');
            } elseif ('questionNum' === $field) {
                $updateFields['questionNum'] = $this->countThreadsByCourseIdAndType($id, 'question');
            } elseif ('ratingNum' === $field) {
                $ratingFields = $this->getReviewService()->countRatingByCourseId($id);
                $updateFields = array_merge($updateFields, $ratingFields);
            } elseif ('noteNum' === $field) {
                $updateFields['noteNum'] = $this->getNoteService()->countCourseNoteByCourseId($id);
            } elseif ('materialNum' === $field) {
                $updateFields['materialNum'] = $this->getCourseMaterialService()->countMaterials(
                    array('courseId' => $id, 'source' => 'coursematerial')
                );
            } elseif ('publishLessonNum' === $field) {
                $updateFields['publishLessonNum'] = $this->getCourseLessonService()->countLessons(
                    array('courseId' => $id, 'status' => 'published')
                );
            } elseif ('lessonNum' === $field) {
                $updateFields['lessonNum'] = $this->getCourseLessonService()->countLessons(
                    array('courseId' => $id)
                );
            }
        }

        if (empty($updateFields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $course = $this->getCourseDao()->update($id, $updateFields);
        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    public function deleteCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ('published' == $course['status']) {
            $this->createNewException(CourseException::FORBIDDEN_DELETE_PUBLISHED());
        }

        $subCourses = $this->findCoursesByParentIdAndLocked($id, 1);
        if (!empty($subCourses)) {
            $this->createNewException(CourseException::SUB_COURSE_EXIST());
        }
        $courseCount = $this->countCourses(array('courseSetId' => $course['courseSetId']));
        if ($courseCount <= 1) {
            $this->createNewException(CourseException::COURSE_NUM_REQUIRED());
        }

        $result = $this->getCourseDeleteService()->deleteCourse($id);

        $this->dispatchEvent('course.delete', new Event($course));

        return $result;
    }

    public function closeCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ('published' != $course['status']) {
            $this->createNewException(CourseException::UNPUBLISHED_COURSE());
        }
        $course['status'] = 'closed';

        try {
            $this->beginTransaction();
            $course = $this->getCourseDao()->update($id, $course);

            $publishedCourses = $this->findPublishedCoursesByCourseSetId($course['courseSetId']);
            //如果课程下没有了已发布的教学计划，则关闭此课程
            if (empty($publishedCourses)) {
                $this->getCourseSetDao()->update($course['courseSetId'], array('status' => 'closed'));
            }
            $this->commit();
            $this->dispatchEvent('course.close', new Event($course));
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function publishCourse($id, $withTasks = false)
    {
        $this->tryManageCourse($id);
        $course = $this->getCourseDao()->update(
            $id,
            array(
                'status' => 'published',
            )
        );
        $this->dispatchEvent('course.publish', $course);

        $this->getCourseLessonService()->publishLessonByCourseId($course['id']);
    }

    public function hasNoTitleForDefaultPlanInMulPlansCourse($id)
    {
        $course = $this->tryManageCourse($id);
        if ($this->hasMulCourses($course['courseSetId'])) {
            $defaultCourse = $this->getDefaultCourseByCourseSetId($course['courseSetId']);

            return !empty($defaultCourse) && empty($defaultCourse['title']);
        }

        return false;
    }

    public function publishAndSetDefaultCourseType($courseId, $title)
    {
        $course = $this->tryManageCourse($courseId);

        if ($this->hasMulCourses($course['courseSetId'])) {
            $defaultCourse = $this->getDefaultCourseByCourseSetId($course['courseSetId']);
            try {
                $this->beginTransaction();
                $this->updateCourse($defaultCourse['id'], array('title' => $title));
                $this->publishCourse($courseId);
                $this->commit();
            } catch (\Exception $e) {
                $this->rollback();
                throw $e;
            }
        }
    }

    public function hasMulCourses($courseSetId, $isPublish = 0)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
        );
        if ($isPublish) {
            $conditions['status'] = 'published';
        }

        $count = $this->countCourses($conditions);

        return $count > 1;
    }

    public function isCourseSetCoursesSummaryEmpty($courseSetId)
    {
        $courses = $this->searchCourses(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX, array('summary'));
        foreach ($courses as $course) {
            if (!empty($course['summary'])) {
                return true;
            }
        }

        return false;
    }

    protected function validateExpiryMode($course)
    {
        if (empty($course['expiryMode'])) {
            return $course;
        }
        //enum: [days,end_date,date,forever]
        if ('days' === $course['expiryMode']) {
            $course['expiryStartDate'] = null;
            $course['expiryEndDate'] = null;

            if (empty($course['expiryDays'])) {
                $this->createNewException(CourseException::EXPIRYDAYS_REQUIRED());
            }
        } elseif ('end_date' == $course['expiryMode']) {
            $course['expiryStartDate'] = null;
            $course['expiryDays'] = 0;

            if (empty($course['expiryEndDate'])) {
                $this->createNewException(CourseException::EXPIRYENDDATE_REQUIRED());
            }
            $course['expiryEndDate'] = TimeMachine::isTimestamp($course['expiryEndDate']) ? $course['expiryEndDate'] : strtotime($course['expiryEndDate'].' 23:59:59');
        } elseif ('date' === $course['expiryMode']) {
            $course['expiryDays'] = 0;
            if (isset($course['expiryStartDate'])) {
                $course['expiryStartDate'] = TimeMachine::isTimestamp($course['expiryStartDate']) ? $course['expiryStartDate'] : strtotime($course['expiryStartDate']);
            } else {
                $this->createNewException(CourseException::EXPIRYSTARTDATE_REQUIRED());
            }
            if (empty($course['expiryEndDate'])) {
                $this->createNewException(CourseException::EXPIRYENDDATE_REQUIRED());
            } else {
                $course['expiryEndDate'] = TimeMachine::isTimestamp($course['expiryEndDate']) ? $course['expiryEndDate'] : strtotime($course['expiryEndDate'].' 23:59:59');
            }
            if ($course['expiryEndDate'] <= $course['expiryStartDate']) {
                $this->createNewException(CourseException::EXPIRY_DATE_SET_INVALID());
            }
        } elseif ('forever' == $course['expiryMode']) {
            $course['expiryStartDate'] = 0;
            $course['expiryEndDate'] = 0;
            $course['expiryDays'] = 0;
        } else {
            $this->createNewException(CourseException::EXPIRYMODE_INVALID());
        }

        return $course;
    }

    public function findCourseItems($courseId, $limitNum = 0)
    {
        $course = $this->getCourse($courseId);
        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }
        $tasks = $this->findTasksByCourseId($course['id']);

        return $this->createCourseStrategy($course)->prepareCourseItems($courseId, $tasks, $limitNum);
    }

    protected function findTasksByCourseId($courseId)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($courseId);
        }

        return $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
    }

    public function findCourseItemsByPaging($courseId, $paging = array())
    {
        $course = $this->getCourse($courseId);
        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $result = $this->createCourseStrategy($course)->accept(new CourseItemPagingVisitor($this->biz, $courseId, $paging));
        if (!empty($paging['limit']) && $result[1] > $paging['limit']) {  //$result[1] 为总数， $result[0] 为相应的数据
            $result[0] = array_slice($result[0], 0, $paging['limit']);
            $result[1] = $paging['limit'];
        }

        return $result;
    }

    public function tryManageCourse($courseId, $courseSetId = 0)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $course = $this->getCourseDao()->get($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }
        if ($courseSetId > 0 && $course['courseSetId'] !== $courseSetId) {
            $this->createNewException(CourseException::NOT_MATCH_COURSESET());
        }

        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);
            if (!empty($classroom) && $classroom['headTeacherId'] == $user['id']) {
                //班主任有权管理班级下所有课程
                return $course;
            }
        }

        if (!$this->hasCourseManagerRole($courseId)) {
            $this->createNewException(CourseException::FORBIDDEN_MANAGE_COURSE());
        }

        return $course;
    }

    public function findStudentsByCourseId($courseId)
    {
        $students = $this->getMemberDao()->findByCourseIdAndRole($courseId, 'student');

        return $this->fillMembersWithUserInfo($students);
    }

    public function findTeachersByCourseId($courseId)
    {
        $teachers = $this->getMemberDao()->findByCourseIdAndRole($courseId, 'teacher');

        return $this->fillMembersWithUserInfo($teachers);
    }

    public function countStudentsByCourseId($courseId)
    {
        return $this->getMemberDao()->count(
            array(
                'courseId' => $courseId,
                'role' => 'student',
            )
        );
    }

    public function countThreadsByCourseIdAndType($courseId, $type)
    {
        return $this->getCourseThreadService()->countThreads(array('courseId' => $courseId, 'type' => $type));
    }

    // Refactor: 该函数不属于CourseService
    public function countThreadsByCourseId($courseId)
    {
        return $this->getThreadDao()->count(
            array(
                'courseId' => $courseId,
            )
        );
    }

    public function getUserRoleInCourse($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        return empty($member) ? null : $member['role'];
    }

    // Refactor: findTeachingCoursesByCourseSetId
    public function findUserTeachingCoursesByCourseSetId($courseSetId, $onlyPublished = true)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $members = $this->getMemberService()->findTeacherMembersByUserIdAndCourseSetId($user['id'], $courseSetId);
        $ids = ArrayToolkit::column($members, 'courseId');
        if ($onlyPublished) {
            return $this->findPublicCoursesByIds($ids);
        } else {
            return $this->findCoursesByIds($ids);
        }
    }

    public function findPriceIntervalByCourseSetIds($courseSetIds)
    {
        $results = $this->getCourseDao()->findPriceIntervalByCourseSetIds($courseSetIds);

        return ArrayToolkit::index($results, 'courseSetId');
    }

    public function tryTakeCourse($courseId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }
        if (!$this->canTakeCourse($course)) {
            $this->createNewException(CourseException::FORBIDDEN_TAKE_COURSE());
        }
        $user = $this->getCurrentUser();
        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'], $user['id']);

        return array($course, $member);
    }

    public function canTakeCourse($course)
    {
        $course = !is_array($course) ? $this->getCourse(intval($course)) : $course;

        if (empty($course)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($course['id'], $user['id']);

        if ($member && in_array($member['role'], array('teacher', 'student'))) {
            return true;
        }

        if ($user->hasPermission('admin_course_manage')) {
            return true;
        }

        return false;
    }

    public function canJoinCourse($id)
    {
        $course = $this->getCourse($id);
        $chain = $this->biz['course.join_chain'];

        if (empty($chain)) {
            $this->createNewException(CourseException::CHAIN_NOT_REGISTERED());
        }

        return $chain->process($course);
    }

    public function canLearnCourse($id)
    {
        $course = $this->getCourse($id);
        $chain = $this->biz['course.learn_chain'];

        if (empty($chain)) {
            $this->createNewException(CourseException::CHAIN_NOT_REGISTERED());
        }

        return $chain->process($course);
    }

    public function canLearnTask($taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $chain = $this->biz['course.task.learn_chain'];

        if (empty($chain)) {
            $this->createNewException(CourseException::CHAIN_NOT_REGISTERED());
        }

        return $chain->process($task);
    }

    public function sortCourseItems($courseId, $ids)
    {
        $course = $this->tryManageCourse($courseId);
        try {
            $this->beginTransaction();
            $this->createCourseStrategy($course)->accept(new CourseItemSortingVisitor($this->biz, $courseId, $ids));
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function createChapter($chapter)
    {
        if (!in_array($chapter['type'], CourseToolkit::getAvailableChapterTypes())) {
            $this->createNewException(CourseException::CHAPTERTYPE_INVALID());
        }

        $chapter = $this->getChapterDao()->create($chapter);

        $this->dispatchEvent('course.chapter.create', new Event($chapter));

        return $chapter;
    }

    public function updateChapter($courseId, $chapterId, $fields)
    {
        $this->tryManageCourse($courseId);
        $chapter = $this->getChapterDao()->get($chapterId);

        if (empty($chapter) || $chapter['courseId'] != $courseId) {
            $this->createNewException(CourseException::NOTFOUND_CHAPTER());
        }

        $fields = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));

        $chapter = $this->getChapterDao()->update($chapterId, $fields);
        $this->dispatchEvent('course.chapter.update', new Event($chapter));

        return $chapter;
    }

    public function findChaptersByCourseId($courseId)
    {
        return $this->getChapterDao()->findChaptersByCourseId($courseId);
    }

    public function deleteChapter($courseId, $chapterId)
    {
        $this->tryManageCourse($courseId);

        $deletedChapter = $this->getChapterDao()->get($chapterId);

        if (empty($deletedChapter)) {
            return;
        }

        if ($deletedChapter['courseId'] != $courseId) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->getChapterDao()->delete($deletedChapter['id']);

        if ('lesson' == $deletedChapter['type']) {
            $this->getTaskService()->deleteTasksByCategoryId($courseId, $deletedChapter['id']);
        }

        $this->dispatchEvent('course.chapter.delete', new Event($deletedChapter));
    }

    public function getChapter($courseId, $chapterId)
    {
        $chapter = $this->getChapterDao()->get($chapterId);
        $course = $this->getCourseDao()->get($courseId);
        if ($course['id'] == $chapter['courseId']) {
            return $chapter;
        }

        return array();
    }

    public function countUserLearningCourses($userId, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        return $this->getMemberDao()->countLearningMembers($conditions);
    }

    public function findUserLearningCourses($userId, $start, $limit, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        $members = $this->getMemberDao()->findLearningMembers($conditions, $start, $limit);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 0;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserLearnCourseIds($userId)
    {
        $courseIds = $this->getMemberDao()->findUserLearnCourseIds($userId);

        return ArrayToolkit::column($courseIds, 'courseId');
    }

    public function countUserLearnCourses($userId)
    {
        return $this->getMemberDao()->countUserLearnCourses($userId);
    }

    // Refactor: countLearnedCourses
    public function countUserLearnedCourses($userId, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);

        return $this->getMemberDao()->countLearnedMembers($conditions);
    }

    // Refactor: findLearnedCourses
    public function findUserLearnedCourses($userId, $start, $limit, $filters = array())
    {
        $conditions = $this->prepareUserLearnCondition($userId, $filters);
        $members = $this->getMemberDao()->findLearnedMembers($conditions, $start, $limit);

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 1;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    // Refactor: countTeachingCourses
    // 1、看是否应该改成：countTeachingCourseByUserId($userId, $onlyPublished = true)
    // 2、若参数列表保持原有，则需要校验必填参数conditions中是否包含userId
    public function findUserTeachCourseCount($conditions, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findByUserIdAndRole($conditions['userId'], 'teacher');
        unset($conditions['userId']);

        if (!$members) {
            return 0;
        }

        $conditions['courseIds'] = ArrayToolkit::column($members, 'courseId');

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourseCount($conditions);
    }

    // Refactor: findTeachingCoursesByUserId
    // 1、看是否应该改成：findTeachingCoursesByUserId($userId, $onlyPublished = true)
    // 2、若参数列表保持原有，则需要校验必填参数conditions中是否包含userId
    public function findUserTeachCourses($conditions, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findByUserIdAndRole($conditions['userId'], 'teacher');
        unset($conditions['userId']);

        if (!$members) {
            return array();
        }

        $conditions['courseIds'] = ArrayToolkit::column($members, 'courseId');

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourses($conditions, array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->findLearnedByCourseIdAndUserId($courseId, $userId);
    }

    public function findTeachingCoursesByUserId($userId, $onlyPublished = true)
    {
        $members = $this->getMemberService()->findTeacherMembersByUserId($userId);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        if ($onlyPublished) {
            $courses = $this->findPublicCoursesByIds($courseIds);
        } else {
            $courses = $this->findCoursesByIds($courseIds);
        }

        return $courses;
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function findLearnCoursesByUserId($userId)
    {
        $members = $this->getMemberService()->findStudentMemberByUserId($userId);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->findPublicCoursesByIds($courseIds);

        return $courses;
    }

    public function findPublicCoursesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $conditions = array(
            'status' => 'published',
            'courseIds' => $ids,
        );
        $count = $this->searchCourseCount($conditions);

        return $this->searchCourses($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    public function hasCourseManagerRole($courseId = 0)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        $course = $this->getCourse($courseId);
        //课程不存在，无权限管理
        if (empty($course)) {
            return false;
        }

        if ($course['creator'] == $user->getId()) {
            return true;
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ($user->getId() == $courseSet['creator']) {
            return true;
        }

        $teacher = $this->getMemberService()->isCourseTeacher($courseId, $user->getId());
        //不是课程教师，无权限管理
        if ($teacher) {
            return true;
        }

        if ($course['parentId'] > 0) {
            $classroomRef = $this->getClassroomService()->getClassroomCourseByCourseSetId($course['courseSetId']);
            if (!empty($classroomRef)) {
                $isTeacher = $this->getClassroomService()->isClassroomTeacher(
                    $classroomRef['classroomId'],
                    $user['id']
                );
                $isHeadTeacher = $this->getClassroomService()->isClassroomHeadTeacher(
                    $classroomRef['classroomId'],
                    $user['id']
                );
                if ($isTeacher || $isHeadTeacher) {
                    return true;
                }
            }
        }

        return false;
    }

    // Refactor: 函数命名
    public function analysisCourseDataByTime($startTime, $endTime)
    {
        return $this->getCourseDao()->analysisCourseDataByTime($startTime, $endTime);
    }

    public function findUserManageCoursesByCourseSetId($userId, $courseSetId)
    {
        $user = $this->getUserService()->getUser($userId);

        $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $user['roles']);
        $isAdmin = in_array('ROLE_ADMIN', $user['roles']);

        $courses = array();
        if ($isSuperAdmin || $isAdmin) {
            $courses = $this->findCoursesByCourseSetId($courseSetId);
        } elseif (in_array('ROLE_TEACHER', $user['roles'])) {
            $courses = $this->findUserTeachingCoursesByCourseSetId($courseSetId, false);
        }

        return $courses ? ArrayToolkit::index($courses, 'id') : array();
    }

    protected function fillMembersWithUserInfo($members)
    {
        if (empty($members)) {
            return $members;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        $user = $this->getUserService()->findUsersByIds($userIds);
        $userMap = ArrayToolkit::index($user, 'id');
        foreach ($members as $index => $member) {
            $member['nickname'] = $userMap[$member['userId']]['nickname'];
            $member['smallAvatar'] = $userMap[$member['userId']]['smallAvatar'];
            $members[$index] = $member;
        }

        return $members;
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if (0 == $value) {
                    return true;
                }

                return !empty($value);
            }
        );

        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday' => array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today' => array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['categoryId'])) {
            $conditions['categoryIds'] = array();

            if (!empty($conditions['categoryId'])) {
                $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
                $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            }

            unset($conditions['categoryId']);
        }

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function searchCourses($conditions, $sort, $start, $limit, $columns = array())
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        $orderBy = $this->_prepareCourseOrderBy($sort);

        return $this->getCourseDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchWithJoinCourseSet($conditions, $sort, $start, $limit, $columns = array())
    {
        $conditions = $this->_prepareCourseConditions($conditions);
        $orderBy = $this->_prepareCourseOrderBy($sort);

        return $this->getCourseDao()->searchWithJoinCourseSet($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchBySort($conditions, $sort, $start, $limit)
    {
        if (array_key_exists('studentNum', $sort) && array_key_exists('outerEndTime', $conditions)) {
            return $this->searchByStudentNumAndTimeZone($conditions, $start, $limit);
        }

        if (array_key_exists('rating', $sort) && array_key_exists('outerEndTime', $conditions)) {
            return $this->searchByRatingAndTimeZone($conditions, $start, $limit);
        }

        if (array_key_exists('recommendedSeq', $sort)) {
            $sort = array_merge($sort, array('recommendedTime' => 'DESC', 'id' => 'DESC'));

            return $this->searchByRecommendedSeq($conditions, $sort, $start, $limit);
        }

        return $this->searchWithJoinCourseSet($conditions, $sort, $start, $limit);
    }

    public function searchByStudentNumAndTimeZone($conditions, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getCourseDao()->searchByStudentNumAndTimeZone($conditions, $start, $limit);
    }

    public function searchByRatingAndTimeZone($conditions, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getCourseDao()->searchByRatingAndTimeZone($conditions, $start, $limit);
    }

    // Refactor: 该函数是否和getMinPublishedCoursePriceByCourseSetId冲突
    public function getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId);
    }

    //移动端接口使用
    public function findCourseTasksAndChapters($courseId)
    {
        $course = $this->getCourse($courseId);
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        $items = $this->convertTasks($tasks, $course);

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);

        foreach ($chapters as $chapter) {
            if ('lesson' == $chapter['type']) {
                continue;
            }
            $chapter['itemType'] = 'chapter';
            $items[] = $chapter;
        }
        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        return $items;
    }

    //移动端接口使用　task 转成lesson
    public function convertTasks($tasks, $course)
    {
        if (empty($tasks)) {
            return array();
        }

        $defaultTask = array(
            'giveCredit' => 0,
            'requireCredit' => 0,
            'materialNum' => 0,
            'quizNum' => 0,
            'viewedNum' => 0,
            'replayStatus' => 'ungenerated',
            'liveProvider' => 0,
            'testMode' => 'normal',
            'testStartTime' => 0,
            'summary' => $course['summary'],
            'exerciseId' => 0,
            'homeworkId' => 0,
            'mediaUri' => '',
            'mediaSource' => '',
        );

        if (empty($course['summary'])) {
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            $defaultTask['summary'] = $courseSet['summary'];
        }

        $transformKeys = array(
            'isFree' => 'free',
            'createdUserId' => 'userId',
            'categoryId' => 'chapterId',
        );

        $items = array();
        $lessons = array();
        $number = 0;

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');

        foreach ($tasks as $task) {
            if ($this->isUselessTask($task, $course['type'])) {
                continue;
            }
            $task = array_merge($task, $defaultTask);
            $task['itemType'] = 'lesson';
            $task['number'] = ++$number;
            if ('doc' == $task['type']) {
                $task['type'] = 'document';
            }
            foreach ($transformKeys as $key => $value) {
                $task[$value] = $task[$key];
            }
            $activity = $activities[$task['activityId']];
            $task = $this->filledTaskByActivity($task, $activity);
            $task['learnedNum'] = $this->getTaskResultService()->countTaskResults(
                array(
                    'courseTaskId' => $task['id'],
                    'status' => 'finish',
                )
            );
            $task['memberNum'] = $this->getTaskResultService()->countTaskResults(
                array(
                    'courseTaskId' => $task['id'],
                )
            );

            $task['content'] = $activity['content'];
            $lessons[] = $this->filterTask($task);
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($course['id']);

        $chapterNumber = array(
            'unit' => 0,
            'lesson' => 0,
            'chapter' => 0,
        );

        foreach ($chapters as $chapter) {
            $chapter['itemType'] = 'chapter';
            $chapter['number'] = ++$chapterNumber[$chapter['type']];
            $items[] = $chapter;
        }
        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        return $lessons;
    }

    //移动端 数字转字符
    protected function filterTask($task)
    {
        array_walk(
            $task,
            function ($value, $key) use (&$task) {
                if (is_numeric($value)) {
                    $task[$key] = (string) $value;
                } else {
                    $task[$key] = $value;
                }
            }
        );

        return $task;
    }

    private function isUselessTask($task, $courseType)
    {
        $lessonTypes = array(
            'testpaper',
            'video',
            'audio',
            'text',
            'flash',
            'ppt',
            'doc',
            'live',
        );

        if ('live' == $courseType) {
            $lessonTypes = array('live', 'testpaper');
        }

        if (!in_array($task['type'], $lessonTypes)) {
            return true;
        }

        return false;
    }

    private function filledTaskByActivity($task, $activity)
    {
        $task['mediaId'] = isset($activity['ext']['mediaId']) ? $activity['ext']['mediaId'] : 0;

        if ('video' == $task['type']) {
            $task['mediaSource'] = $activity['ext']['mediaSource'];
            $task['mediaUri'] = $activity['ext']['mediaUri'];
        } elseif ('audio' == $task['type']) {
            $task['mediaSource'] = 'self';
            $task['hasText'] = $activity['ext']['hasText'] ? true : false;
            $task['mediaText'] = $activity['ext']['hasText'] ? $activity['content'] : '';
        } elseif ('live' == $task['type']) {
            if ('videoGenerated' == $activity['ext']['replayStatus']) {
                $task['mediaSource'] = 'self';
            }

            $task['liveProvider'] = $activity['ext']['liveProvider'];
            $task['replayStatus'] = $activity['ext']['replayStatus'];
        }

        return $task;
    }

    public function findUserLearningCourseCountNotInClassroom($userId, $filters = array())
    {
        if (isset($filters['type'])) {
            return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned(
                $userId,
                'student',
                $filters['type'],
                0
            );
        }

        return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndRoleAndIsLearned($userId, 'student', 0, true);
    }

    //过滤约排课
    public function findUserLearningCoursesNotInClassroom($userId, $start, $limit, $filters = array())
    {
        if (isset($filters['type'])) {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned(
                $userId,
                'student',
                $filters['type'],
                '0',
                $start,
                $limit
            );
        } else {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndIsLearned(
                $userId,
                'student',
                0,
                $start,
                $limit,
                true
            );
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 0;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserLeanedCourseCount($userId, $filters = array())
    {
        if (isset($filters['type'])) {
            return $this->getMemberDao()->countMemberByUserIdAndCourseTypeAndIsLearned(
                $userId,
                'student',
                $filters['type'],
                1
            );
        }

        return $this->getMemberDao()->countMemberByUserIdAndRoleAndIsLearned($userId, 'student', 1);
    }

    public function findUserLearnedCoursesNotInClassroom($userId, $start, $limit, $filters = array())
    {
        if (isset($filters['type'])) {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned(
                $userId,
                'student',
                $filters['type'],
                1,
                $start,
                $limit
            );
        } else {
            $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndIsLearned(
                $userId,
                'student',
                1,
                $start,
                $limit,
                true
            );
        }

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        $sortedCourses = array();

        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }

            $course = $courses[$member['courseId']];
            $course['memberIsLearned'] = 1;
            $course['memberLearnedNum'] = $member['learnedNum'];
            $sortedCourses[] = $course;
        }

        return $sortedCourses;
    }

    public function findUserLearnCourseCountNotInClassroom($userId, $onlyPublished = true, $filterReservation = false)
    {
        return $this->getMemberDao()->countMemberNotInClassroomByUserIdAndRole($userId, 'student', $onlyPublished, $filterReservation);
    }

    public function findUserLearnCoursesNotInClassroom($userId, $start, $limit, $onlyPublished = true, $filterReservation = false)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole(
            $userId,
            'student',
            $start,
            $limit,
            $onlyPublished,
            $filterReservation
        );

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        return $courses;
    }

    public function findUserLearnCoursesNotInClassroomWithType($userId, $type, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRoleAndType(
            $userId,
            'student',
            $type,
            $start,
            $limit,
            $onlyPublished
        );

        $courses = $this->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        return $courses;
    }

    public function findUserTeachCourseCountNotInClassroom($conditions, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole(
            $conditions['userId'],
            'teacher',
            0,
            PHP_INT_MAX,
            $onlyPublished
        );
        unset($conditions['userId']);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $conditions['courseIds'] = $courseIds;

        if (0 == count($courseIds)) {
            return 0;
        }

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        return $this->searchCourseCount($conditions);
    }

    public function findUserTeachCoursesNotInClassroom($conditions, $start, $limit, $onlyPublished = true)
    {
        $members = $this->getMemberDao()->findMembersNotInClassroomByUserIdAndRole(
            $conditions['userId'],
            'teacher',
            $start,
            $limit,
            $onlyPublished
        );
        unset($conditions['userId']);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $conditions['courseIds'] = $courseIds;

        if (0 == count($courseIds)) {
            return array();
        }

        if ($onlyPublished) {
            $conditions['status'] = 'published';
        }

        $courses = $this->searchCourses($conditions, 'latest', 0, PHP_INT_MAX);

        return $courses;
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoritedCourseCountNotInClassroom($userId)
    {
        $courseFavorites = $this->getFavoriteDao()->findCourseFavoritesNotInClassroomByUserId($userId, 0, PHP_INT_MAX);
        $courseIds = ArrayToolkit::column($courseFavorites, 'courseId');
        $conditions = array('courseIds' => $courseIds, 'excludeTypes' => array('reservation'));

        if (0 == count($courseIds)) {
            return 0;
        }

        return $this->searchCourseCount($conditions);
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoritedCoursesNotInClassroom($userId, $start, $limit)
    {
        $courseFavorites = $this->getFavoriteDao()->findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit);
        $favoriteCourses = $this->getCourseDao()->search(
            array(
                'ids' => ArrayToolkit::column($courseFavorites, 'courseId'),
                'excludeTypes' => array('reservation'),
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        return $favoriteCourses;
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType, $start, $limit)
    {
        $coursesIds = $this->getFavoriteDao()->findUserFavoriteCoursesNotInClassroomWithCourseType(
            $userId,
            $courseType,
            $start,
            $limit
        );

        $courses = $this->findCoursesByIds(ArrayToolkit::column($coursesIds, 'id'));

        return $courses;
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function countUserFavoriteCourseNotInClassroomWithCourseType($userId, $courseType)
    {
        return $this->getFavoriteDao()->countUserFavoriteCoursesNotInClassroomWithCourseType(
            $userId,
            $courseType
        );
    }

    public function unlockCourse($courseId)
    {
        $course = $this->getCourseDao()->update($courseId, array('locked' => 0));

        $this->dispatchEvent('course.update', new Event($course));

        return $course;
    }

    protected function _prepareCourseOrderBy($sort)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ('popular' == $sort || 'hitNum' == $sort) {
            $orderBy = array('hitNum' => 'DESC');
        } elseif ('recommended' == $sort) {
            $orderBy = array('recommendedTime' => 'DESC');
        } elseif ('rating' == $sort) {
            $orderBy = array('rating' => 'DESC');
        } elseif ('studentNum' == $sort) {
            $orderBy = array('studentNum' => 'DESC');
        } elseif ('recommendedSeq' == $sort) {
            $orderBy = array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC');
        } elseif ('createdTimeByAsc' == $sort) {
            $orderBy = array('createdTime' => 'ASC');
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        return $orderBy;
    }

    public function searchCourseCount($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getCourseDao()->count($conditions);
    }

    public function countWithJoinCourseSet($conditions)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getCourseDao()->countWithJoinCourseSet($conditions);
    }

    public function countCourses(array $conditions)
    {
        return $this->getCourseDao()->count($conditions);
    }

    public function countCoursesByCourseSetId($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
        );

        return $this->getCourseDao()->count($conditions);
    }

    public function countCoursesGroupByCourseSetIds($courseSetIds)
    {
        return $this->getCourseDao()->countGroupByCourseSetIds($courseSetIds);
    }

    public function getFavoritedCourseByUserIdAndCourseSetId($userId, $courseSetId)
    {
        return $this->getFavoriteDao()->getByUserIdAndCourseSetId($userId, $courseSetId);
    }

    public function appendReservationConditions($conditions)
    {
        if (!$this->getSettingService()->isReservationOpen()) {
            if (empty($conditions['excludeTypes'])) {
                $conditions['excludeTypes'] = array();
            }
            $conditions['excludeTypes'][] = 'reservation';
        }

        return $conditions;
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->biz['course.strategy_context']->createStrategy($course['courseType']);
    }

    public function calculateLearnProgressByUserIdAndCourseIds($userId, array $courseIds)
    {
        if (empty($userId) || empty($courseIds)) {
            return array();
        }
        $courses = $this->findCoursesByIds($courseIds);

        $conditions = array(
            'courseIds' => $courseIds,
            'userId' => $userId,
        );
        $count = $this->getMemberService()->countMembers($conditions);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            $count
        );

        $learnProgress = array();
        foreach ($members as $member) {
            $learnProgress[] = array(
                'courseId' => $member['courseId'],
                'totalLesson' => $courses[$member['courseId']]['taskNum'],
                'learnedNum' => $member['learnedNum'],
            );
        }

        return $learnProgress;
    }

    public function buildCourseExpiryDataFromClassroom($expiryMode, $expiryValue)
    {
        $fields = array();
        if ('forever' === $expiryMode) {
            $fields = array(
                'expiryMode' => 'forever',
                'expiryDays' => 0,
                'expiryStartDate' => null,
                'expiryEndDate' => null,
            );
        } elseif ('days' === $expiryMode) {
            if (0 == $expiryValue) {
                $fields = array(
                    'expiryMode' => 'forever',
                    'expiryDays' => 0,
                    'expiryStartDate' => null,
                    'expiryEndDate' => null,
                );
            } else {
                $fields = array(
                    'expiryMode' => 'days',
                    'expiryDays' => $expiryValue,
                    'expiryStartDate' => null,
                    'expiryEndDate' => null,
                );
            }
        } elseif ('date' === $expiryMode) {
            $fields = array(
                'expiryMode' => 'end_date',
                'expiryDays' => 0,
                'expiryStartDate' => null,
                'expiryEndDate' => $expiryValue,
            );
        }

        return $fields;
    }

    public function hitCourse($courseId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        return $this->getCourseDao()->wave(array($courseId), array('hitNum' => 1));
    }

    public function recountLearningData($courseId, $userId)
    {
        $member = $this->getMemberService()->getCourseMember($courseId, $userId);

        if (empty($member)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $learnedNum = $this->getTaskResultService()->countTaskResults(
            array('courseId' => $courseId, 'userId' => $userId, 'status' => 'finish')
        );

        $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);

        $this->getMemberService()->updateMember(
            $member['id'],
            array('learnedNum' => $learnedNum, 'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum)
        );
    }

    public function tryFreeJoin($courseId)
    {
        $access = $this->canJoinCourse($courseId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            throw new UnableJoinException($access['msg'], $access['code']);
        }

        $course = $this->getCourse($courseId);

        if ((1 == $course['isFree'] || 0 == $course['originPrice']) && $course['buyable']) {
            $this->getMemberService()->becomeStudent($course['id'], $this->getCurrentUser()->getId());
        }
        $this->dispatch('course.try_free_join', $course);
    }

    public function findLiveCourse($conditions, $userId, $role)
    {
        $liveCourses = array();
        $tasks = $this->getTaskService()->searchTasks(
            array('type' => 'live', 'startTime_GE' => $conditions['startTime_GE'], 'endTime_LT' => $conditions['endTime_LT'], 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );
        foreach ($tasks as $task) {
            $members = $this->getMemberDao()->search(array('courseId' => $task['courseId'], 'role' => $role), array(), 0, PHP_INT_MAX);
            $userIds = ArrayToolkit::column($members, 'userId');
            if (empty($userIds) || !in_array($userId, $userIds)) {
                continue;
            }
            $course = $this->getCourse($task['courseId']);
            if (!empty($course) && 'published' == $course['status']) {
                $courseSet = $this->getCourseSetDao()->get($course['courseSetId']);
                if (!empty($courseSet) && 'published' == $courseSet['status']) {
                    $liveCourse = array(
                        'title' => $courseSet['title'],
                        'courseId' => $task['courseId'],
                        'taskId' => $task['id'],
                        'event' => $courseSet['title'].'-'.$course['title'].'-'.$task['title'],
                        'startTime' => date('Y-m-d H:i:s', $task['startTime']),
                        'endTime' => date('Y-m-d H:i:s', $task['endTime']),
                        'date' => date('w', $task['startTime']),
                    );
                    array_push($liveCourses, $liveCourse);
                }
            }
        }

        return $liveCourses;
    }

    public function fillCourseTryLookVideo($courses)
    {
        if (!empty($courses)) {
            $tryLookAbleCourses = array_filter($courses, function ($course) {
                return !empty($course['tryLookable']) && 'published' === $course['status'];
            });
            $tryLookAbleCourseIds = ArrayToolkit::column($tryLookAbleCourses, 'id');
            $activities = $this->getActivityService()->findActivitySupportVideoTryLook($tryLookAbleCourseIds);
            $activityIds = ArrayToolkit::column($activities, 'id');
            $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);
            $tasks = ArrayToolkit::index($tasks, 'activityId');

            $activities = array_filter($activities, function ($activity) use ($tasks) {
                return 'published' === $tasks[$activity['id']]['status'];
            });
            //返回有云视频任务的课程
            $activities = ArrayToolkit::index($activities, 'fromCourseId');

            foreach ($courses as &$course) {
                if (!empty($activities[$course['id']])) {
                    $course['tryLookVideo'] = 1;
                } else {
                    $course['tryLookVideo'] = 0;
                }
            }
            unset($course);
        }

        return $courses;
    }

    public function searchByRecommendedSeq($conditions, $sort, $offset, $limit)
    {
        $conditions['recommended'] = 1;
        $recommendCount = $this->countWithJoinCourseSet($conditions);
        $recommendAvailable = $recommendCount - $offset;
        $courses = array();

        if ($recommendAvailable >= $limit) {
            $courses = $this->searchWithJoinCourseSet(
                $conditions,
                $sort,
                $offset,
                $limit
            );
        }

        if ($recommendAvailable <= 0) {
            $conditions['recommended'] = 0;
            $courses = $this->searchWithJoinCourseSet(
                $conditions,
                array('createdTime' => 'DESC'),
                abs($recommendAvailable),
                $limit
            );
        }

        if ($recommendAvailable > 0 && $recommendAvailable < $limit) {
            $courses = $this->searchWithJoinCourseSet(
                $conditions,
                $sort,
                $offset,
                $recommendAvailable
            );
            $conditions['recommended'] = 0;
            $coursesTemp = $this->searchWithJoinCourseSet(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $limit - $recommendAvailable
            );
            $courses = array_merge($courses, $coursesTemp);
        }

        return $courses;
    }

    /**
     * @deprecated 即将废弃，不要使用：函数名不合理；本质上静态函数不需要写到业务层
     */
    public function sortByCourses($courses)
    {
        usort($courses, function ($a, $b) {
            if ($a['seq'] == $b['seq']) {
                return 0;
            }

            return $a['seq'] < $b['seq'] ? 1 : -1;
        });

        return $courses;
    }

    public function countCourseItems($course)
    {
        $chapterConditions = array(
            'courseId' => $course['id'],
            'types' => CourseToolkit::getUserDisplayedChapterTypes(),
        );
        $chapterUnitCount = $this->getChapterDao()->count($chapterConditions);

        return $chapterUnitCount + $course['compulsoryTaskNum'];
    }

    public function sortCourse($courseSetId, $ids)
    {
        if (empty($ids)) {
            return;
        }

        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $count = $this->searchCourseCount(
            array(
                'courseSetId' => $courseSetId,
                'courseIds' => $ids,
            )
        );

        if (count($ids) != $count) {
            $this->createNewException(CourseException::NOT_MATCH_COURSESET());
        }

        $seq = 1;
        foreach ($ids as $id) {
            $fields[] = array(
                'seq' => $seq++,
            );
        }
        $this->getCourseDao()->batchUpdate($ids, $fields, 'id');
        $this->dispatch('courseSet.courses.sort', new Event($courseSet, array(
            'courseIds' => $ids,
        )));
    }

    public function changeHidePublishLesson($courseId, $status)
    {
        $this->tryManageCourse($courseId);
        $course = $this->getCourseDao()->update($courseId, array('isHideUnpublish' => $status));
        $this->getLessonService()->updateLessonNumbers($courseId);
        $this->dispatch('course.change.showPublishLesson', new Event($course));
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();

        return $user->hasPermission('admin_course_content_manage');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Course:FavoriteDao');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return MaterialService
     */
    protected function getCourseMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    protected function getLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    /**
     * @return CourseDeleteService
     */
    protected function getCourseDeleteService()
    {
        return $this->createService('Course:CourseDeleteService');
    }

    /**
     * @return ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * 当默认值未设置时，合并默认值
     *
     * @param  $course
     *
     * @return array
     */
    protected function mergeCourseDefaultAttribute($course)
    {
        $course = array_filter(
            $course,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                return true;
            }
        );

        $default = array(
            'tryLookable' => 0,
            'originPrice' => 0.00,
        );

        return array_merge($default, $course);
    }

    /**
     * used for search userLearn userLearning userLearned.
     *
     * @param  $userId
     * @param  $filters
     *
     * @return array
     */
    protected function prepareUserLearnCondition($userId, $filters)
    {
        $filters = ArrayToolkit::parts($filters, array('type', 'classroomId', 'locked'));
        $conditions = array(
            'm.userId' => $userId,
            'm.role' => 'student',
        );
        if (!empty($filters['type'])) {
            $conditions['c.type'] = $filters['type'];
        }
        if (!empty($filters['classroomId'])) {
            $conditions['m.classroomId'] = $filters['classroomId'];
        }

        if (!empty($filters['locked'])) {
            $conditions['m.locked'] = $filters['locked'];
        }

        return $conditions;
    }

    /**
     * @param  $id
     * @param  $fields
     *
     * @return mixed
     */
    private function processFields($course, $fields, $courseSet)
    {
        if (in_array($course['status'], array('published', 'closed'))) {
            //计划发布或者关闭，不允许修改模式，但是允许修改时间
            unset($fields['expiryMode']);
            if ('published' == $courseSet['status'] && 'published' == $course['status']) {
                //计划发布后，不允许修改时间
                unset($fields['expiryDays']);
                unset($fields['expiryStartDate']);
                unset($fields['expiryEndDate']);
            }
        }

        if (isset($fields['originPrice'])) {
            list($fields['price'], $fields['coinPrice']) = $this->calculateCoursePrice($course['id'], $fields['originPrice']);
        }

        if (empty($fields['originPrice']) || $fields['originPrice'] <= 0) {
            $fields['isFree'] = 1;
        } else {
            $fields['isFree'] = 0;
        }

        if (empty($fields['tryLookLength'])) {
            $fields['tryLookLength'] = 0;
        }

        if ('normal' == $courseSet['type'] && 0 == $fields['tryLookLength']) {
            $fields['tryLookLength'] = 0;
            $fields['tryLookable'] = 0;
        } else {
            $fields['tryLookable'] = 1;
        }

        if (!empty($fields['buyExpiryTime'])) {
            if (is_numeric($fields['buyExpiryTime'])) {
                $fields['buyExpiryTime'] = date('Y-m-d', $fields['buyExpiryTime']);
            }

            $fields['buyExpiryTime'] = strtotime($fields['buyExpiryTime'].' 23:59:59');
        } else {
            $fields['buyExpiryTime'] = 0;
        }

        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        if (isset($fields['summary'])) {
            $fields['summary'] = $this->purifyHtml($fields['summary'], true);
        }

        if (!empty($fields['goals'])) {
            $fields['goals'] = json_decode($fields['goals'], true);
        }

        if (!empty($fields['audiences'])) {
            $fields['audiences'] = json_decode($fields['audiences'], true);
        }

        return $fields;
    }

    protected static function learnModes()
    {
        return array(
            static::FREE_LEARN_MODE,
            static::LOCK_LEARN_MODE,
        );
    }

    protected static function courseTypes()
    {
        return array(
            static::DEFAULT_COURSE_TYPE,
            static::NORMAL__COURSE_TYPE,
        );
    }

    public function canUpdateCourseBaseInfo($courseId, $courseSetId = 0)
    {
        $course = $this->getCourse($courseId);

        if ($courseSetId > 0 && $course['courseSetId'] !== $courseSetId) {
            $this->createNewException(CourseException::NOT_MATCH_COURSESET());
        }

        $user = $this->getCurrentUser();
        $courseSetting = $this->getSettingService()->get('course');

        if (!empty($courseSetting['teacher_manage_marketing']) && !empty($course['teacherIds']) && in_array($user['id'], $course['teacherIds'])) {
            return $course;
        }

        return $this->tryManageCourse($courseId, $courseSetId);
    }
}
