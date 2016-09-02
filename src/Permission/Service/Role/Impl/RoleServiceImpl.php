<?php
namespace Permission\Service\Role\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Permission\Service\Role\RoleService;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function getRole($id)
    {
        return $this->getRoleDao()->getRole($id);
    }

    public function getRoleByCode($code)
    {
        return $this->getRoleDao()->getRoleByCode($code);
    }

    public function findRolesByCodes($codes)
    {
        return $this->getRoleDao()->findRolesByCodes($codes);
    }

    public function createRole($role)
    {
        $role['createdTime']   = time();
        $user                  = $this->getCurrentUser();
        $role['createdUserId'] = $user['id'];
        $role                  = ArrayToolkit::parts($role, array('name', 'code', 'data', 'createdTime', 'createdUserId'));
        $this->getLogService()->info('role', 'create_role', '新增权限用户组"'.$role['name'].'"', $role);
        return $this->getRoleDao()->createRole($role);
    }

    public function updateRole($id, array $fields)
    {
        $user                  = $this->getCurrentUser();
        $fields                = ArrayToolkit::parts($fields, array('name', 'code', 'data'));
        $fields['updatedTime'] = time();
        $role                  = $this->getRoleDao()->updateRole($id, $fields);
        $this->getLogService()->info('role', 'update_role', '更新权限用户组"'.$role['name'].'"', $role);
        return $role;
    }

    public function deleteRole($id)
    {
        $role = $this->getRoleDao()->getRole($id);
        if (!empty($role)) {
            $this->getRoleDao()->deleteRole($id);
            $this->getLogService()->info('role', 'delete_role', '删除橘色"'.$role['name'].'"', $role);
        }
    }

    public function searchRoles($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        switch ($sort) {
            case 'created':
                $sort = array('createdTime', 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime', 'ASC');
                break;

            default:
                throw $this->createServiceException('参数sort不正确。');
                break;
        }
        $roles = $this->getRoleDao()->searchRoles($conditions, $sort, $start, $limit);

        return $roles;
    }

    public function searchRolesCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
        return $this->getRoleDao()->searchRolesCount($conditions);
    }

    public function initRoles()
    {
        $role = PermissionBuilder::instance()->getOriginPermissionTree();
        $role = ArrayToolkit::column($role, 'code');

        $superAdminRole = $this->getRoleDao()->getRoleByCode('ROLE_SUPER_ADMIN');
        $adminRole      = $this->getRoleDao()->getRoleByCode('ROLE_ADMIN');
        $teacherRole    = $this->getRoleDao()->getRoleByCode('ROLE_TEACHER');
        $userRole       = $this->getRoleDao()->getRoleByCode('ROLE_USER');
        if (empty($superAdminRole)) {
            $superAdminRole = $this->makeRole('ROLE_SUPER_ADMIN', $role);
            $superAdminRole = $this->createRole($superAdminRole);
        }
        if (empty($adminRole)) {
            $superAdminRole = $this->makeRole('ROLE_ADMIN', $role);
            $superAdminRole = $this->createRole($superAdminRole);
        }
        if (empty($teacherRole)) {
            $superAdminRole = $this->makeRole('ROLE_TEACHER', $role);
            $superAdminRole = $this->createRole($superAdminRole);
        }
        if (empty($userRole)) {
            $superAdminRole = $this->makeRole('ROLE_USER', $role);
            $superAdminRole = $this->createRole($superAdminRole);
        }
    }

    protected function makeRole($code, $role)
    {
        if ($code == 'ROLE_SUPER_ADMIN') {
            $condition = array(
                'name' => '超级管理员',
                'code' => 'ROLE_SUPER_ADMIN',
                'data' => $role
            );
            return $condition;
        }
        if ($code == 'ROLE_ADMIN') {
            $removeRole = array("admin","admin_user","admin_user_show","admin_user_manage","admin_user_avatar","admin_user_change_password","admin_app","admin_my_cloud","admin_my_cloud_overview","admin_cloud_bill","admin_cloud_video_setting","admin_setting_cloud_video","admin_edu_cloud_sms","admin_edu_cloud_sms_setting","admin_edu_cloud_search_setting","admin_edu_cloud_search","admin_setting_cloud_attachment","admin_cloud_attachment","admin_setting_cloud","admin_setting_my_cloud","admin_system","admin_org_manage","admin_org","admin_roles","admin_role_manage","admin_role_create","admin_role_edit","admin_role_delete","admin_setting","admin_setting_message","admin_setting_theme","admin_setting_mailer","admin_top_navigation","admin_foot_navigation","admin_friendlyLink_navigation","admin_setting_consult_setting","admin_setting_es_bar","admin_setting_share","admin_setting_user","admin_user_auth","admin_setting_login_bind","admin_setting_user_center","admin_setting_user_fields","admin_setting_avatar","admin_setting_course_setting","admin_classroom_setting","admin_setting_course","admin_setting_live_course","admin_setting_questions_setting","admin_setting_course_avatar","admin_setting_operation","admin_article_setting","admin_group_set","admin_invite_set","admin_setting_finance","admin_payment","admin_coin_settings","admin_setting_refund","admin_setting_mobile","admin_setting_mobile_settings","admin_optimize","admin_optimize_settings","admin_jobs","admin_jobs_manage","admin_setting_ip_blacklist","admin_setting_ip_blacklist_manage","admin_setting_post_num_rules","admin_setting_post_num_rules_settings","admin_report_status","admin_report_status_list","admin_logs","admin_logs_query","admin_logs_prod");
            $role = array_diff($role, $removeRole);
            $condition = array(
                'name' => '管理员',
                'code' => 'ROLE_ADMIN',
                'data' => $role
            );
            return $condition;
        }
        if ($code == 'ROLE_TEACHER') {
            $role = array("web","course_manage","course_manage_info","course_manage_base","course_manage_detail","course_manage_picture","course_manage_lesson","course_manage_lesson_create","course_manage_lesson_edit","course_manage_lesson_delete","course_manage_lesson_preview","course_manage_lesson_create_testpaper","course_manage_chapter_create","course_manage_chapter_edit","course_manage_chapter_delete","course_manage_lesson_create_homework","course_manage_lesson_delete_homework","course_manage_lesson_create_exercise","course_manage_lesson_delete_exercise","course_manage_lesson_create_material","course_manage_lesson_publish","course_manage_lesson_unpublish","live_course_manage_replay","course_manage_files","course_manage_setting","course_manage_price","course_manage_teachers","course_manage_students","course_manage_student_create","course_manage_questions","course_manage_question","course_manage_testpaper","course_manange_operate","course_manage_data","course_manage_order","classroom_manage","classroom_manage_settings","classroom_manage_set_info","classroom_manage_set_price","classroom_manage_set_picture","classroom_manage_service","classroom_manage_headteacher","classroom_manage_teachers","classroom_manage_assistants","classroom_manage_content","classroom_manage_courses","classroom_manage_students","classroom_manage_testpaper");
            $condition = array(
                'name' => '教师',
                'code' => 'ROLE_TEACHER',
                'data' => $role
            );
            return $condition;            
        }
        if ($code == 'ROLE_USER') {
            $condition = array(
                'name' => '学生',
                'code' => 'ROLE_USER',
                'data' => array()
            );
            return $condition;
        }
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nextExcutedStartTime']) && !empty($conditions['nextExcutedEndTime'])) {
            $conditions['nextExcutedStartTime'] = strtotime($conditions['nextExcutedStartTime']);
            $conditions['nextExcutedEndTime']   = strtotime($conditions['nextExcutedEndTime']);
        } else {
            unset($conditions['nextExcutedStartTime']);
            unset($conditions['nextExcutedEndTime']);
        }

        if (empty($conditions['cycle'])) {
            unset($conditions['cycle']);
        }

        if (empty($conditions['name'])) {
            unset($conditions['name']);
        } else {
            $conditions['nameLike'] = '%'.$conditions['name'].'%';
        }

        return $conditions;
    }

    public function isRoleNameAvalieable($name, $exclude = null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $role = $this->getRoleDao()->getRoleByName($name);
        return $role ? false : true;
    }

    public function isRoleCodeAvalieable($code, $exclude = null)
    {
        // if (empty($code) || in_array($code, array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
        //     return false;
        // }

        if ($code == $exclude) {
            return true;
        }

        $tag = $this->getRoleByCode($code);

        return $tag ? false : true;
    }

    protected function getRoleDao()
    {
        return $this->createDao('Permission:Role.RoleDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
