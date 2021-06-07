<?php

namespace Biz\MultiClass;

use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class AssistantPermission
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function isAssistant($multiClassId = 0)
    {
        $user = $this->biz['user'];
        $member = $this->getMemberService()->getMemberByMultiClassIdAndUserId($multiClassId, $user['id']);
        if (!empty($member) && 'assistant' === $member['role']) {
            return true;
        }

        if (empty($multiClassId) && in_array('ROLE_TEACHER_ASSISTANT', $user['roles'])) {
            if (empty(array_intersect($user['roles'], ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER']))) {
                return true;
            }
        }

        return false;
    }

    public function getPermissionMenu()
    {
        return [
            [
                'title' => '班课管理',
                'code' => 'multi_class_manage',
                'disabled' => 1,
                'children' => [
                    ['title' => '创建班课', 'code' => 'multi_class_create', 'disabled' => 0],
                    ['title' => '编辑班课', 'code' => 'multi_class_edit', 'disabled' => 0],
                    ['title' => '复制班课', 'code' => 'multi_class_copy', 'disabled' => 0],
                    ['title' => '删除班课', 'code' => 'multi_class_delete', 'disabled' => 0],
                ],
            ],
            [
                'title' => '课程管理',
                'code' => 'course_manage',
                'disabled' => 1,
                'children' => [
                    [
                        'title' => '课时管理',
                        'code' => 'course_lesson_manage',
                        'disabled' => 1,
                        'children' => [
                            ['title' => '创建课时', 'code' => 'course_lesson_create', 'disabled' => 0],
                            ['title' => '编辑课时', 'code' => 'course_lesson_edit', 'disabled' => 0],
                            ['title' => '删除课时', 'code' => 'course_lesson_delete', 'disabled' => 0],
                        ],
                    ],
                    [
                        'title' => '学员管理',
                        'code' => 'course_member_manage',
                        'disabled' => 1,
                        'children' => [
                            ['title' => '添加学员', 'code' => 'course_member_create', 'disabled' => 0],
                            ['title' => '移除学员', 'code' => 'course_member_delete', 'disabled' => 0],
                            ['title' => '修改学员有效期', 'code' => 'course_member_deadline_edit', 'disabled' => 0],
                            ['title' => '导入学员', 'code' => 'course_member_import', 'disabled' => 0],
                            ['title' => '导出学员', 'code' => 'course_member_export', 'disabled' => 0],
                        ],
                    ],
                    [
                        'title' => '直播统计',
                        'code' => 'course_live_manage',
                        'disabled' => 0,
                        'children' => [
                            ['title' => '导出数据', 'code' => 'course_live_data_export', 'disabled' => 0],
                        ],
                    ],
                    ['title' => '作业批阅', 'code' => 'course_homework_review', 'disabled' => 0],
                    ['title' => '考试批阅', 'code' => 'course_exam_review', 'disabled' => 0],
                    ['title' => '数据预览', 'code' => 'course_statistics_view', 'disabled' => 0],
                    ['title' => '公告管理', 'code' => 'course_announcement_manage', 'disabled' => 0],
                    ['title' => '录播管理', 'code' => 'course_replay_manage', 'disabled' => 0],
                    ['title' => '弹题统计', 'code' => 'course_question_marker_manage', 'disabled' => 0],
                    ['title' => '订单统计', 'code' => 'course_order_manage', 'disabled' => 0],
                ],
            ],
        ];
    }

    public function getPermissions()
    {
        $assistantPermissions = $this->getSettingService()->get('assistant_permission', []);

        if (empty($assistantPermissions['permissions'])) {
            return [
                'multi_class_manage',
                'course_manage',
                'course_lesson_manage',
                'course_member_manage',
                'course_member_create',
                'course_member_deadline_edit',
                'course_member_import',
                'course_live_manage',
                'course_homework_review',
                'course_exam_review',
                'course_statistics_view',
                'course_announcement_manage',
                'course_replay_manage',
                'course_question_marker_manage',
                'course_order_manage',
            ];
        }

        return $assistantPermissions['permissions'];
    }

    public function hasActionPermission($action)
    {
        $permissions = $this->getPermissions();

        return in_array($action, $permissions);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
