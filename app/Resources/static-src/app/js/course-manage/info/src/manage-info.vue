<template>

    <div class="course-manage-info">
        <base-info ref="baseInfo"
                   v-bind:course="course"
                   v-bind:has-mul-courses="hasMulCourses"
                   v-bind:is-un-multi-course-set="isUnMultiCourseSet"
                   v-bind:tags="tags"
                   v-bind:tag-search-url="tagSearchUrl"
        ></base-info>
        <base-rule ref="baseRule"
                   v-bind:course="course"
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
                   v-bind:can-free-activity-types="canFreeActivityTypes"
                   v-bind:free-task-changelog="freeTaskChangelog"
        ></base-rule>
        <market-setting ref="marketing"
                        v-bind:course="course"
                        v-bind:course-product="courseProduct"
                        v-bind:can-modify-course-price="canModifyCoursePrice"
                        v-bind:notifies="notifies"
                        v-bind:buy-before-approval="buyBeforeApproval"
        ></market-setting>

        <button class="cd-btn cd-btn-primary" @click="submitForm">{{ 'form.btn.save'|trans }}</button>
    </div>

</template>

<script>
    import baseInfo from './base-info';
    import baseRule from './base-rule';
    import marketSetting from './marketing/market-setting'

    export default {
        name: "manage-info",
        props: {
            courseManageUrl: '',
            course: {},
            courseSet: {},
            isUnMultiCourseSet: false,
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
            buyBeforeApproval: false,
            canFreeActivityTypes: '',
            freeTaskChangelog: '',
            tags: '',
            tagSearchUrl: ''
        },
        components: {
            baseInfo,
            baseRule,
            marketSetting,
        },
        methods: {
            validForm() {
                let invalidField = '';
                let valids = {
                    baseInfo: this.$refs.baseInfo.validateForm(),
                    baseRule: this.$refs.baseRule.validateForm(),
                    marketing: this.$refs.marketing.validateForm()
                };

                for (let key in valids) {
                    if (!valids[key].result) {
                        for (let field in valids[key].invalidFields) {
                            invalidField = field;
                            this.$refs[key].$refs[invalidField].focus();
                            return false;
                        }
                    }
                }

                return true;
            },
            submitForm() {
                if (!this.validForm()) {
                    return;
                }

                let formData = {'_csrf_token': $('meta[name=csrf-token]').attr('content')};
                Object.assign(
                    formData,
                    this.$refs.baseInfo.getFormData(),
                    this.$refs.baseRule.getFormData(),
                    this.$refs.marketing.getFormData()
                );

                this.$axios.post(this.courseManageUrl, formData, {emulateJSON: true}).then((res) => {
                    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
                    window.location.reload();

                });
            }

        },
        data() {
            return {
                courseManageUrl: '',
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
                canFreeActivityTypes: '',
                freeTaskChangelog: '',
                isUnMultiCourseSet: false,
                tags: '',
                tagSearchUrl: ''
            }
        }
    }
</script>

<style>

</style>