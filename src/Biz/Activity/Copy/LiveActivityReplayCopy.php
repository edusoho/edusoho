<?php

namespace Biz\Activity\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseLessonReplayDao;

class LiveActivityReplayCopy extends AbstractCopy
{
    protected function getFields()
    {
        return [
            'title',
            'replayId',
            'globalId',
            'userId',
            'hidden',
            'type',
        ];
    }

    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        if ('live' != $options['newActivity']['mediaType']) {
            return;
        }
        $replays = $this->getLessonReplayDao()->findByCourseIdAndLessonId($options['originCourse']['id'], $options['originActivity']['id']);
        if (empty($replays)) {
            return;
        }
        $newReplays = [];
        foreach ($replays as $replay) {
            $newReplay = $this->partsFields($replay);
            $newReplay['lessonId'] = $options['newActivity']['id'];
            $newReplay['courseId'] = $options['newCourse']['id'];
            $newReplay['copyId'] = $replay['id'];
            $newReplays[] = $newReplay;
        }
        $this->getLessonReplayDao()->batchCreate($newReplays);
    }

    /**
     * @return CourseLessonReplayDao
     */
    protected function getLessonReplayDao()
    {
        return $this->biz->dao('Course:CourseLessonReplayDao');
    }
}
