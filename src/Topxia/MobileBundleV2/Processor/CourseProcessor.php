<?php

namespace Topxia\MobileBundleV2\Processor;

interface CourseProcessor
{
    public function getVersion();

    public function getCourses();

    public function getLearningCourseWithoutToken();

    public function getUserTeachCourse();

    public function getLearnedCourse();

    public function getFavoriteCourse();

    public function getFavoriteLiveCourse();

    public function getFavoriteNormalCourse();

    /**
     * tagId    	标签id
     * search 	搜索内容
     * 参数只能有一个 tagId或search.
     */
    public function searchCourse();

    public function getCourse();

    public function getCourseReviewInfo();

    public function getReviews();

    public function favoriteCourse();

    public function unFavoriteCourse();

    public function getTeacherCourses();

    /**
     *获取课程公告列表.
     */
    public function getCourseNotices();

    public function unLearnCourse();

    public function getCourseThreads();

    public function commitCourse();

    /**
     *获取用户 课程会员信息
     * token 用户token
     * courseId 课程id.
     */
    public function getCourseMember();

    /**
     *  获取问题详情（包括提问的用户信息）
     *	courseId 课程id
     *	threadId 问答id
     *	token userToken.
     */
    public function getThread();

    /**
     *	问题编辑更新
     *	courseId 课程id
     *	threadId 问答id.
     */
    public function updateThread();

    public function getThreadTeacherPost();

    /**
     *	courseId 课程id
     *	threadId 问答id
     *	start 起始索引
     *	limit 分页.
     */
    public function getThreadPost();

    /**
     *	courseId
     *	threadId
     *	content 内容
     *	imageCount 图片数量
     *	image1， image2...
     */
    public function postThread();

    /** 更新一条回复
     *
     */
    public function updatePost();

    public function coupon();

    public function vipLearn();

    /**
     *根据用户ID获取笔记信息(全部).
     */
    public function getNoteList();

    /**
     * 获取课程下所有笔记.
     */
    public function getCourseNotes();

    /**
     *获取课时笔记.
     */
    public function getLessonNote();

    /**
     *添加一条笔记.
     */
    public function AddNote();

    /**
     *删除一条笔记.
     */
    public function DeleteNote();

    public function getOneNote();

    /**
     *获取单条回复信息.
     */
    public function getOneThreadPost();

    public function hitThread();

    /**
     *测试直播参数.
     */
    public function getLiveCourse();

    public function getLiveCourses();

    public function getAllLiveCourses();

    public function getModifyInfo();

    public function updateModifyInfo();
}
