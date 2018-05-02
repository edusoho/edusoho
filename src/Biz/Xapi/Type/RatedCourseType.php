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
        $courses = $this->findCourses($statements,'target_id');
        foreach ($statements as $statement) {
            try {
                $actor = $this->getActor($statement['user_id']);
                $data = $statement['context'];
                $object = array(
                    'id' => $data['uri'],
                    'definitionType' => XAPIActivityTypes::COURSE,
                    'name' => $courses[$statement['target_id']]['title'],
                );

                $result = array(
                    'score' => $data['score'],
                );

                $pushStatements[] = $sdk->rated($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
