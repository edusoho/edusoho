<?php

namespace Biz\WrongBook\Service;

interface WrongBookPractiseService
{
    public function createExercise($fields);

    public function updateExercise($id, $fields);
}
