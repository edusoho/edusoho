<?php

namespace Biz\Xapi\Type;

class PurchasedCourseType extends Type
{
    const TYPE = 'purchased_course';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        $pushStatements = array();

        $sdk = $this->createXAPIService();
        $courses = $this->findCourses(
            array($statements, 'target_id')
        );
        foreach ($statements as $statement) {
            try {
                $actor = $this->getActor($statement['user_id']);
                $data = $statement['context'];
                $course = $courses[$statement['target_id']];
                $object = array(
                    'id' => $statement['target_id'],
                    'definitionType' => $this->convertActivityType($statement['target_type']),
                    'name' => $data['title'],
                    'course' => $course,
                );
                $result = array(
                    'amount' => $data['pay_amount'],
                );

                $pushStatements[] = $sdk->purchased($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e->getMessage());
            }
        }

        return $pushStatements;
    }
}
