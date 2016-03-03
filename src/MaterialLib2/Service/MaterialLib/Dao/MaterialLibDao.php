<?php

namespace MaterialLib\Service\MaterialLib\Dao;

interface MaterialLibDao
{
    public function findLatestUploadCourses($limit);

    public function findLatestUploadUsers($limit);
}
