<?php

namespace Biz\Accessor;

use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;

abstract class AccessorAdapter implements AccessorInterface
{
    const CONTEXT_ERROR_KEY = '_contextResults';
    /**
     * @var Biz
     */
    protected $biz;

    private $messages;

    /**
     * @var \Biz\Accessor\AccessorAdapter
     */
    private $nextAccessor = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->registerDefaultMessages();
        $this->registerMessages();
    }

    public function setNextAccessor(AccessorInterface $nextAccessor)
    {
        $this->nextAccessor = $nextAccessor;
    }

    public function getNextAccessor()
    {
        return $this->nextAccessor;
    }

    public function process($bean)
    {
        $error = $this->access($bean);
        if ($this->nextAccessor) {
            if ($error) {
                $bean[self::CONTEXT_ERROR_KEY] = $error;
            }

            return $this->nextAccessor->access($bean);
        } else {
            return $error;
        }
    }

    public function hasError($bean, $errorCode)
    {
        if (empty($bean[self::CONTEXT_ERROR_KEY])) {
            return false;
        } else {
            return $bean[self::CONTEXT_ERROR_KEY]['code'] === $errorCode;
        }
    }

    protected function registerMessages()
    {
    }

    abstract public function access($bean);

    protected function buildResult($code, $params = [])
    {
        // api暂时不需要支持国际化
        return [
            'code' => $code,
            'msg' => $this->getMessage($code, $params),
        ];
    }

    private function getMessage($key, $params)
    {
        if (empty($this->messages[$key])) {
            return 'Denied';
        }
        if (!empty($params)) {
            return call_user_func_array('sprintf', array_merge([$this->messages[$key]], array_values($params)));
        }

        return $this->messages[$key];
    }

    private function registerDefaultMessages()
    {
        $this->messages['item_bank_exercise.not_found'] = '题库练习未找到';
        $this->messages['item_bank_exercise.unpublished'] = '题库练习(#%s)尚未发布';
        $this->messages['item_bank_exercise.closed'] = '题库练习(#%s)已关闭';
        $this->messages['item_bank_exercise.not_join_enable'] = '题库练习(#%s)未开放购买';
        $this->messages['item_bank_exercise.expired'] = '题库练习(#%s)已到期';
        $this->messages['item_bank_exercise.not_arrive'] = '题库练习(#%s)未到学习时间';

        $this->messages['course.not_found'] = '教学计划未找到';
        $this->messages['course.unpublished'] = '教学计划(#%s)尚未发布';
        $this->messages['course.closed'] = '教学计划(#%s)已关闭';
        $this->messages['course.not_buyable'] = '教学计划(#%s)未开放购买';
        $this->messages['course.course.reach_max_student_num'] = '不达到最大人数限制，不可加入';
        $this->messages['course.expired'] = '教学计划(#%s)已到期';
        $this->messages['course.buy_expired'] = '教学计划(#%s)已过购买期限';

        $this->messages['user.not_found'] = '用户未找到';
        $this->messages['user.not_login'] = '用户未登录';
        $this->messages['user.locked'] = '用户(#%s)已被禁用';

        $this->messages['member.not_found'] = '学员未找到';
        $this->messages['member.member_exist'] = '用户(#%s)已经是学员';
        $this->messages['member.expired'] = '学员(#%s)已过期';
        $this->messages['member.auditor'] = '旁听生无权限学习';

        $this->messages['classroom.not_found'] = '班级未找到';
        $this->messages['classroom.unpublished'] = '班级(#%s)未发布';
        $this->messages['classroom.closed'] = '班级(#%s)已关闭';
        $this->messages['classroom.not_buyable'] = '班级(#%s)未开放购买';
        $this->messages['classroom.expired'] = '班级(#%s)已到期';
    }

    protected function registerMessage($key, $msg)
    {
        if (empty($this->messages[$key])) {
            $this->messages[$key] = $msg;
        }
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }
}
