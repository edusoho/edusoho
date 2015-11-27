<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Course extends BaseResource
{

    public function filter(&$res)
    {

       $res['createdTime'] = date('c', $res['createdTime']);
        $res['courseId'] =  $res['id'];

          unset($res['id']);
          unset($res['status']);
          unset($res['maxStudentNum']);
          unset($res['originPrice']);
          unset($res['coinPrice']);
          unset($res['originCoinPrice']);
          unset($res['expiryDay']);
          unset($res['showStudentNumType']);
          unset($res['serializeMode']);
          unset($res['income']);
          unset($res['giveCredit']);
          unset($res['rating']);
          unset($res['vipLevelId']);
          unset($res['useInClassroom']);
          unset($res['categoryId']);
          unset($res['smallPicture']);
          unset($res['middlePicture']);

          unset($res['largePicture']);
          unset($res['teacherIds']);
          unset($res['recommended']);
          unset($res['recommendedSeq']);
          unset($res['recommendedTime']);
          unset($res['locationId']);
          unset($res['parentId']);
          unset($res['address']);
          unset($res['studentNum']);
          unset($res['noteNum']);
          unset($res['userId']);
          unset($res['deadlineNotify']);
          unset($res['daysOfNotifyBeforeDeadline']);
          unset($res['watchLimit']);
          unset($res['singleBuy']);
          unset($res['freeStartTime']);
           unset($res['freeEndTime']);
           unset($res['discountId']);
           unset($res['discount']);
           unset($res['approval']);
           unset($res['locked']);
           unset($res['maxRate']);


        return $res;
    }



}