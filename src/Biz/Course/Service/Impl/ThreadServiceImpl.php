<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Dao\ThreadDao;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\LogService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\Util\TextHelper;
use Codeages\Biz\Framework\Event\Event;
use Biz\User\Service\NotificationService;
use Biz\Course\Dao\Impl\ThreadPostDaoImpl;
use Biz\Sensitive\Service\SensitiveService;
use Biz\Course\ThreadException;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function countThreads($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->count($conditions);
    }

    public function searchThreads($conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function getThreadByThreadId($threadId)
    {
        $thread = $this->getThreadDao()->get($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        return $thread;
    }

    public function getThread($courseId, $threadId)
    {
        $thread = $this->getThreadDao()->get($threadId);
        if (!empty($thread) && !empty($courseId) && $thread['courseId'] != $courseId) {
            $this->createNewException(ThreadException::NOT_MATCH_COURSE());
        }

        return $thread;
    }

    public function findThreadIds($conditions)
    {
        $threadIds = $threadIds = $this->getThreadDao()->findThreadIds($conditions);

        return ArrayToolkit::column($threadIds, 'id');
    }

    public function findPostThreadIds($conditions)
    {
        $postThreadIds = $this->getThreadPostDao()->findThreadIds($conditions);

        return ArrayToolkit::column($postThreadIds, 'threadId');
    }

    public function countPartakeThreadsByUserId($userId)
    {
        $threadIds = $this->findThreadIds(array('userId' => $userId));
        $postThreadIds = $this->findPostThreadIds(array('userId' => $userId));

        return count(array_unique(array_merge($threadIds, $postThreadIds)));
    }

    public function findThreadsByType($courseId, $type, $sort, $start, $limit)
    {
        if ('latestPosted' === $sort) {
            $orderBy = array('latestPosted' => 'DESC');
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        if (!in_array($type, array('question', 'discussion'))) {
            $type = 'all';
        }

        if ('all' === $type) {
            return $this->getThreadDao()->search(array('courseId' => $courseId), $orderBy, $start, $limit);
        }

        return $this->getThreadDao()->search(array('courseId' => $courseId, 'type' => $type), $orderBy, $start, $limit);
    }

    public function findLatestThreadsByType($type, $start, $limit)
    {
        return $this->getThreadDao()->search(array('type' => $type), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findEliteThreadsByType($type, $status, $start, $limit)
    {
        return $this->getThreadDao()->search(array('type' => $type, 'isElite' => $status), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function searchThreadCountInCourseIds($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->count($conditions);
    }

    public function searchThreadInCourseIds($conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function searchThreadPosts($conditions, $sort, $start, $limit)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ('createdTimeByAsc' === $sort) {
            $orderBy = array('createdTime' => 'ASC');
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        return $this->getThreadPostDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchThreadPostsCount($conditions)
    {
        return $this->getThreadPostDao()->count($conditions);
    }

    public function createThread($thread)
    {
        if (empty($thread['courseId'])) {
            $this->createNewException(ThreadException::COURSEID_REQUIRED());
        }

        $thread = $this->filterThread($thread);
        $trimedThreadTitle = empty($thread['title']) ? '' : trim($thread['title']);
        if (empty($trimedThreadTitle) && !isset($thread['source'])) {
            $this->createNewException(ThreadException::TITLE_REQUIRED());
        }

        if (empty($thread['type']) || !in_array($thread['type'], array('discussion', 'question'))) {
            $this->createNewException(ThreadException::TYPE_INVALID());
        }

        $event = $this->dispatchEvent('course.thread.before_create', $thread);

        if ($event->isPropagationStopped()) {
            $this->createNewException(\Biz\Thread\ThreadException::FORBIDDEN_TIME_LIMIT());
        }

        $thread['content'] = $this->sensitiveFilter($thread['content'], 'course-thread-create');
        $thread['title'] = $this->sensitiveFilter($thread['title'], 'course-thread-create');

        list($course, $member) = $this->getCourseService()->tryTakeCourse($thread['courseId']);

        $thread['userId'] = $this->getCurrentUser()->id;
        $thread['title'] = $this->biz['html_helper']->purify(empty($thread['title']) ? '' : $thread['title']);
        $thread['courseSetId'] = $course['courseSetId'];

        //if user can manage course, we trusted rich editor content
        $hasCourseManagerRole = $this->getCourseService()->hasCourseManagerRole($thread['courseId']);
        $trusted = empty($hasCourseManagerRole) ? false : true;
        //更新thread过滤html
        $thread['content'] = $this->biz['html_helper']->purify($thread['content'], $trusted);
        $thread['content'] = $this->filter_Emoji($thread['content']);
        $thread['title'] = $this->filter_Emoji($thread['title']);

        $thread['createdTime'] = time();
        $thread['latestPostUserId'] = $thread['userId'];
        $thread['latestPostTime'] = $thread['createdTime'];
        $thread['private'] = 'published' === $course['status'] ? 0 : 1;

        $thread = $this->getThreadDao()->create($thread);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        foreach ($course['teacherIds'] as $teacherId) {
            if ($teacherId == $thread['userId']) {
                continue;
            }

            if ('question' !== $thread['type']) {
                continue;
            }

            $this->getNotifiactionService()->notify($teacherId, 'thread', array(
                'threadId' => $thread['id'],
                'threadUserId' => $thread['userId'],
                'threadUserNickname' => $this->getCurrentUser()->nickname,
                'threadTitle' => !empty($thread['title']) ? $thread['title'] : $this->trans('course.thread.question_type.'.$thread['questionType']),
                'threadType' => $thread['type'],
                'courseId' => $course['id'],
                'courseTitle' => !empty($course['title']) ? $courseSet['title'].'-'.$course['title'] : $courseSet['title'],
            ));
        }

        $this->dispatchEvent('course.thread.create', new Event($thread));

        return $thread;
    }

    public function updateThread($courseId, $threadId, $fields)
    {
        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $fields['content'] = isset($fields['content']) ? $this->sensitiveFilter($fields['content'], 'course-thread-update') : $thread['content'];
        $fields['title'] = isset($fields['title']) ? $this->sensitiveFilter($fields['title'], 'course-thread-update') : $thread['title'];

        if ($this->getCurrentUser()->getId() != $thread['userId']) {
            $this->getCourseService()->tryManageCourse($thread['courseId'], 'admin_course_thread');
        }

        $fields = ArrayToolkit::parts($fields, array('title', 'content', 'askVideoThumbnail'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        //if user can manage course, we trusted rich editor content
        $hasCourseManagerRole = $this->getCourseService()->hasCourseManagerRole($courseId);
        $trusted = empty($hasCourseManagerRole) ? false : true;
        //更新thread过滤html
        $fields['content'] = isset($fields['content']) ? $this->biz['html_helper']->purify($fields['content'], $trusted) : $thread['content'];

        $thread['content'] = $this->filter_Emoji($thread['content']);
        $thread['title'] = $this->filter_Emoji($thread['title']);
        $thread = $this->getThreadDao()->update($threadId, $fields);
        $this->dispatchEvent('course.thread.update', new Event($thread));

        return $thread;
    }

    public function deleteThread($threadId)
    {
        $thread = $this->getThreadDao()->get($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }
        $this->getCourseService()->tryManageCourse($thread['courseId']);

        $this->getThreadPostDao()->deleteByThreadId($threadId);
        $this->getThreadDao()->delete($threadId);

        $this->dispatchEvent('course.thread.delete', new Event($thread));
    }

    public function stickThread($courseId, $threadId)
    {
        $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $thread = $this->getThreadDao()->update($thread['id'], array('isStick' => 1));

        $this->dispatchEvent('course.thread.stick', new Event($thread));
    }

    public function unstickThread($courseId, $threadId)
    {
        $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $thread = $this->getThreadDao()->update($thread['id'], array('isStick' => 0));

        $this->dispatchEvent('course.thread.unstick', new Event($thread));
    }

    public function eliteThread($courseId, $threadId)
    {
        $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $thread = $this->getThreadDao()->update($thread['id'], array('isElite' => 1));

        $this->dispatchEvent('course.thread.elite', new Event($thread));
    }

    public function uneliteThread($courseId, $threadId)
    {
        $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $thread = $this->getThreadDao()->update($thread['id'], array('isElite' => 0));

        $this->dispatchEvent('course.thread.unelite', new Event($thread));
    }

    public function hitThread($courseId, $threadId)
    {
        $thread = $this->getThread($courseId, $threadId);
        if (empty($thread)) {
            return;
        }

        $this->getThreadDao()->wave(array($threadId), array('hitNum' => +1));
    }

    public function findThreadPosts($courseId, $threadId, $sort, $start, $limit)
    {
        $thread = $this->getThread($courseId, $threadId);

        if (empty($thread)) {
            return array();
        }

        if ('best' === $sort) {
            $orderBy = array('score' => 'DESC');
        } elseif ('elite' === $sort) {
            $orderBy = array('createdTime' => 'DESC', 'isElite' => 'ASC');
        } else {
            $orderBy = array('createdTime' => 'ASC');
        }

        return $this->getThreadPostDao()->search(array('threadId' => $threadId), $orderBy, $start, $limit);
    }

    public function getThreadPostCount($courseId, $threadId)
    {
        return $this->getThreadPostDao()->count(array('threadId' => $threadId));
    }

    public function findThreadElitePosts($courseId, $threadId, $start, $limit)
    {
        return $this->getThreadPostDao()->search(array('threadId' => $threadId, 'isElite' => 1), array('createdTime' => 'ASC'), $start, $limit);
    }

    public function getPostCountByuserIdAndThreadId($userId, $threadId)
    {
        return $this->getThreadPostDao()->count(array('userId' => $userId, 'threadId' => $threadId));
    }

    public function getThreadPostCountByThreadId($threadId)
    {
        return $this->getThreadPostDao()->count(array('threadId' => $threadId));
    }

    public function getMyReplyThreadCount()
    {
        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
        );

        return $this->getThreadPostDao()->countGroupByThreadId($conditions);
    }

    public function getMyLatestReplyPerThread($start, $limit)
    {
        return $this->getThreadPostDao()->searchByUserIdGroupByThreadId($this->getCurrentUser()->getId(), $start, $limit);
    }

    public function getPost($courseId, $id)
    {
        return $this->getThreadPostDao()->get($id);
    }

    public function postAtNotifyEvent($post, $users)
    {
        $this->dispatchEvent('course.thread.post.at', $post, array('users' => $users));
    }

    public function createPost($post)
    {
        $requiredKeys = array('courseId', 'threadId', 'content');

        if (!ArrayToolkit::requireds($post, $requiredKeys)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $event = $this->dispatchEvent('course.thread.post.before_create', $post);

        if ($event->isPropagationStopped()) {
            $this->createNewException(\Biz\Thread\ThreadException::FORBIDDEN_TIME_LIMIT());
        }

        $thread = $this->getThread($post['courseId'], $post['threadId']);
        list($course, $member) = $this->getCourseService()->tryTakeCourse($thread['courseId']);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $post['content'] = $this->sensitiveFilter($post['content'], 'course-thread-post-create');
        $post['content'] = $this->filter_Emoji($post['content']);

        $this->getCourseService()->tryTakeCourse($post['courseId']);

        $post['userId'] = $this->getCurrentUser()->id;
        $post['isElite'] = $this->getMemberService()->isCourseTeacher($post['courseId'], $post['userId']) ? 1 : 0;
        $post['createdTime'] = time();

        //if user can manage course, we trusted rich editor content
        $hasCourseManagerRole = $this->getCourseService()->hasCourseManagerRole($post['courseId']);
        $trusted = empty($hasCourseManagerRole) ? false : true;
        //创建post过滤html
        $post['content'] = $this->biz['html_helper']->purify($post['content'], $trusted);

        $post = $this->getThreadPostDao()->create($post);

        // 高并发的时候， 这样更新postNum是有问题的，这里暂时不考虑这个问题。
        $threadFields = array(
            'postNum' => $thread['postNum'] + 1,
            'latestPostUserId' => $post['userId'],
            'latestPostTime' => $post['createdTime'],
        );
        $this->getThreadDao()->update($thread['id'], $threadFields);
        $user = $this->getCurrentUser();

        if ('question' == $thread['type']) {
            if ($user['id'] == $thread['userId']) {
                foreach ($course['teacherIds'] as $teacherId) {
                    if ('question' == $thread['type']) {
                        $this->getNotifiactionService()->notify($teacherId, 'question-post-ask', array(
                            'user' => array('id' => $user['id'], 'nickname' => $user['nickname']),
                            'id' => $post['id'],
                            'content' => TextHelper::truncate($post['content'], 50),
                            'thread' => empty($thread) ? null : array('id' => $thread['id'], 'title' => !empty($thread['title']) ? $thread['title'] : $this->trans('course.thread.question_type.'.$thread['questionType'])),
                            'post' => $post,
                            'courseId' => $course['id'],
                        ));
                    }
                }
            } else {
                $this->getNotifiactionService()->notify($thread['userId'], 'question-answer', array(
                    'user' => array('id' => $user['id'], 'nickname' => $user['nickname']),
                    'id' => $post['id'],
                    'content' => TextHelper::truncate($post['content'], 50),
                    'thread' => empty($thread) ? null : array('id' => $thread['id'], 'title' => !empty($thread['title']) ? $thread['title'] : $this->trans('course.thread.question_type.'.$thread['questionType'])),
                    'post' => $post,
                    'courseId' => $course['id'],
                ));
            }
        }

        $this->dispatchEvent('course.thread.post.create', $post);

        return $post;
    }

    public function updatePost($courseId, $id, $fields)
    {
        $fields['content'] = $this->sensitiveFilter($fields['content'], 'course-thread-post-update');

        $post = $this->getPost($courseId, $id);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        $user = $this->getCurrentUser();
        ($user->isLogin() && $user->id == $post['userId']) || $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $fields = ArrayToolkit::parts($fields, array('content'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        //if user can manage course, we trusted rich editor content
        $hasCourseManagerRole = $this->getCourseService()->hasCourseManagerRole($courseId);
        $trusted = empty($hasCourseManagerRole) ? false : true;
        //更新post过滤html
        $fields['content'] = $this->biz['html_helper']->purify($fields['content'], $trusted);

        $post = $this->getThreadPostDao()->update($id, $fields);
        $this->dispatchEvent('course.thread.post.update', $post);

        return $post;
    }

    public function readPost($postId)
    {
        return $this->getThreadPostDao()->update($postId, array('isRead' => 1));
    }

    public function deletePost($courseId, $id)
    {
        $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');

        $post = $this->getThreadPostDao()->get($id);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        if ($post['courseId'] != $courseId) {
            $this->createNewException(ThreadException::POST_NOT_MATCH_COURSE());
        }

        $this->getThreadPostDao()->delete($post['id']);
        $this->getThreadDao()->wave(array($post['threadId']), array('postNum' => -1));
        $this->dispatchEvent('course.thread.post.delete', $post);
    }

    protected function prepareThreadSearchConditions($conditions)
    {
        if (isset($conditions['threadType'])) {
            $conditions[$conditions['threadType']] = 1;
        }

        if (isset($conditions['keywordType'], $conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('title', 'content', 'courseId', 'courseTitle'))) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (!empty($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        return $conditions;
    }

    protected function filterThread($thread)
    {
        return ArrayToolkit::parts($thread, array('title', 'content', 'type', 'videoAskTime', 'videoId', 'courseId', 'taskId', 'source', 'questionType'));
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array('isStick' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'posted':
                $orderBys = array('isStick' => 'DESC', 'latestPostTime' => 'DESC');
                break;
            case 'createdNotStick':
                $orderBys = array('createdTime' => 'DESC');
                break;
            case 'postedNotStick':
                $orderBys = array('latestPostTime' => 'DESC');
                break;
            case 'popular':
                $orderBys = array('hitNum' => 'DESC');
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBys;
    }

    protected function filter_Emoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '[emoji]' : $match[0];
            },
            $str);

        return $str;
    }

    protected function sensitiveFilter($str, $type)
    {
        return $this->getSensitiveService()->sensitiveCheck($str, $type);
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    /**
     * @return ThreadPostDaoImpl
     */
    protected function getThreadPostDao()
    {
        return $this->createDao('Course:ThreadPostDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
