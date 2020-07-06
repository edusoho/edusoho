<template>

    <div class="course-manage-info">
        <base-info ref="baseInfo"
                   v-bind:course="course"
                   v-bind:course-set="courseSet"
                   v-bind:has-mul-courses="hasMulCourses"
                   v-bind:is-un-multi-course-set="isUnMultiCourseSet"
                   v-bind:tags="tags"
                   v-bind:image-save-url="imageSaveUrl"
                   v-bind:image-src="imageSrc"
                   v-bind:image-upload-url="imageUploadUrl"
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
                   v-bind:live-capacity-url="liveCapacityUrl"
        ></base-rule>
        <market-setting ref="marketing"
                        v-bind:course="course"
                        v-bind:course-product="courseProduct"
                        v-bind:can-modify-course-price="canModifyCoursePrice"
                        v-bind:notifies="notifies"
                        v-bind:buy-before-approval="buyBeforeApproval"
                        v-bind:vip-installed="vipInstalled"
                        v-bind:vip-enabled="vipEnabled"
                        v-bind:vip-levels="vipLevels"
        ></market-setting>

        <el-row>
            <el-col span="18" offset="6">
                <button class="cd-btn cd-btn-primary" @click="submitForm">{{ 'form.btn.save'|trans }}</button>
            </el-col>
        </el-row>
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
            imageSaveUrl: '',
            imageSrc: '',
            imageUploadUrl: '',
            vipInstalled: false,
            vipEnabled: false,
            vipLevels: {},
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

                this.$axios.post(this.courseManageUrl, this.$qs.stringify(formData), {emulateJSON: true}).then((res) => {
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
                imageSaveUrl: '',
                imageSrc: '',
                imageUploadUrl: '',
                vipInstalled: false,
                vipEnabled: false,
                vipLevels: {},
            }
        }
    }
</script>

<style>

</style>