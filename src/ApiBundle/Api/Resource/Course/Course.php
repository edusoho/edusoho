<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class Course extends Resource
{
    public function get(Request $request, $courseId)
    {
        return array('id' => $courseId, 'title' => 'i am fake course');
    }

    public function update(Request $request, $courseId)
    {
        return array(
            'id' => $courseId,
            'title' => $request->request->get('title')
        );
    }

    public function search(Request $request)
    {
        return array(
            array('id' => 1, 'title' => 'i am fake course'),
            array('id' => 2, 'title' => 'i am fake course'),
        );
    }

    public function remove(Request $request, $courseId)
    {
        return true;
    }

    public function add(Request $request)
    {
        return $request->request->all();
    }

    public function filter($res)
    {
    }

}