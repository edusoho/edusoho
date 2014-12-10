<?php

namespace Topxia\Service\Course;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface CourseCopyService
{
    public function copyTeachers($courseId, $newCourse);

    public function copyTestpapers($courseId, $newCourse, $newQuestions);

    public function convertTestpaperLesson($newLessons, $newTestpapers);

    public function copyQuestions($courseId, $newCourse, $newLessons);

    public function copyLessons($courseId, $newCourse, $chapters);

    public function copyChapters($courseId, $newCourse);

    public function copyCourse($course);

    public function copyMaterials($courseId, $newCourse, $newLessons);

    public function copyHomeworks($courseId, $newCourse, $newLessons,$newQuestions);

    public function copyExercises($courseId, $newCourse, $newLessons);
}