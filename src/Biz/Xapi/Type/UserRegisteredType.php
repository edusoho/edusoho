<?php

namespace Biz\Xapi\Type;

class UserRegisteredType extends Type
{
    const TYPE = 'registered_user';

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

                $pushStatements[] = $sdk->registered($actor, null, null, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
