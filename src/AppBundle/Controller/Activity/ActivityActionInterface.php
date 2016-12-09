<?php

namespace AppBundle\Controller\Activity;

use Symfony\Component\HttpFoundation\Request;

interface ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId);

    public function editAction(Request $request, $id, $courseId);

    public function createAction(Request $request, $courseId);
}
