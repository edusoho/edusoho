<?php
namespace Biz\TestpaperActivity\Service;

interface TestpaperActivityService
{
    public function getActivity($id);

    public function createActivity($fields);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);
}
