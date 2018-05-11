<?php

namespace Biz\Xapi\Type;

use QiQiuYun\SDK\Constants\XAPIActivityTypes;

class RatedCourseType extends Type
{
    const TYPE = 'rated_course';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }

        $pushStatements = array();
        $sdk = $this->createXAPIService();
        $courses = $this->findCourses(array($statements, 'target_id'));

        foreach ($statements as $statement) {
            try {
                $actor = $this->getActor($statement['user_id']);
                $course = $courses[$statement['target_id']];
                $data = $statement['context'];
                $object = array(
                    'id' => $statement['target_id'],
                    'definitionType' => XAPIActivityTypes::COURSE,
                    'name' => $course['title'],
                    'course' => $course,
                );

                $result = array(
                    'score' => $data['score'],
                    'response' => $data['response'],
                );

                $pushStatements[] = $sdk->rated($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
