<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_setup.rules'|trans }}</div>
        <el-form ref="baseRuleForm" :model="baseRuleForm" :rules="formRule" role="course-base-rule"
                 label-position="right"
                 label-width="150px">
            <el-form-item prop="learnMode">
                <label slot="label">{{ 'course.plan_setup.mode'|trans }}
                    <el-popover
                        placement="top"
                        trigger="hover">
                        <span v-html="learnModeTips"></span>
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal"
                           slot="reference"></a>
                    </el-popover>
                </label>
                <el-col :span="18">
                    <el-radio v-model="baseRuleForm.learnMode"
                              v-for="(label, value) in learnModeRadio"
                              ref="learnMode"
                              :key="value"
                              :label="value"
                              :disabled="course.status !== 'draft' || course.platform !== 'self'"
                              class="cd-radio">{{ label }}
                    </el-radio>
                </el-col>
            </el-form-item>

            <el-form-item prop="watchLimit" v-if="lessonWatchLimit" :label="'course.marketing_setup.rule.watch_time_limit'|trans">
                <el-col :span="2">
                    <el-input v-model="baseRuleForm.watchLimit" ref="watchLimit"></el-input>
                </el-col>
                <el-col :span="8" class="mlm">{{ 'course.marketing_setup.rule.watch_time_limit.watch_limit'|trans }}
                    <el-popover width="300"
                        placement="top"
                        trigger="hover">
                        <span v-html="watchLimitTip"></span>
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                    </el-popover>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.plan_setup.finish_rule'|trans({'taskName': taskName })">
                <el-col :span="18">
                    <el-radio v-model="baseRuleForm.enableFinish" label="1" :disabled="course.platform === 'supplier'"
                              class="cd-radio">
                        {{ 'course.plan_setup.finish_rule.nothing'|trans }}
                    </el-radio>
                    <el-radio v-model="baseRuleForm.enableFinish" label="0" :disabled="course.platform === 'supplier'"
                              class="cd-radio">
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

            <div v-if="courseSet.type != 'live'">
                <el-form-item :label="'course.marketing_setup.preview.set_task'|trans({'taskName': taskName})">
                    <el-col :span="16">
                        <ul v-if="canFreeTasks.length" class="list-group mb0 pb0 js-task-price-setting-scroll"
                            :class="freeTaskJsClass">
                            <el-scrollbar :style="scrollLength(canFreeTasks.length)">
                                <el-checkbox-group v-model="baseRuleForm.freeTaskIds">
                                    <li v-for="(task) in canFreeTasks"
                                        class="task-price-setting-group__item"
                                        :class="{'open': freeTasks[task.id]}"
                                        :data-id="task.id"
                                        :key="task.id">
                                        <el-checkbox :label="task.id" :key="task.id"
                                                     @change="(value) => freeTaskItemChange(task.id)(value)">
                                            <div slot="default">
                                                <el-tooltip effect="dark"
                                                            :content="'course.marketing_setup.preview.set_task.task_name'|trans({'name':activityMetas[task.type].name,'taskName':taskName})"
                                                            placement="top">
                                                    <i class="color-gray" :class="activityMetas[task.type].icon"></i>
                                                </el-tooltip>
                                                <span class="inline-block vertical-middle text-overflow title">
                                                                                {{ taskName }} {{ task.number }}：{{ task.title }}
                                                                            </span>
                                                <span class="cd-tag cd-tag-orange pull-right price">{{ 'course.marketing_setup.preview.set_task.free'|trans }}</span>
                                            </div>
                                        </el-checkbox>
                                    </li>
                                </el-checkbox-group>
                            </el-scrollbar>
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

                <el-form-item v-if="uploadMode !== 'local'">
                    <label slot="label">{{ 'course.marketing_setup.preview.try_watch'|trans }}
                        <el-popover
                            placement="top"
                            :content="'course.marketing_setup.preview.try_watch_tips'|trans"
                            trigger="hover">
                            <a class="es-icon es-icon-help course-mangae-info__help text-normal"
                               slot="reference"></a>
                        </el-popover>
                    </label>
                    <el-select v-model="baseRuleForm.tryLookLength" :disabled="course.platform !== 'self'">
                        <el-option
                            v-for="(label, value) in tryLookLengthOptions"
                            :key="value"
                            :label="label"
                            :value="value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </div>

            <el-form-item id="audio-modal-id"
                          :label="'course.info.video.convert.audio.enable'|trans"
                          v-model="baseRuleForm.enableAudio"
                          v-if="audioServiceStatus !== 'needOpen' && course.type === 'normal'">
                <el-col :span="16">
                    <el-radio v-model="baseRuleForm.enableAudio"
                              v-for="audioServiceStatusRadio in audioServiceStatusRadios"
                              class="cd-radio"
                              :key="audioServiceStatusRadio.value"
                              :label="audioServiceStatusRadio.value"
                              @click="changeAudioMode"
                              :disabled="course.platform ==='supplier'">{{ audioServiceStatusRadio.label }}
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
                    <div class="course-mangae-info__tip">
                        3.{{ 'course.enable.video.convert.audio.un_supported'|trans }}
                    </div>
                </el-col>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

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
            activityMetas: {},
            audioServiceStatus: '',
            videoConvertCompletion: '',
            courseSetManageFilesUrl: '',
            canFreeActivityTypes: '',
            freeTaskChangelog: ''
        },
        watch: {},
        methods: {
            scrollLength(length) {
                return length >= 5 ? "height: 225px;" : `height:${length * 50}px;`
            },
            freeTaskItemChange(id) {
                return (value) => {
                    this.$set(this.freeTasks, id, value)
                }
            },
            changeAudioMode(event) {
                if ($('#course-audio-mode').data('value') === 'notAllowed') {
                    let enableAudios = $("[name='enableAudio']");
                    cd.message({type: 'info', message: Translator.trans('course.audio.enable.biz.user')});
                    enableAudios[0].checked = true;
                    enableAudios[1].checked = false;
                }
            },
            validateForm() {
                let result = false;
                let invalids = {};
                this.$refs.baseRuleForm.clearValidate();

                this.$refs.baseRuleForm.validate((valid, invalidFields) => {
                    if (valid) {
                        result = true;
                    } else {
                        invalids = invalidFields;
                    }
                });

                return {result: result, invalidFields: invalids};
            },
            getFormData() {
                return this.baseRuleForm;
            }
        },
        data() {
            let freeTaskJsClass = this.canFreeTasks ? ' task-price-setting-group' : '';
            freeTaskJsClass += (this.course.platform === 'self' ? ' js-task-price-setting' : '');

            let tryLookLengthOptions = [];
            let i = 0;
            while (i < 11) {
                tryLookLengthOptions[i] = i > 0 ? i + Translator.trans('course.marketing_setup.preview.minutes.try_watch') : Translator.trans('course.marketing_setup.preview.not.support.try_watch');
                i++;
            }

            let liveCapacity = null;
            return {
                liveCapacity: liveCapacity,
                learnModeRadio: {
                    freeMode: Translator.trans('course.plan_setup.mode.free'),
                    lockMode: Translator.trans('course.plan_setup.mode.locked'),
                },
                learnModeTips: Translator.trans('course.plan_setup.mode.tips'),
                freeTaskJsClass: freeTaskJsClass,
                audioServiceStatusRadios: [
                    {
                        value: '1',
                        label: Translator.trans('course.info.video.convert.audio.start'),
                    },
                    {
                        value: '0',
                        label:  Translator.trans('course.info.video.convert.audio.close'),
                    }
                ],
                baseRuleForm: {
                    learnMode: this.course.learnMode,
                    watchLimit: this.course.watchLimit,
                    enableFinish: this.course.enableFinish,
                    tryLookLength: parseInt(this.course.tryLookLength),
                    enableAudio: this.course.enableAudio,
                    freeTaskIds: Object.keys(this.freeTasks)
                },
                formRule: {
                    watchLimit: {
                        validator: validation.digits_0,
                        message: Translator.trans('validate.unsigned_integer.message'),
                        trigger: 'blur'
                    },

                },
                tryLookLengthOptions: tryLookLengthOptions,
                watchLimitTip: Translator.trans('course.marketing_setup.rule.watch_time_limit.watch_limit_tips'),
                today: Date.now(),
                dateOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    }
                }
            };
        },
        mounted() {
        },
        created() {

        }
    }
</script>

<style scoped>

</style>
