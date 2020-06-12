<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_setup.rules'|trans }}</div>
        <el-form ref="baseRuleForm" v-model="ruleForm" role="course-base-rule" label-position="right" label-width="150px">
            <el-form-item prop="learnMode">
                <label slot="label">{{ 'course.plan_setup.mode'|trans }}
                    <el-popover
                        placement="top"
                        :content="'course.plan_setup.mode.tips'|trans"
                        trigger="hover">
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal"
                           slot="reference"></a>
                    </el-popover>
                </label>
                <el-col span="18">
                    <el-radio v-model="ruleForm.learnMode"
                              v-for="(label, value) in learnModeRadio"
                              :key="value"
                              :label="value"
                              :disabled="course.status != 'draft' || course.platform !='self'">{{ label }}
                    </el-radio>
                </el-col>
            </el-form-item>

            <el-form-item>
                <label slot="label">
                    {{ 'course.marketing_setup.rule.expiry_date'|trans }}
                    <el-popover
                        placement="top"
                        trigger="hover">
                        <ul class='pl10 list-unstyled'>
                            <li class='mb10'>{{ 'course.teaching_plan.expiry_date.anytime'|trans }}</li>
                            <li class='mb10'>{{ 'course.teaching_plan.expiry_date.real_time'|trans }}</li>
                            <li>{{ 'course.teaching_plan.expiry_date.overdue_tips'|trans }}</li>
                        </ul>
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                    </el-popover>
                </label>
                <el-col span="18">
                    <el-radio v-for="(label, value) in expiryMode"
                              v-model="ruleForm.expiryMode"
                              :label="value"
                              :key="value"
                              :disabled="coursePublished || courseClosed || course.platform !='self'">
                        {{label}}
                    </el-radio>

                    <div class="course-manage-expiry" :class="{'hidden':ruleForm.expiryMode != 'days'}"
                         id="expiry-days">
                        <span class="caret"></span>
                        <el-radio v-model="ruleForm.deadlineType"
                                  v-for="(label, value) in deadlineTypeRadio"
                                  :disabled="coursePublished || courseClosed || course.platform !='self'"
                                  :label="value"
                                  :key="value">
                            {{label}}
                        </el-radio>

                        <div class="cd-mt16" :class="{'hidden': ruleForm.deadlineType != 'end_date'}">
                            <el-date-picker
                                v-model="ruleForm.deadline"
                                type="date"
                                size="small"
                                :default-value="today"
                                :picker-options="dateOptions"
                                :disabled="course.platform != 'self'">
                            </el-date-picker>
                            <span class="mlm">{{ 'course.marketing_setup.rule.expiry_date_tips'|trans }}</span>
                        </div>
                        <div class="cd-mt16"
                             :class="{'hidden' : ruleForm.deadlineType != 'days'}">
                            <el-col span="8">
                                <el-input v-model="ruleForm.expiryDays"
                                          :disabled="(coursePublished && courseSetPublished) || course.platform != 'self'">
                                </el-input>
                            </el-col>
                            <span class="mlm">{{ 'course.marketing_setup.rule.expiry_date.publish_tips'|trans }}</span>
                        </div>
                    </div>

                    <div class="course-manage-expiry"
                         :class="{'hidden': ruleForm.expiryMode != 'date'}">
                        <span class="caret"></span>
                        <div class="course-manage-expiry__circle">
                            {{ 'course.plan_task.start_time'|trans }}
                            <el-date-picker
                                :style="'max-width: 150px;'"
                                :disabled="(coursePublished && courseSetPublished) || course.platform != 'self' ? true : false"
                                v-model="ruleForm.expiryStartDate"
                                :default-value="today"
                                :picker-options="dateOptions"
                                size="small"
                                type="date">
                            </el-date-picker>
                            {{ 'course.plan_task.end_time'|trans }}

                            <el-date-picker
                                :style="'max-width: 150px;'"
                                :disabled="(coursePublished && courseSetPublished) || course.platform != 'self' ? true : false"
                                v-model="ruleForm.expiryEndDate"
                                :default-value="today"
                                :picker-options="dateOptions"
                                size="small"
                                type="date">
                            </el-date-picker>
                            <div class="help-block"></div>
                        </div>
                    </div>
                    <div class="course-mangae-info__tip js-expiry-tip"
                         :class="{'ml0': ruleForm.expiryMode == 'forever'}">
                        {{ 'course.marketing_setup.rule.expiry_date.first_publish_tips'|trans }}
                    </div>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.plan_setup.finish_rule'|trans({'taskName': taskName })">
                <el-col span="18">
                    <el-radio v-model="ruleForm.enableFinish" label="1" :disabled="course.platform == 'supplier'">
                        {{ 'course.plan_setup.finish_rule.nothing'|trans }}
                    </el-radio>
                    <el-radio v-model="ruleForm.enableFinish" label="0" :disabled="course.platform == 'supplier'">
                        {{ 'course.plan_setup.finish_rule.depend_on_finish_condition'|trans({'taskName': taskName}) }}
                        <el-popover
                            placement="top"
                            :content="'course.plan_setup.finish_rule.depend_on_finish_condition_tips'|trans({'taskName': taskName})"
                            trigger="hover">
                            <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                        </el-popover>
                    </el-radio>
                </el-col>
            </el-form-item>

            <el-form-item v-if="courseSet.type == 'live'"
                          :label="'course.plan_setup.member_numbers'|trans">
                <el-col span="8">
                    <el-input v-model="ruleForm.maxStudentNum"
                              :data-live-capacity-url="liveCapacityUrl"
                              data-explain="">
                    </el-input>
                </el-col>
                <el-col span="6" class="mlm">
                    {{'site.data.people'|trans}}
                    <a class="cd-text-sm cd-link-primary" :href="contentCourseRuleUrl" target="_blank">{{'course.plan_setup.member_numbers.view_rule_btn'|trans}}</a>
                </el-col>
            </el-form-item>
            <div v-else>
                <el-form-item :label="'course.marketing_setup.preview.set_task'|trans({'taskName': taskName})">
                    <el-col span="16">
                        <ul v-if="canFreeTasks.length" class="list-group mb0 pb0 js-task-price-setting-scroll"
                            :class="freeTaskJsClass">
                            <li v-for="(task) in canFreeTasks"
                                class="task-price-setting-group__item"
                                :class="{'open': freeTasks[task.id] != undefined}">
                                <input type="checkbox" class="mr10" name="freeTaskIds[]" :value="task.id"
                                       :checked="freeTasks[task.id] != undefined"
                                       :disabled="course.platform != 'self'">
                                <el-tooltip effect="dark"
                                            :content="'course.marketing_setup.preview.set_task.task_name'|trans({'name':activityMetas[task.type].name,'taskName':taskName})"
                                            placement="top">
                                    <i class="color-gray" :class="activityMetas[task.type].icon"></i>
                                </el-tooltip>
                                <span class="inline-block vertical-middle text-overflow title">
                                    {{ taskName }} {{ task.number }}：{{ task.title }}
                                </span>
                                <span class="cd-tag cd-tag-orange pull-right price">{{ 'course.marketing_setup.preview.set_task.free'|trans }}</span>
                            </li>
                        </ul>

                        <div class="help-block course-mange-space" :class="{'mt0': !canFreeTasks.length}">
                            {{ 'course.marketing_setup.preview.set_task.free_tips'|trans({'taskName':taskName}) }}
                            {{ canFreeActivityTypes }}
                            <el-popover
                                placement="right"
                                :content="freeTaskChangelog"
                                trigger="hover">
                                <i v-if="freeTaskChangelog" class="es-icon es-icon-tip admin-update__icon color-danger"
                                   slot="reference"></i>
                            </el-popover>
                        </div>
                    </el-col>
                </el-form-item>

                <el-form-item v-if="uploadMode != 'local'">
                    <label slot="label">{{ 'course.marketing_setup.preview.try_watch'|trans }}
                        <el-popover
                            placement="top"
                            :content="'course.marketing_setup.preview.try_watch_tips'|trans"
                            trigger="hover">
                            <a class="es-icon es-icon-help course-mangae-info__help text-normal"
                               slot="reference"></a>
                        </el-popover>
                    </label>
                    <el-select v-model="ruleForm.tryLookLength" :disabled="course.platform != 'self'">
                        <el-option
                            v-for="(label, value) in tryLookLengthOptions"
                            :key="value"
                            :label="label"
                            :value="value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </div>

            <el-form-item :label="'course.marketing_setup.services.provide_services'|trans">
                <el-col>
                    <el-popover v-for="(tag, key) in serviceTags"
                                placement="top"
                                :key="key"
                                :content="tag.summary|trans"
                                trigger="hover">
                        <span class="service-item js-service-item"
                              slot="reference"
                              :key="key"
                              :class="tag.active ? 'service-primary-item' : ''"
                              :data-code="tag.code"
                              @click="serviceItemClick"
                        >{{ tag.fullName }}</span>
                    </el-popover>
                    <el-input type="hidden" v-model="ruleForm.services"></el-input>
                </el-col>
            </el-form-item>

            <el-form-item id="audio-modal-id"
                          :label="'course.info.video.convert.audio.enable'|trans"
                          v-model="ruleForm.enableAudio"
                          v-if="audioServiceStatus != 'needOpen' && course.type == 'normal'">
                <el-col span="16">
                    <el-radio v-model="ruleForm.enableAudio"
                              v-for="(label, value) in audioServiceStatusRadio"
                              :key="value"
                              :label="value"
                              @click="changeAudioMode"
                              :disabled="course.platform =='supplier'">{{ label }}
                    </el-radio>
                    <div class="course-mangae-info__tip">
                        1.{{ 'course.enable.video.convert.audio.benefit'|trans }}
                    </div>
                    <div class="course-mangae-info__tip">
                        2.{{ 'course.video.convert.audio.status'|trans }} ：{{ videoConvertCompletion }}
                        <a class="ml5 link-primary" :href="courseSetManageFilesUrl" target="__blank">
                            {{ 'course.video.convert.audio.detail'|trans }}
                        </a>
                    </div>
                </el-col>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    export default {
        name: "base-rule",
        components: {},
        filters: {
            json_encode(value) {
                if (!value) return '';
                return JSON.stringify(value);
            }
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
            courseRemindSendDays: '',
            uploadMode: '',
            serviceTags: {},
            activityMetas: {},
            audioServiceStatus: '',
            videoConvertCompletion: '',
            courseSetManageFilesUrl: '',
            canFreeActivityTypes: '',
            freeTaskChangelog: ''
        },
        watch: {},
        methods: {
            serviceItemClick(event) {
                let $item = $(event.currentTarget);
                let $values = $('#course_services').val();
                let values = this.course.services;
                if (!$values) {
                    values = [];
                }

                let code = $item.data('code')
                if ($item.hasClass('service-primary-item')) {
                    $item.removeClass('service-primary-item');
                    values.splice(values.indexOf(code), 1);
                } else {
                    $item.addClass('service-primary-item');

                    if (values.indexOf(code) < 0) {
                        values.push(code);
                    }
                }

                $('#course_services').val(JSON.stringify(values));
            },
            changeAudioMode(event) {
                if ($('#course-audio-mode').data('value') == 'notAllowed') {
                    let enableAudios = $("[name='enableAudio']");
                    cd.message({type: 'info', message: Translator.trans('course.audio.enable.biz.user')});
                    enableAudios[0].checked = true;
                    enableAudios[1].checked = false;
                }
            },
        },
        data() {
            let freeTaskJsClass = this.canFreeTasks ? ' task-price-setting-group' : '';
            freeTaskJsClass += (this.course.platform == 'self' ? ' js-task-price-setting' : '');

            let tryLookLengthOptions = [];
            let i = 0;
            while (i < 11) {
                tryLookLengthOptions[i] = i > 0 ? i + Translator.trans('course.marketing_setup.preview.minutes.try_watch') : Translator.trans('course.marketing_setup.preview.not.support.try_watch');
                i++;
            }

            let ruleForm = {
                learnMode: this.course.learnMode,
                enableFinish: this.course.enableFinish,
                maxStudentNum: this.course.maxStudentNum,
                tryLookLength: parseInt(this.course.tryLookLength),
                services: JSON.stringify(this.course.services),
                enableAudio: this.course.enableAudio,
                expiryMode: this.course.expiryMode,
                deadlineType: this.course.deadlineType ? this.course.deadlineType : 'days',
                deadline: this.course.expiryEndDate > 0 ? this.course.expiryEndDate * 1000 : null,
                expiryDays: this.course.expiryDays,
                expiryStartDate: this.course.expiryStartDate > 0 ? this.course.expiryStartDate * 1000 : null,
                expiryEndDate: this.course.expiryEndDate > 0 ? this.course.expiryEndDate * 1000 : null
            };

            let coursePublished = this.course.status ? this.course.status == 'published' : false;
            let courseClosed = this.course.status ? this.course.status == 'closed' : false;
            let courseSetPublished = this.courseSet.status ? this.courseSet.status == 'published' : false;
            let courseSetClosed = this.courseSet.status ? this.courseSet.status == 'closed' : false;


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
                courseRemindSendDays: '',
                serviceTags: {},
                learnModeRadio: {
                    freeMode: Translator.trans('course.plan_setup.mode.free'),
                    lockMode: Translator.trans('course.plan_setup.mode.locked'),
                },

                freeTaskJsClass: freeTaskJsClass,
                taskName: '',
                activityMetas: {},
                audioServiceStatus: '',
                audioServiceStatusRadio: {
                    1: Translator.trans('course.info.video.convert.audio.start'),
                    0: Translator.trans('course.info.video.convert.audio.close')
                },
                videoConvertCompletion: '',
                courseSetManageFilesUrl: '',
                ruleForm: ruleForm,
                freeTaskIds: [],
                canFreeActivityTypes: '',
                freeTaskChangelog: '',
                tryLookLengthOptions: tryLookLengthOptions,
                expiryMode: {
                    'days': Translator.trans('course.teaching_plan.expiry_date.anywhere_mode'),
                    'date': Translator.trans('course.teaching_plan.expiry_date.date_mode'),
                    'forever': Translator.trans('course.teaching_plan.expiry_date.forever_mode')
                },
                deadlineTypeRadio: {
                    'end_date': Translator.trans('course.teaching_plan.expiry_date.end_date_mode'),
                    'days': Translator.trans('course.teaching_plan.expiry_date.days_mode')
                },
                courseClosed: courseClosed,
                coursePublished: coursePublished,
                courseSetClosed: courseSetClosed,
                courseSetPublished: courseSetPublished,
                today: Date.now(),
                dateOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    }
                }
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>