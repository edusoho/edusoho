<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_setup.rules'|trans }}</div>
        <div role="course-base-rule">
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ 'course.plan_setup.mode'|trans }}
                    <el-popover
                        placement="top"
                        :content="'course.plan_setup.mode.tips'|trans"
                        trigger="hover">
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                    </el-popover>
                </label>
                <div class="col-sm-8 cd-radio-group mb0">
                    <label class="cd-radio" :class="course.learnMode == value ? 'checked' : ''"
                           :disabled="course.status != 'draft' || course.platform !='self' ? true : false"
                           v-for="(key, value) in learnModeRadio">
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
                      v-bind:wechat-manage-url="wechatManageUrl"
                      v-bind:course-remind-send-days="courseRemindSendDays">
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
                        <el-popover
                            placement="top"
                            :content="'course.plan_setup.finish_rule.depend_on_finish_condition_tips'|trans({'taskName': taskName})"
                            trigger="hover">
                            <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                        </el-popover>
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
                                <i class="color-gray"
                                   :class="activityMetas[task.type].icon"
                                   data-toggle="tooltip" data-placement="top"
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

                <div v-if="uploadMode != 'local'">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">
                            {{ 'course.marketing_setup.preview.try_watch'|trans }}
                            <el-popover
                                placement="top"
                                :content="'course.marketing_setup.preview.try_watch_tips'|trans"
                                trigger="hover">
                                <a class="es-icon es-icon-help course-mangae-info__help text-normal"
                                   slot="reference"></a>
                            </el-popover>
                        </label>
                        <div class="col-sm-8">
                            <select :disabled="course.platform != 'self'"
                                    class="form-control course-mange-info__select mh5" id="tryLookLength"
                                    v-model="course.tryLookLength"
                                    name="tryLookLength">
                                <option value="0">{{ 'course.marketing_setup.preview.not.support.try_watch'|trans }}
                                </option>
                                <option v-for="(i) in (1,2,3,4,5,6,7,8,9,10)" :value="i">
                                    {{ i }}{{ 'course.marketing_setup.preview.minutes.try_watch'|trans }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group js-services">
                <label class="col-sm-2 control-label">
                    {{ 'course.marketing_setup.services.provide_services'|trans }}
                </label>
                <div class="col-sm-8 form-control-static">
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
                    <input type="hidden" name="services" id="course_services" :value="course.services|json_encode">
                </div>
            </div>

            <div v-if="audioServiceStatus != 'needOpen' && course.type == 'normal'" class="form-group"
                 id="audio-modal-id">
                <label for="" class="col-sm-2 control-label">
                    {{ 'course.info.video.convert.audio.enable'|trans }}
                </label>
                <div class="col-sm-8 cd-radio-group" :data-value="audioServiceStatus" id="course-audio-mode">
                    <label class="cd-radio" :class="course.enableAudio == value ? 'checked' : ''"
                           :disabled="course.platform =='supplier' ? true : false"
                           v-for="(key, value) in audioServiceStatusRadio">
                        <input type="radio"
                               data-toggle="cd-radio" name="enableAudio"
                               :value="value"
                               v-model="course.learnMode"
                               @click="changeAudioMode"
                               :disabled="course.platform =='supplier' ? true : false"/>
                        {{ key }}
                    </label>
                    <div>
                        <div class="course-mangae-info__tip">
                            1.{{ 'course.enable.video.convert.audio.benefit'|trans }}
                        </div>
                        <div class="course-mangae-info__tip">
                            2.{{ 'course.video.convert.audio.status'|trans }} ：{{ videoConvertCompletion }}
                            <a class="ml5 link-primary" :href="courseSetManageFilesUrl" target="__blank">
                                {{ 'course.video.convert.audio.detail'|trans }}
                            </a>
                        </div>
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
        },
        watch: {},
        methods: {
            serviceItemClick(event) {
                let $item = $(event.currentTarget);
                console.log($item);
                console.log($item.hasClass('service-primary-item'));
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
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>