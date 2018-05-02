<?php

namespace Biz\Xapi\Type;

use QiQiuYun\SDK\Constants\XAPIActivityTypes;

class BookmarkedCourseType extends Type
{
    const TYPE = 'bookmarked_course';

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
                    'definitionType' => XAPIActivityTypes::COURSE,
                    'name' => $data['name'],
                );

                $pushStatements[] = $sdk->bookmarked($actor, $object, null, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
