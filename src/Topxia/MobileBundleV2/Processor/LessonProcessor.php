<?php

namespace Topxia\MobileBundleV2\Processor;

interface LessonProcessor
{
    public function getCourseLessons();

    public function getLesson();

    public function getLessonMaterial();

    /*
    * 	courseId
    *	lessonId
    *	token
    */
    public function learnLesson();

    /*
    * 	courseId
    *	lessonId
    *	token
    */
    public function unLearnLesson();

    public function getLearnStatus();

    public function getLessonStatus();

    public function getTestpaperInfo();

    public function getVideoMediaUrl();

    public function getCourseDownLessons();

    public function getLocalVideo();
}
