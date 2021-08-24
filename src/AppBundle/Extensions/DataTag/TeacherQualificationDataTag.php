<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class TeacherQualificationDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $teacherQualifications = $this->getTeacherQualification()->searchTeacherQualification(
            ['roles' => '|ROLE_TEACHER|'],
            ['updated_time' => 'DESC'],
            0, $arguments['count']
        );

        $teacherQualifications = ArrayToolkit::index($teacherQualifications, 'user_id');

        $teacherProfiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teacherQualifications, 'user_id'));

        foreach ($teacherQualifications as $userId => $teacherQualification) {
            if ($teacherQualification['avatar']) {
                $teacherQualifications[$userId]['url'] = $this->getWebExtension()->getFpath($teacherQualification['avatar']);
            }
            $teacherQualifications[$userId]['truename'] = $teacherProfiles[$userId]['truename'] ?: '';
        }

        return $teacherQualifications;
    }
}
