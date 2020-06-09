<template>
    <div>
        <div class="form-group mb0">
            <label class="col-sm-2 control-label">
                {{ 'course.marketing_setup.rule.expiry_date'|trans }}
                <a class="es-icon es-icon-help course-mangae-info__help text-normal" data-trigger="hover"
                   data-toggle="popover"
                   data-container="body" data-placement="top"
                   data-content="TODO:Trans"
                >
                </a>
            </label>
            <div class="col-sm-10 cd-radio-group mbm">
                <label class="cd-radio" :class="course.expiryMode == value ? 'checked' : ''"
                       :disabled="coursePublished || courseClosed || course.platform !='self' ? true : false"
                       v-for="(key, value) in expiryMode">
                    <input type="radio"
                           name="expiryMode"
                           :value="value"
                           v-model="course.expiryMode"
                           :disabled="coursePublished || courseClosed || course.platform !='self' ? true : false"
                           data-toggle="cd-radio"/>
                    {{ key }}
                </label>
                <input
                    v-if="courseClosed || (courseSetClosed && course.expiryMode == 'days') && course.status != 'draft'"
                    type="hidden" name="expiryMode" v-model="course.expiryMode">

                <div class="course-manage-expiry"
                     :class="{'hidden': course.expiryMode != 'days'}"
                     id="expiry-days">
                    <span class="caret"></span>
                    <label class="cd-radio" :class="course.deadlineType == value ? 'checked' : ''"
                           :disabled="coursePublished || courseClosed || course.platform !='self' ? true : false"
                           v-for="(key, value) in deadlineType">
                        <input type="radio"
                               name="deadlineType"
                               :value="value" v-bind:course="course"
                               v-bind:courseSet="courseSet"
                               v-bind:lesson-watch-limit="lessonWatchLimit"
                               v-bind:has-role-admin="hasRoleAdmin"
                               v-bind:has-wechat-notification-manage-role="hasWechatNotificationManageRole"
                               v-model="course.deadlineType"
                               :disabled="coursePublished || courseClosed || course.platform !='self'"
                               data-toggle="cd-radio"/>
                        {{key}}
                    </label>
                    <input v-if="coursePublished || courseClosed" type="hidden" name="deadlineType"
                           v-model="course.deadlineType">

                    <div class="cd-mt16"
                         :class="{'hidden': course.deadlineType != 'end_date'}">
                        <input :disabled="course.platform != 'self' ? true : false"
                               v-model="course.expiryEndDate"
                               autocomplete="off" class="form-control course-mangae-info__input js-expiry-input cd-mr8"
                               id="deadline"
                               name="deadline"
                        />{{ 'course.marketing_setup.rule.expiry_date_tips'|trans }}
                    </div>

                    <div class="cd-mt16"
                         :class="{'hidden': course.deadlineType != 'days'}"
                         id="deadlineType-days">
                        <input
                            :disabled="(coursePublished && courseSetPublished) || course.platform != 'self' ? true : false"
                            class="form-control course-mangae-info__input js-expiry-input cd-mr8" type="text"
                            id="expiryDays"
                            name="expiryDays"
                            v-model="course.expiryDays"/>
                        {{ 'course.marketing_setup.rule.expiry_date.publish_tips'|trans }}
                    </div>
                </div>

                <div class="course-manage-expiry"
                     :class="{'hidden': course.expiryMode != 'date'}"
                     id="expiry-date">
                    <span class="caret"></span>
                    <div class="course-manage-expiry__circle">
                        {{ 'course.plan_task.start_time'|trans }}
                        <input
                            :disabled="(coursePublished && courseSetPublished) || course.platform != 'self' ? true : false"
                            v-model="course.expiryStartDate"
                            class="form-control cd-ml16 cd-mr32 course-mangae-info__input js-expiry-input"
                            id="expiryStartDate"
                            type="text" name="expiryStartDate">{{ 'course.plan_task.end_time'|trans }}

                        <input
                            :disabled="(coursePublished && courseSetPublished) || course.platform != 'self' ? true : false"
                            v-model="course.expiryEndDate"
                            class="form-control cd-ml16 course-mangae-info__input js-expiry-input" type="text"
                            id="expiryEndDate"
                            name="expiryEndDate">
                    </div>
                </div>

                <div class="course-mangae-info__tip js-expiry-tip"
                     :class="{'ml0': course.expiryMode == 'forever'}">
                    {{ 'course.marketing_setup.rule.expiry_date.first_publish_tips'|trans }}
                </div>
            </div>
        </div>

        <div class="form-group mtl" v-if="lessonWatchLimit">
            <label class="col-sm-2 control-label">
                {{ 'course.marketing_setup.rule.watch_time_limit'|trans }}
            </label>
            <div class="col-sm-8">
                <input class="form-control course-mangae-info__input mrs" type="text" name="watchLimit"
                       v-model="course.watchLimit">
                {{ 'course.marketing_setup.rule.watch_time_limit.watch_limit'|trans }}
                <a class="es-icon es-icon-help text-normal course-mangae-info__help" data-container="body"
                   data-toggle="popover"
                   data-trigger="hover" data-placement="top"
                   :data-content="'course.marketing_setup.rule.watch_time_limit.watch_limit_tips'|trans">
                </a>
            </div>
        </div>

        <div class="form-group mtl" v-if="hasRoleAdmin">
            <label class="col-sm-2 control-label">
                {{ 'course.setting.course_remind'|trans }}
            </label>
            <div class="col-sm-8">
                <div v-if="wechatSetting.templates.courseRemind && wechatSetting.templates.courseRemind.status"
                     class="help-block course-mange-space">
                    {{
                    'course.setting.course_remind_tip'|trans({'D':courseRemindSendDays,'H':wechatSetting.templates.courseRemind.sendTime})
                    }}
                    <!--                    {{ 'course.setting.course_remind_tip'|trans({'%D%': days, '%H%': wechatSetting.templates.courseRemind.sendTime }) }}-->
                    <a v-if="hasWechatNotificationManageRole"
                       data-container="body" data-toggle="popover" data-trigger="hover" :href="wechatManageUrl"
                       target="_blank">{{ 'course.setting.course_remind_change'|trans }}</a>
                </div>
                <div v-else class="help-block course-mange-space">{{ 'course.setting.course_remind_not_open'|trans }}
                    <a v-if="hasWechatNotificationManageRole"
                       data-container="body" data-toggle="popover" data-trigger="hover" v-bind:href="wechatManageUrl"
                       target="_blank">{{ 'course.setting.course_remind_go_to_open'|trans }}</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "set-rule",
        props: {
            course: {},
            courseSet: {},
            lessonWatchLimit: false,
            hasRoleAdmin: false,
            wechatSetting: {},
            hasWechatNotificationManageRole: false,
            wechatManageUrl: '',
            courseRemindSendDays: '',
        },
        data() {
            let coursePublished = this.course.status ? this.course.status == 'published' : false;
            let courseClosed = this.course.status ? this.course.status == 'closed' : false;
            let courseSetPublished = this.courseSet.status ? this.courseSet.status == 'published' : false;
            let courseSetClosed = this.courseSet.status ? this.courseSet.status == 'closed' : false;

            return {
                course: this.course,
                courseSet: this.courseSet,
                lessonWatchLimit: this.lessonWatchLimit ? this.lessonWatchLimit : false,
                hasRoleAdmin: this.hasRoleAdmin ? this.hasRoleAdmin : false,
                wechatSetting: this.wechatSetting,
                hasWechatNotificationManageRole: this.hasWechatNotificationManageRole ? this.hasWechatNotificationManageRole : false,
                wechatManageUrl: '',
                courseRemindSendDays: '',
                expiryMode: {
                    'days': Translator.trans('course.teaching_plan.expiry_date.anywhere_mode'),
                    'date': Translator.trans('course.teaching_plan.expiry_date.date_mode'),
                    'forever': Translator.trans('course.teaching_plan.expiry_date.forever_mode')
                },
                deadlineType: {
                    'end_date': Translator.trans('course.teaching_plan.expiry_date.end_date_mode'),
                    'days': Translator.trans('course.teaching_plan.expiry_date.days_mode')
                },
                courseClosed: courseClosed,
                coursePublished: coursePublished,
                courseSetClosed: courseSetClosed,
                courseSetPublished: courseSetPublished,
            }
        }
    }
</script>

<style scoped>

</style>