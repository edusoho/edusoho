<?php

namespace Custom\Service\Homework;

interface HomeworkService
{
    public function createCustomHomework($courseId,$lessonId,$fields);
}