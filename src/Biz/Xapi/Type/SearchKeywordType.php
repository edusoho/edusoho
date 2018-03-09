<?php

namespace Biz\Xapi\Type;

use QiQiuYun\SDK\XAPIObjectTypes;

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

        $pushStatements = array();
        $sdk = $this->createXAPIService();
        foreach ($statements as $statement) {
            try {
                $actor = $this->getActor($statement['user_id']);
                $data = $statement['data'];
                $object = array(
                    'id' => $data['uri'],
                    'definitionType' => $data['type'] === 'teacher' ? '' : $this->getDefinitionType($data['type']),
                    'objectType' => $data['type'] === 'teacher' ? XAPIObjectTypes::AGENT : XAPIObjectTypes::ACTIVITY
                );
                $result = array(
                    'response' => $data['q']
                );
                $pushStatements[] = $sdk->searched($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
