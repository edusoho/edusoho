<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Dao\FavoriteDao;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\CourseNoteService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class CourseSetServiceImpl extends BaseService implements CourseSetService
{
    /**
     * collect course set
     *
     * @param  $id
     * @throws AccessDeniedException
     * @return bool
     */
    public function favorite($id)
    {
        $courseSet = $this->getCourseSet($id);
        $user      = $this->getCurrentUser();

        if (empty($courseSet)) {
            return false;
        }

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('user is not log in');
        }

        $isFavorite = $this->isUserFavorite($user['id'], $courseSet['id']);

        if ($isFavorite) {
            return true;
        }

        $course = $this->getCourseService()->getFirstPublishedCourseByCourseSetId($courseSet['id']);

        if (empty($course)) {
            return false;
        }

        $favorite = array(
            'courseSetId' => $courseSet['id'],
            'type'        => 'course',
            'userId'      => $user['id'],
            'courseId'    => $course['id']
        );

        $favorite = $this->getFavoriteDao()->create($favorite);

        return !empty($favorite);
    }

    /**
     * cancel collected course set
     *
     * @param  $id
     * @throws AccessDeniedException
     * @return bool
     */
    public function unfavorite($id)
    {
        $courseSet = $this->getCourseSet($id);
        $user      = $this->getCurrentUser();

        if (empty($courseSet)) {
            return false;
        }

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('user is not log in');
        }

        $favorite = $this->getFavoriteDao()->getByUserIdAndCourseSetId($user['id'], $courseSet['id'], 'course');

        if (empty($favorite)) {
            return true;
        }

        $this->getFavoriteDao()->delete($favorite['id']);
        return true;
    }

    /**
     * @param  int    $userId
     * @param  int    $courseSetId
     * @return bool
     */
    public function isUserFavorite($userId, $courseSetId)
    {
        $courseSet = $this->getCourseSet($courseSetId);
        $favorite  = $this->getFavoriteDao()->getByUserIdAndCourseSetId($userId, $courseSet['id'], 'course');
        return !empty($favorite);
    }

    public function tryManageCourseSet($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        $courseSet = $this->getCourseSetDao()->get($id);

        if (empty($courseSet)) {
            throw $this->createNotFoundException("CourseSet#{$id} Not Found");
        }

        if (!$this->hasCourseSetManagerRole($id)) {
            throw $this->createAccessDeniedException("Unauthorized");
        }

        return $courseSet;
    }

    /**
     * @param  array   $conditions
     * @param  array   $orderBys
     * @param  int     $start
     * @param  int     $limit
     * @return mixed
     */
    public function searchCourseSets(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getCourseSetDao()->search($conditions, $orderBys, $start, $limit);
    }

    /**
     * @param  array   $conditions
     * @return mixed
     */
    public function countCourseSets(array $conditions)
    {
        return $this->getCourseSetDao()->count($conditions);
    }

    /**
     * @param  int       $userId
     * @return integer
     */
    public function countUserLearnCourseSets($userId)
    {
        $courses    = $this->getCourseService()->findLearnCoursesByUserId($userId);
        $courseSets = $this->findCourseSetsByCourseIds(ArrayToolkit::column($courses, 'id'));
        return count($courseSets);
    }

    /**
     * @param  int       $userId
     * @param  int       $start
     * @param  int       $limit
     * @return array[]
     */
    public function searchUserLearnCourseSets($userId, $start, $limit)
    {
        $sets = $this->findLearnCourseSetsByUserId($userId);
        $ids  = ArrayToolkit::column($sets, 'id');

        if (empty($ids)) {
            return array();
        }

        return $this->searchCourseSets(
            array(
                'ids'    => $ids,
                'status' => 'published'
            ),
            array(
                'createdTime' => 'DESC'
            ),
            $start,
            $limit
        );
    }

    public function countUserTeachingCourseSets($userId, array $conditions)
    {
        $members    = $this->getCourseMemberService()->findTeacherMembersByUserId($userId);
        $ids        = ArrayToolkit::column($members, 'courseSetId');
        $conditions = array_merge($conditions, array('ids' => $ids));
        return $this->countCourseSets($conditions);
    }

    public function searchUserTeachingCourseSets($userId, array $conditions, $start, $limit)
    {
        $members    = $this->getCourseMemberService()->findTeacherMembersByUserId($userId);
        $ids        = ArrayToolkit::column($members, 'courseSetId');
        $conditions = array_merge($conditions, array('ids' => $ids));
        return $this->searchCourseSets($conditions, array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findCourseSetsByCourseIds(array $courseIds)
    {
        $courses      = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $sets         = $this->findCourseSetsByIds(array_unique($courseSetIds));
        return $sets;
    }

    public function findCourseSetsByIds(array $ids)
    {
        $courseSets = $this->getCourseSetDao()->findByIds($ids);
        return ArrayToolkit::index($courseSets, 'id');
    }

    public function findCourseSetsLikeTitle($title)
    {
        return $this->getCourseSetDao()->findLikeTitle($title);
    }

    public function getCourseSet($id)
    {
        return $this->getCourseSetDao()->get($id);
    }

    public function createCourseSet($courseSet)
    {
        if (!$this->hasCourseSetManagerRole()) {
            throw $this->createAccessDeniedException('You have no access to Course Set Management');
        }
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException("Invalid Param: type");
        }

        $courseSet = ArrayToolkit::parts($courseSet, array(
            'type',
            'title'
        ));
        $courseSet['status']  = 'draft';
        $courseSet['creator'] = $this->getCurrentUser()->getId();
        $created              = $this->getCourseSetDao()->create($courseSet);

        // 同时创建默认的教学计划
        // XXX
        // 1. 是否创建默认教学计划应该是可配的；
        // 2. 教学计划的内容（主要是学习模式、有效期模式）也应该是可配的
        $defaultCourse = array(
            'courseSetId' => $created['id'],
            'title'       => '默认教学计划',
            'expiryMode'  => 'days',
            'expiryDays'  => 0,
            'learnMode'   => 'freeMode',
            'isDefault'   => 1,
            'status'      => 'draft'
        );

        $course['creator'] = $this->getCurrentUser()->getId();
        $this->getCourseService()->createCourse($defaultCourse);

        return $created;
    }

    public function updateCourseSet($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'categoryId', 'serializeMode'))) {
            throw $this->createInvalidArgumentException("Lack of required fields");
        }
        if (!in_array($fields['serializeMode'], array('none', 'serialized', 'finished'))) {
            throw $this->createInvalidArgumentException("Invalid Param: serializeMode");
        }

        $courseSet = $this->tryManageCourseSet($id);

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'subtitle',
            'tags',
            'categoryId',
            'serializeMode',
            // 'summary',
            'smallPicture',
            'middlePicture',
            'largePicture'
        ));

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function (&$item, $key) {
                $item = (int) $item['id'];
            });
        }
        return $this->getCourseSetDao()->update($courseSet['id'], $fields);
    }

    public function updateCourseSetDetail($id, $fields)
    {
        $courseSet = $this->tryManageCourseSet($id);

        $fields = ArrayToolkit::parts($fields, array(
            'summary',
            'goals',
            'audiences'
        ));

        return $this->getCourseSetDao()->update($courseSet['id'], $fields);
    }

    public function changeCourseSetCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            throw $this->createInvalidArgumentException("Invalid Param: cover");
        }
        $courseSet = $this->tryManageCourseSet($id);
        $covers    = array();
        foreach ($coverArray as $cover) {
            $file                   = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        return $this->getCourseSetDao()->update($courseSet['id'], array('cover' => $covers));
    }

    public function deleteCourseSet($id)
    {
        //TODO
        //1. 判断该课程能否被删除
        //2. 删除时需级联删除课程下的教学计划、用户信息等等
        $courseSet = $this->tryManageCourseSet($id);
        return $this->getCourseSetDao()->delete($courseSet['id']);
    }

    public function findTeachingCourseSetsByUserId($userId, $onlyPublished = true)
    {
        $courses = $this->getCourseService()->findTeachingCoursesByUserId($userId, $onlyPublished);
        $setIds  = ArrayToolkit::column($courses, 'courseSetId');

        if ($onlyPublished) {
            return $this->findPublicCourseSetsByIds($setIds);
        } else {
            return $this->findCourseSetsByIds($setIds);
        }
    }

    /**
     * @param  int     $userId
     * @return mixed
     */
    public function findLearnCourseSetsByUserId($userId)
    {
        $courses = $this->getCourseService()->findLearnCoursesByUserId($userId);
        $setIds  = ArrayToolkit::column($courses, 'courseSetId');
        return $this->findPublicCourseSetsByIds($setIds);
    }

    /**
     * @param  array   $ids
     * @return mixed
     */
    public function findPublicCourseSetsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $conditions = array(
            'ids'    => $ids,
            'status' => 'published'
        );
        $count = $this->countCourseSets($conditions);
        return $this->searchCourseSets($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    public function updateCourseSetStatistics($id, array $fields)
    {
        if (empty($fields)) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $updateFields = array();
        foreach ($fields as $field) {
            if ($field === 'ratingNum') {
                $ratingFields = $this->getReviewService()->countRatingByCourseSetId($id);
                $updateFields = array_merge($updateFields, $ratingFields);
            }

            if ($field === 'noteNum') {
                $noteNum                 = $this->getNoteService()->countCourseNoteByCourseSetId($id);
                $updateFields['noteNum'] = $noteNum;
            }

            if ($field === 'studentNum') {
                $updateFields['studentNum'] = $this->countStudentNumById($id);
            }
        }

        return $this->getCourseSetDao()->update($id, $updateFields);
    }

    public function publishCourseSet($id)
    {
        $courseSet = $this->tryManageCourseSet($id);
        $this->getCourseSetDao()->update($courseSet['id'], array('status' => 'published'));
        $this->dispatchEvent('course-set.publish', $courseSet);
    }

    public function closeCourseSet($id)
    {
        $courseSet = $this->tryManageCourseSet($id);
        if ($courseSet['status'] != 'published') {
            throw $this->createAccessDeniedException('CourseSet has not bean published');
        }
        $this->getCourseSetDao()->update($courseSet['id'], array('status' => 'closed'));
        $this->dispatchEvent('course-set.closed', $courseSet);
    }

    /**
     * @param  int       $userId
     * @return integer
     */
    public function countUserFavorites($userId)
    {
        return $this->getFavoriteDao()->countByUserId($userId);
    }

    /**
     * @param  int       $userId
     * @param  int       $start
     * @param  int       $limit
     * @return array[]
     */
    public function searchUserFavorites($userId, $start, $limit)
    {
        return $this->getFavoriteDao()->searchByUserId($userId, $start, $limit);
    }

    protected function hasCourseSetManagerRole($courseSetId = 0)
    {
        $userId = $this->getCurrentUser()->getId();
        //TODO
        //1. courseSetId为空，判断是否有创建课程的权限
        //2. courseSetId不为空，判断是否有该课程的管理权限
        return true;
    }

    protected function validateCourseSet($courseSet)
    {
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException("Lack of Required Fields");
        }
        if (!in_array($courseSet['type'], array('normal', 'live', 'liveOpen', 'open'))) {
            throw $this->createInvalidArgumentException("Invalid Param: type");
        }
    }

    protected function countStudentNumById($id)
    {
        $courseSet = $this->getCourseSet($id);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
        return array_reduce($courses, function ($studentNum, $course) {
            $studentNum += $course['studentNum'];
            return $studentNum;
        });
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getTagService()
    {
        return $this->biz->service('Taxonomy:TagService');
    }

    protected function getReviewService()
    {
        return $this->biz->service('Course:ReviewService');
    }

    protected function getFileService()
    {
        return $this->biz->service('Content:FileService');
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->biz->dao('Course:FavoriteDao');
    }
}
