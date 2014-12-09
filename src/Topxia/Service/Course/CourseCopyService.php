<?php

namespace Topxia\Service\Course;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface CourseCopyService
{
      protected function configure();

      protected function execute(InputInterface $input, OutputInterface $output);

      protected function copyTeachers($courseId, $newCourse);

      protected function copyTestpapers($courseId, $newCourse, $newQuestions);

      protected function convertTestpaperLesson($newLessons, $newTestpapers);

      protected function copyQuestions($courseId, $newCourse, $newLessons);

      protected function copyLessons($courseId, $newCourse, $chapters);

      protected function copyChapters($courseId, $newCourse);

      private function copyCourse($course);



}