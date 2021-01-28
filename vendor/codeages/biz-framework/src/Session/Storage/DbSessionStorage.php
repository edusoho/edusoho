<?php

namespace Codeages\Biz\Framework\Session\Storage;

class DbSessionStorage implements SessionStorage
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function delete($sessId)
    {
        return $this->getSessionDao()->deleteBySessId($sessId);
    }

    public function get($sessId)
    {
        return $this->getSessionDao()->getBySessId($sessId);
    }

    public function save($session)
    {
        $savedSession = $this->getSessionDao()->getBySessId($session['sess_id']);
        if (empty($savedSession)) {
            return $this->getSessionDao()->create($session);
        } else {
            return $this->getSessionDao()->update($savedSession['id'], $session);
        }
    }

    public function gc()
    {
        return $this->getSessionDao()->deleteBySessDeadlineLessThan(time());
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Session:SessionDao');
    }
}
