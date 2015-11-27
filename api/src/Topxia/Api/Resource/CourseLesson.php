<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CourseLesson extends BaseResource
{

    public function filter(&$res)
    {

       $res['createdTime'] = date('c', $res['createdTime']);
        $res['lessonId'] =  $res['id'];

          unset($res['id']);
          unset($res['chapterId']);
          unset($res['seq']);
          unset($res['free']);
          unset($res['status']);
          unset($res['giveCredit']);
          unset($res['requireCredit']);
          unset($res['mediaId']);
          unset($res['mediaSource']);
          unset($res['mediaName']);
          unset($res['mediaUri']);
          unset($res['homeworkId']);
          unset($res['exerciseId']);
          unset($res['length']);
          unset($res['materialNum']);
          unset($res['quizNum']);
          unset($res['startTime']);
          unset($res['endTime']);
          unset($res['memberNum']);
          unset($res['replayStatus']);
          unset($res['maxOnlineNum']);
          unset($res['liveProvider']);
          unset($res['userId']);
          unset($res['copyId']);



        return $res;
    }
}
