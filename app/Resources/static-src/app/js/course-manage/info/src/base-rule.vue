<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_setup.rules'|trans }}</div>
        <div role="course-base-rule">
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ 'course.plan_setup.mode'|trans }}
                    <a class="es-icon es-icon-help course-mangae-info__help text-normal" data-trigger="hover"
                       data-toggle="popover"
                       data-container="body" data-placement="top"
                       :data-content="'course.plan_setup.mode.tips'|trans">
                    </a></label>
                <div class="col-sm-8 cd-radio-group mb0">
                    <label class="cd-radio" :class="course.learnMode == value ? 'checked' : ''"
                           :disabled="course.status != 'draft' || course.platform !='self' ? true : false"
                           v-for="(key, value) in learnMode">
                        <input type="radio"
                               name="learnMode"
                               :value="value"
                               v-model="course.learnMode"
                               :disabled="course.status != 'draft' || course.platform !='self' ? true : false"
                               data-toggle="cd-radio"/>
                        {{ key }}
                    </label>
                </div>
            </div>

            <set-rule v-bind:course="course"
                      v-bind:courseSet="courseSet"
                      v-bind:lesson-watch-limit="lessonWatchLimit"
                      v-bind:has-role-admin="hasRoleAdmin"
                      v-bind:has-wechat-notification-manage-role="hasWechatNotificationManageRole"
                      v-bind:wechat-setting="wechatSetting"
                      v-bind:wechat-manage-url="wechatManageUrl">
            </set-rule>

            <div class="form-group">
                <label class="col-sm-2 control-label mbs">
                    {{ 'course.plan_setup.finish_rule'|trans({'taskName': taskName }) }}</label>
                <div class="col-sm-8 cd-radio-group mb0">
                    <label class="cd-radio"
                           :class="course.enableFinish == 1 ? 'checked' : ''"
                           :disabled="course.platform == 'supplier' ? true : false">
                        <input type="radio" data-toggle="cd-radio" name="enableFinish" value="1"
                               :disabled="course.platform == 'supplier' ? true : false">
                        {{ 'course.plan_setup.finish_rule.nothing'|trans }}
                    </label>
                    <label class="cd-radio"
                           :class="course.enableFinish == 0 ? 'checked' : ''"
                           :disabled="course.platform == 'supplier' ? true : false">
                        <input type="radio" data-toggle="cd-radio" name="enableFinish" value="0"
                               :disabled="course.platform == 'supplier' ? true : false">
                        {{ 'course.plan_setup.finish_rule.depend_on_finish_condition'|trans({'taskName': taskName}) }}
                        <a class="es-icon es-icon-help course-mangae-info__help" data-trigger="hover"
                           data-toggle="popover"
                           data-container="body" data-placement="top"
                           :data-content="'course.plan_setup.finish_rule.depend_on_finish_condition_tips'|trans({'taskName': taskName})"></a>
                    </label>
                </div>
            </div>

            <div v-if="courseSet.type == 'live'" class="form-group">
                <div class="col-sm-2 control-label">
                    <label class="control-label-required" for="maxStudentNum-field">{{'course.plan_setup.member_numbers'|trans}}</label>
                </div>
                <div class="col-sm-10 controls">
                    <input type="text" id="maxStudentNum-field" name="maxStudentNum"
                           class="form-control width-input width-input-large"
                           v-model="course.maxStudentNum"
                           :data-live-capacity-url="liveCapacityUrl" data-explain=""> {{'site.data.people'|trans}}
                    <a class="cd-text-sm cd-link-primary" :href="contentCourseRuleUrl" target="_blank">{{'course.plan_setup.member_numbers.view_rule_btn'|trans}}</a>
                </div>
                <div class="col-sm-offset-2 col-sm-10 js-course-rule">
                    <p class="color-warning cd-text-sm mb0 form-error-message"></p>
                </div>
            </div>
            <div v-else>
                <div class="form-group">
                    <label class="col-sm-2 control-label mbs">
                        {{ 'course.marketing_setup.preview.set_task'|trans({'taskName': taskName}) }}
                    </label>
                    <div class="col-sm-8">
                        <ul class="list-group mb0 pb0"
                            :class="freeTaskJsClass">
                            <li v-for="(task) in canFreeTasks"
                                class="task-price-setting-group__item"
                                :class="freeTasks[task.id] != undefined ? 'open': ''">
                                <input type="checkbox" class="mr10" name="freeTaskIds[]" :value="task.id"
                                       :checked="freeTasks[task.id] != undefined ? true: false"
                                       :disabled="course.platform != 'self' ? true: false">
                                <!--                                {% set meta = activity_meta(task.type) %}-->
                                <i class="es-icon es-icon-video color-gray" data-toggle="tooltip" data-placement="top"
                                   title=""
                                   data-container="body"
                                   :data-original-title="'course.marketing_setup.preview.set_task.task_name'|trans({'taskName':taskName})"></i>
                                <span class="inline-block vertical-middle text-overflow title">
                                    {{ taskName }} {{ task.number }}：{{ task.title }}
                                </span>
                                <span class="cd-tag cd-tag-orange pull-right price">{{ 'course.marketing_setup.preview.set_task.free'|trans }}</span>
                            </li>
                        </ul>


                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<script>
    import setRule from './marketing/set-rule';

    export default {
        name: "base-rule",
        components: {
            setRule,
        },
        props: {
            course: {},
            courseSet: {},
            lessonWatchLimit: false,
            hasRoleAdmin: false,
            wechatSetting: {},
            hasWechatNotificationManageRole: false,
            wechatManageUrl: '',
            liveCapacityUrl: '',
            contentCourseRuleUrl: '',
            canFreeTasks: {},
            freeTasks: {},
            taskName: '',
        },

        data() {
            let freeTaskJsClass = this.canFreeTasks ? ' task-price-setting-group' : '';
            freeTaskJsClass += (this.course.platform == 'self' ? ' js-task-price-setting' : '');

            return {
                course: {},
                courseSet: {},
                lessonWatchLimit: false,
                hasRoleAdmin: false,
                wechatSetting: {},
                hasWechatNotificationManageRole: false,
                wechatManageUrl: '',
                liveCapacityUrl: '',
                contentCourseRuleUrl: '',
                canFreeTasks: {},
                freeTasks: {},
                learnMode: {
                    freeMode: Translator.trans('course.plan_setup.mode.free'),
                    lockMode: Translator.trans('course.plan_setup.mode.locked'),
                },
                freeTaskJsClass: freeTaskJsClass,
                taskName: ''
            };
        }
    }
</script>

<style scoped>

</style>