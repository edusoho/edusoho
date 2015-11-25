<?php
namespace Topxia\Service\Search\Adapter;

class TeacherSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $teachers)
    {
        $adaptResult = array();

        foreach ($teachers as $index => $teacher) {
            array_push($adaptResult, $teacher);
        }

        return $adaptResult;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
