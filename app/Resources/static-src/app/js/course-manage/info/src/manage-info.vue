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
                   v-bind:activity-metas="activityMetas"
                   v-bind:course-remind-send-days="courseRemindSendDays"
                   v-bind:upload-mode="uploadMode"
                   v-bind:service-tags="serviceTags"
                   v-bind:audio-service-status="audioServiceStatus"
                   v-bind:video-convert-completion="videoConvertCompletion"
                   v-bind:course-set-manage-files-url="courseSetManageFilesUrl"
                   v-bind:content-course-rule-url="contentCourseRuleUrl"
        ></base-rule>
        <market-setting v-bind:course="course"
                        v-bind:course-product="courseProduct"
                        v-bind:can-modify-course-price="canModifyCoursePrice"
                        v-bind:notifies="notifies"
                        v-bind:buy-before-approval="buyBeforeApproval"
        ></market-setting>
    </div>

</template>

<script>
    import baseInfo from './base-info';
    import baseRule from './base-rule';
    import marketSetting from './marketing/market-setting'

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
            liveCapacityUrl: '',
            contentCourseRuleUrl: '',
            canFreeTasks: {},
            freeTasks: {},
            taskName: '',
            activityMetas: {},
            courseRemindSendDays: '',
            uploadMode: '',
            serviceTags: {},
            audioServiceStatus: '',
            videoConvertCompletion: '',
            courseSetManageFilesUrl: '',
            courseProduct: {},
            notifies: {},
            canModifyCoursePrice: true,
            buyBeforeApproval: false

        },
        components: {
            baseInfo,
            baseRule,
            marketSetting,
        },
        data() {
            return {
                course: this.course,
                courseSet: {},
                lessonWatchLimit: false,
                hasRoleAdmin: false,
                wechatSetting: {},
                hasWechatNotificationManageRole: false,
                hasMulCourses: false,
                wechatManageUrl: '',
                liveCapacityUrl: '',
                contentCourseRuleUrl: '',
                canFreeTasks: {},
                freeTasks: {},
                taskName: '',
                activityMetas: {},
                courseRemindSendDays: '',
                uploadMode: '',
                serviceTags: [],
                audioServiceStatus: '',
                videoConvertCompletion: '',
                courseSetManageFilesUrl: '',
                courseProduct: {},
                notifies: {},
                canModifyCoursePrice: true,
                buyBeforeApproval: false

            }
        }
    }
</script>

<style>

</style>