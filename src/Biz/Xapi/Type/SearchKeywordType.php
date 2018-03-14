<?php

namespace Biz\Xapi\Type;

use QiQiuYun\SDK\Constants\XAPIActivityTypes;

class SearchKeywordType extends Type
{
    const TYPE = 'searched_keyword';

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
                    'id' => $data['uri'],
                    'definitionType' => XAPIActivityTypes::SEARCH_ENGINE,
                );
                $result = array(
                    'response' => $data['q'],
                    'type' => $this->convertActivityType($data['type']),
                );
                $pushStatements[] = $sdk->searched($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
