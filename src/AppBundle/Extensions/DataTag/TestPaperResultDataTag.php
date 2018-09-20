<?php

namespace AppBundle\Extensions\DataTag;

class TestPaperResultDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        $user = $this->getCurrentUser();
        $this->checkArguments($arguments, array(
            'activityId',
            'testpaperId',
        ));

        $activity = $this->getActivityService()->getActivity($arguments['activityId'], false);

        return $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $arguments['testpaperId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);
    }

    public function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
