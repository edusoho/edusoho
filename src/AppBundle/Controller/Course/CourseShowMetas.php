<?php
namespace AppBundle\Controller\Course;

class CourseShowMetas
{
    const SUMMARY_NAME  = '介绍';
    const TASKS_NAME    = '目录';
    const THREADS_NAME  = '话题';
    const REVIEWS_NAME  = '评价';
    const NOTES_NAME    = '笔记';
    const MATERIAL_NAME = '资料区';

    public static function getMemberCourseShowMetas()
    {
        $metas = self::getGuestCourseShowMetas();
        return array(
            'header'  => 'AppBundle:My/Course:headerForMember',
            'tabs'    => array(
                'tasks'    => array(
                    'name'    => self::TASKS_NAME,
                    'content' => 'AppBundle:Course:tasks'
                ),
                'threads'  => array(
                    'name'    => self::THREADS_NAME,
                    'number'  => 'threadNum',
                    'content' => 'AppBundle:Course/Thread:index'
                ),
                'reviews'  => array(
                    'name'    => self::REVIEWS_NAME,
                    'number'  => 'ratingNum',
                    'content' => 'AppBundle:Course:reviews'
                ),
                'notes'    => array(
                    'name'    => self::NOTES_NAME,
                    'number'  => 'noteNum',
                    'content' => 'AppBundle:Course:notes'
                ),
                'material' => array(
                    'name'    => self::MATERIAL_NAME,
                    'number'  => 'materialNum',
                    'content' => 'AppBundle:Course/Material:index'
                ),
                'summary'  => array(
                    'name'    => self::SUMMARY_NAME,
                    'content' => 'AppBundle:Course:summary'
                )
            ),
            'widgets' => $metas['widgets']
        );
    }

    public static function getGuestCourseShowMetas()
    {
        return array(
            'header'  => 'AppBundle:Course:header',
            'tabs'    => array(
                'summary' => array(
                    'name'    => self::SUMMARY_NAME,
                    'content' => 'AppBundle:Course:summary'
                ),
                'tasks'   => array(
                    'name'    => self::TASKS_NAME,
                    'content' => 'AppBundle:Course:tasks'
                ),
                'reviews' => array(
                    'name'    => self::REVIEWS_NAME,
                    'number'  => 'ratingNum',
                    'content' => 'AppBundle:Course:reviews'
                ),
                'notes'   => array(
                    'name'    => self::NOTES_NAME,
                    'number'  => 'noteNum',
                    'content' => 'AppBundle:Course:notes'
                )
            ),
            'widgets' => array(
                'characteristic'     => array(
                    'uri'  => 'AppBundle:Course:characteristic',
                    'type' => 'render'
                ),
                'otherCourse'        => array(
                    'uri'  => 'AppBundle:Course:otherCourse',
                    'type' => 'render'
                ),
                'recommendClassroom' => array(
                    'uri'  => 'course/widgets/recommend-classroom.html.twig',
                    'type' => 'include'
                ),
                'teachers'           => array(
                    'uri'  => 'AppBundle:Course:teachers',
                    'type' => 'render'
                ),
                'newestStudents'     => array(
                    'uri'  => 'AppBundle:Course:newestStudents',
                    'type' => 'render'
                ),
                'studentActivity'    => array(
                    'uri'  => 'course/widgets/student-activity.html.twig',
                    'type' => 'include'
                )
            )
        );
    }
}
