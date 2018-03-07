<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;
use QiQiuYun\SDK\XAPIActivityTypes;
use QiQiuYun\SDK\XAPIObjectTypes;
use QiQiuYun\SDK\XAPIVerbs;

class SearchKeywordType extends Type
{
    const TYPE = 'searched_keyword';

    public function package($statement)
    {
        //
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $pushStatements = array();
            $sdk = $this->createXAPIService();
            foreach ($statements as $statement) {
                $actor = $this->getActor($statement['user_id']);
                $data = $statement['data'];
                $object = array(
                    'id' => '/search?q='.$data['q'].'&type='.$data['type'],
                    'definitionType' => $this->getDefinitionType($data['type']),
                    'objectType' => $data['type'] === 'teacher' ? XAPIObjectTypes::AGENT : XAPIObjectTypes::ACTIVITY
                );
                $result = array(
                    'response' => $data['q']
                );
                $pushStatements[] = $sdk->searched($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            }

            return $pushStatements;
        } catch (\Exception $e) {
            $this->biz['logger']->error($e->getMessage());
        }
    }

    private function getDefinitionType($type)
    {
        static $map = array(
            'teacher' => '',
            'article' => XAPIActivityTypes::MESSAGE,
            'thread' => XAPIActivityTypes::QUESTION,
            'course' => XAPIActivityTypes::COURSE,
            'classroom' => XAPIActivityTypes::CLASS_ONLINE,
        );

        return $map[$type];
    }
}
