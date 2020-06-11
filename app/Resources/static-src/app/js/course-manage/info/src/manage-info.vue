<template>

    <div>
        <el-from :model="courseInfoForm" class="form-horizontal" role="form" id="course-info-form" ref="courseInfoFrom">
            <base-info v-bind:course="course"
                       v-bind:has-mul-courses="hasMulCourses"
            ></base-info>
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

            <el-button type="primary" @click="submitForm"></el-button>

            <!--            <div class="form-group">-->
            <!--                <div class="col-sm-offset-2 col-sm-8">-->
            <!--                    <button id="course-submit" type="button" class="cd-btn cd-btn-primary"-->
            <!--                            data-loading-text="{{ 'form.btn.save.submiting'|trans }}">{{ 'form.btn.save'|trans }}-->
            <!--                    </button>-->
            <!--                    <div id="test"></div>-->
            <!--                </div>-->
            <!--            </div>-->

            <!--            {% if not hasMulCourses and courseSet.type == 'normal' %}-->
            <!--            <div class="course-manage-intro js-plan-intro hidden">-->
            <!--                <div class="course-manage-intro__outer js-plan-intro-btn">-->
            <!--                    <div class="course-manage-intro__inner"><i class="es-icon es-icon-zhinan"></i></div>-->
            <!--                </div>-->
            <!--                <div class="mtm course-manage-intro__tip">{{ 'course.base_plan_intro'|trans }}</div>-->
            <!--            </div>-->
            <!--            {% endif %}-->
            <!--            <input type="hidden" name="_csrf_token" value="{{ csrf_token('site') }}">-->
        </el-from>
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
        methods: {
            submitForm() {
                console.log($('#course-info-form'));
            }

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
                buyBeforeApproval: false,
            }
        }
    }
</script>

<style>

</style>