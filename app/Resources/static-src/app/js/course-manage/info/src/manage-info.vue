<template>

    <div>
        <base-info v-bind:course="course" v-bind:has-mul-courses="hasMulCourses"></base-info>
        <base-rule v-bind:course="course"
                   v-bind:courseSet="courseSet"
                   v-bind:lesson-watch-limit="lessonWatchLimit"
                   v-bind:has-role-admin="hasRoleAdmin"
                   v-bind:has-wechat-notification-manage-role="hasWechatNotificationManageRole"
                   v-bind:wechat-setting="wechatSetting"
                   v-bind:wechat-manage-url="wechatManageUrl"
                   v-bind:can-free-tasks="canFreeTasks"
                   v-bind:free-tasks="freeTasks"
                   v-bind:task-name="taskName"
        ></base-rule>
    </div>

</template>

<script>
    import baseInfo from './base-info';
    import baseRule from './base-rule';

    export default {
        name: "manage-info",
        props: {
            course: {},
            courseSet: {},
            lessonWatchLimit: false,
            hasRoleAdmin: false,
            wechatSetting: {},
            hasWechatNotificationManageRole: false,
            hasMulCourses: false,
            wechatManageUrl: '',
            liveCapacityUrl:'',
            contentCourseRuleUrl:'',
            canFreeTasks: {},
            freeTasks: {},
            taskName: '',
        },
        components: {
            baseInfo,
            baseRule,
        },
        data() {
            return {
                course: {},
                courseSet: {},
                lessonWatchLimit: false,
                hasRoleAdmin: false,
                wechatSetting: {},
                hasWechatNotificationManageRole: false,
                hasMulCourses: false,
                wechatManageUrl: '',
                liveCapacityUrl:'',
                contentCourseRuleUrl:'',
                canFreeTasks: {},
                freeTasks: {},
                taskName: '',
            }
        }
    }
</script>

<style>

</style>