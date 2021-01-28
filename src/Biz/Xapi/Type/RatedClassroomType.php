<?php

namespace Biz\Xapi\Type;

use QiQiuYun\SDK\Constants\XAPIActivityTypes;

class RatedClassroomType extends Type
{
    const TYPE = 'rated_classroom';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }

        $pushStatements = array();
        $sdk = $this->createXAPIService();
        foreach ($statements as $statement) {
            try {
                $actor = $this->getActor($statement['user_id']);
                $data = $statement['context'];
                $object = array(
                    'id' => $statement['target_id'],
                    'definitionType' => XAPIActivityTypes::CLASS_ONLINE,
                    'name' => $data['name'],
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
