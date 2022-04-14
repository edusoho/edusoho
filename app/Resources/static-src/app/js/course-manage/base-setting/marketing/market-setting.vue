<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.marketing_setup'|trans }}</div>
        <el-form ref="marketSettingForm" :model="marketingForm"
                 :rules="formRule" label-position="right"
                 label-width="150px">
            <div v-if="course.platform === 'supplier'">
                <el-form-item :label="'s2b2c.product.cooperation_price'|trans">
                    <el-col :span="18">
                        {{ courseProduct.cooperationPrice }}
                        <span class="ml5">{{ 'site.currency.CNY'|trans }}</span>
                        <el-popover
                            placement="top"
                            :content="('s2b2c.resource.sync.prev_price.notify'|trans) + (notifies.modifyPrice.data.old.cooperationPrice)"
                            trigger="hover">
                            <i v-if="notifies.modifyPrice"
                               class="es-icon es-icon-tip admin-update__icon v2-color-warning color-danger"
                               slot="reference"></i>
                        </el-popover>
                    </el-col>
                </el-form-item>
                <el-form-item :label="'s2b2c.product.suggestion_price'|trans">
                    <el-col :span="18">
                        {{ courseProduct.suggestionPrice }}
                        <span class="ml5">{{ 'site.currency.CNY'|trans }}</span>
                        <el-popover
                            placement="top"
                            :content="('s2b2c.resource.sync.prev_price.notify'|trans) + (notifies.modifyPrice.data.old.suggestionPrice)"
                            trigger="hover">
                            <i v-if="notifies.modifyPrice"
                               class="es-icon es-icon-tip admin-update__icon v2-color-warning color-danger"
                               slot="reference"></i>
                        </el-popover>
                    </el-col>
                </el-form-item>
            </div>

            <div class="hidden" id="js-course-info"
                 :data-hint-message="course.platform === 'self' ? 'validate_old.positive_currency.message' : 'course_manage.positive_currency.message'"
                 :data-min-price="course.platform === 'self' ? 0 : 0.01">
            </div>

            <el-form-item :label="'site.price'|trans" prop="originPrice">
                <el-col :span="4">
                    <el-input v-model="marketingForm.originPrice" ref="originPrice"
                              :disabled="course.platform === 'supplier' && !canModifyCoursePrice"></el-input>
                </el-col>
                <el-col :span="8" class="mlm">{{ 'site.currency.CNY'|trans }}</el-col>
            </el-form-item>

            <el-form-item>
                <label slot="label">
                    {{ 'course.marketing_setup.setup.can_join'|trans }}
                    <el-popover
                        placement="top"
                        :content="'course.marketing_setup.setup.can_join.tips'|trans"
                        trigger="hover">
                        <a class="es-icon es-icon-help text-normal course-mangae-info__help"
                           slot="reference"></a>
                    </el-popover>
                </label>
                <el-col :span="18">
                    <el-radio v-for="buyableRadio in buyableRadios"
                              v-model="marketingForm.buyable"
                              :key="buyableRadio.value"
                              :value="buyableRadio.value"
                              :label="buyableRadio.value"
                              class="cd-radio">
                        {{ buyableRadio.label }}
                    </el-radio>
                </el-col>
            </el-form-item>
          <el-form-item v-if="courseSet.type === 'live'"
                        :label="'course.plan_setup.member_numbers'|trans"
                        prop="maxStudentNum">
            <el-col :span="8">
              <el-input v-model="marketingForm.maxStudentNum" ref="maxStudentNum"></el-input>
             <div class="course-mangae-info__tip js-expiry-tip ml0">{{ 'course.plan_setup.member_numbers.tips'|trans }}</div>
            </el-col>
            <div v-if="liveCapacity !== null && parseInt(marketingForm.maxStudentNum) > parseInt(liveCapacity)" class="el-form-item__error">
              {{'course.manage.max_capacity_hint'|trans({capacity: liveCapacity})}}
            </div>
          </el-form-item>

          <el-form-item :label="'course.marketing_setup.expiry_date'|trans"
                          :prop="marketingForm.enableBuyExpiryTime == 1 ? 'buyExpiryTime': 'enableBuyExpiryTime'">
                <el-col :span="8">
                    <el-radio v-for="buyExpiryTimeEnabledRadio in buyExpiryTimeEnabledRadios"
                              v-model="marketingForm.enableBuyExpiryTime"
                              :key="buyExpiryTimeEnabledRadio.value"
                              :label="buyExpiryTimeEnabledRadio.value"
                              class="cd-radio">
                        {{buyExpiryTimeEnabledRadio.label}}
                    </el-radio>
                </el-col>
                <el-date-picker v-if="marketingForm.enableBuyExpiryTime == 1"
                                v-model="marketingForm.buyExpiryTime"
                                :default-value="today"
                                :picker-options="dateOptions"
                                value-format="timestamp"
                                size="small"
                                ref="buyExpiryTime"
                                type="date">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <label slot="label">
                    {{ 'course.marketing_setup.rule.expiry_date'|trans }}
                    <el-popover
                        placement="top"
                        trigger="hover">
                        <ul class='pl10 list-unstyled'>
                            <li class='mb10'><span v-html="expiryModeTips.anytime"></span></li>
                            <li class='mb10'><span v-html="expiryModeTips.realtime"></span></li>
                            <li><span v-html="expiryModeTips.overdue"></span></li>
                        </ul>
                        <a class="es-icon es-icon-help course-mangae-info__help text-normal" slot="reference"></a>
                    </el-popover>
                </label>
                <el-col :span="18">
                    <el-radio v-for="(label, value) in expiryMode"
                              v-model="marketingForm.expiryMode"
                              :label="value"
                              :key="value"
                              :disabled="coursePublished || courseClosed || course.platform !== 'self'"
                              class="cd-radio">
                        {{label}}
                    </el-radio>

                    <div class="course-manage-expiry" :class="{'hidden':marketingForm.expiryMode !== 'days'}"
                         id="expiry-days" style="max-width: 600px;">
                        <span class="caret"></span>
                        <el-radio v-model="marketingForm.deadlineType"
                                  v-for="(label, value) in deadlineTypeRadio"
                                  :disabled="coursePublished || courseClosed || course.platform !=='self'"
                                  class="cd-radio"
                                  :label="value"
                                  :key="value">
                            {{label}}
                        </el-radio>

                        <div class="cd-mt16"
                             v-if="marketingForm.expiryMode === 'days' && marketingForm.deadlineType === 'end_date'">
                            <el-form-item prop="deadline">
                                <el-date-picker
                                    v-model="marketingForm.deadline"
                                    type="date"
                                    size="small"
                                    ref="deadline"
                                    :default-value="today"
                                    :picker-options="dateOptions"
                                    :disabled="course.platform !== 'self'">
                                </el-date-picker>
                                <span class="mlm">{{ 'course.marketing_setup.rule.expiry_date_tips'|trans }}</span>
                            </el-form-item>
                        </div>
                        <div class="cd-mt16"
                             v-if="marketingForm.expiryMode === 'days' && marketingForm.deadlineType === 'days'">
                            <el-col :span="8">
                                <el-form-item prop="expiryDays">
                                    <el-input ref="expiryDays" v-model="marketingForm.expiryDays"
                                              :disabled="(coursePublished && courseSetPublished) || course.platform !== 'self'">
                                    </el-input>
                                </el-form-item>
                            </el-col>
                            <span class="mlm">{{ 'course.marketing_setup.rule.expiry_date.publish_tips'|trans }}</span>
                        </div>
                    </div>

                    <div class="course-manage-expiry"
                         :class="{'hidden': marketingForm.expiryMode !== 'date'}" style="max-width: 600px;">
                        <span class="caret"></span>
                        <div class="course-manage-expiry__circle"
                             v-if="marketingForm.expiryMode === 'date' && marketingForm.expiryMode === 'date'">
                            <el-form-item prop="expiryStartDate" style="display: inline-block; margin-left: -10px">
                                <span class="demonstration">{{ 'course.plan_task.start_time'|trans }}</span>
                                <el-date-picker
                                    v-model="marketingForm.expiryStartDate"
                                    type="date"
                                    size="small"
                                    ref="expiryStartDate"
                                    :default-value="today"
                                    :picker-options="startDateOptions"
                                    :disabled="(coursePublished && courseSetPublished) || course.platform !== 'self'">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item prop="expiryEndDate" style="display: inline-block; margin-left: 4px">
                                <span class="demonstration">{{ 'course.plan_task.end_time'|trans }}</span>
                                <el-date-picker
                                    v-model="marketingForm.expiryEndDate"
                                    type="date"
                                    size="small"
                                    ref="expiryEndDate"
                                    :picker-options="endDateOptions"
                                    :disabled="(coursePublished && courseSetPublished) || course.platform !== 'self'">
                                </el-date-picker>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="course-mangae-info__tip js-expiry-tip"
                         :class="{'ml0': marketingForm.expiryMode === 'forever'}">
                        {{ 'course.marketing_setup.rule.expiry_date.first_publish_tips'|trans }}
                    </div>
                </el-col>
            </el-form-item>
            <!--            实名认证有bug，暂不显示-->
            <!--            <el-form-item v-if="buyBeforeApproval">-->
            <!--                <label slot="label">-->
            <!--                    {{ 'course.marketing_setup.approval'|trans }}-->
            <!--                    <el-popover-->
            <!--                        placement="top"-->
            <!--                        :content="'course.marketing_setup.approval_tips'|trans"-->
            <!--                        trigger="hover">-->
            <!--                        <a class="es-icon es-icon-help text-normal course-mangae-info__help"-->
            <!--                           slot="reference"></a>-->
            <!--                    </el-popover>-->
            <!--                </label>-->
            <!--                <el-col :span="18">-->
            <!--                    <el-radio v-for="(label, value) in approvalRadio"-->
            <!--                              v-model="marketingForm.approval"-->
            <!--                              :key="value"-->
            <!--                              :label="value"-->
            <!--                              class="cd-radio">-->
            <!--                        {{label}}-->
            <!--                    </el-radio>-->
            <!--                </el-col>-->

            <!--            </el-form-item>-->
            <el-form-item v-if="vipInstalled && vipEnabled" :label="'vip.level.free_learning_new'|trans">
                <el-select v-model="marketingForm.vipLevelId">
                    <el-option value="0" :label="'site.default.none'|trans"></el-option>
                    <el-option
                        v-if="vipLevels"
                        v-for="(level) in vipLevels"
                        :key="level.id"
                        :label="level.name"
                        :value="level.id">
                    </el-option>
                </el-select>
            </el-form-item>

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
                              :class="tag.active || marketingForm.services.indexOf(tag.code) >= 0 ? 'service-primary-item' : ''"
                              :data-code="tag.code"
                              @click="serviceItemClick"
                        >{{ tag.fullName }}</span>
                    </el-popover>
                </el-col>
                <el-input class="hidden" type="hidden" v-model="marketingForm.services"></el-input>
            </el-form-item>

            <el-form-item :label="'course.marketing_setup.services.course_list_display'|trans">
              <el-col :span="18">
                <el-radio
                  v-for="courseTaskDisplayRadio in courseTaskDisplayRadios"
                  v-model="marketingForm.taskDisplay"
                  :key="courseTaskDisplayRadio.value"
                  :value="courseTaskDisplayRadio.value"
                  :label="courseTaskDisplayRadio.value"
                  class="cd-radio"
                >
                  {{ courseTaskDisplayRadio.label }}
                </el-radio>
              </el-col>
            </el-form-item>

          <el-form-item>
            <label slot="label">
              {{ 'drainage.setting' | trans }}
              <el-popover
                placement="top"
                :content="'drainage.setting_tips' | trans"
                trigger="hover"
              >
                <i class="es-icon es-icon-help text-normal course-mangae-info__help" slot="reference"></i>
              </el-popover>
            </label>
            <el-col :span="18">
              <el-radio
                v-for="drainageRadio in drainageRadios"
                v-model="marketingForm.drainageEnabled"
                :key="drainageRadio.value"
                :value="drainageRadio.value"
                :label="drainageRadio.value"
                class="cd-radio"
              >
                {{ drainageRadio.label }}
              </el-radio>
            </el-col>
          </el-form-item>

          <template v-if="marketingForm.drainageEnabled">
            <el-form-item :label="'drainage.qr_setting' | trans" prop="drainageImage">
              <el-col :span="18">
                <el-upload
                  action=""
                  class="qr-uploader"
                  :show-file-list="false"
                  :http-request="customUploadImage"
                >
                  <img v-if="marketingForm.drainageImage" :src="marketingForm.drainageImage" class="qr">
                  <i v-else class="el-icon-plus qr-uploader-icon"></i>
                  <div slot="tip" class="el-upload__tip">{{ 'drainage.upload_tips' | trans }}</div>
                </el-upload>
              </el-col>
            </el-form-item>

            <el-form-item :label="'drainage.text' | trans">
              <el-col :span="18">
                <el-input
                  type="text"
                  :placeholder="'drainage.placeholder' | trans"
                  v-model="marketingForm.drainageText"
                  maxlength="20"
                  show-word-limit
                />
              </el-col>
            </el-form-item>

            <el-form-item :label="'drainage.style' | trans">
              <el-col :span="18">
                {{ 'drainage.style_tips' | trans }}
                <el-popover
                  popper-class="el-popover-drainage-img"
                  placement="top-start"
                  trigger="hover"
                >
                  <img src="/static-dist/app/img/vue/drainage.png" alt="">
                  <el-button type="text" slot="reference">{{ 'drainage.view_detail' | trans }}</el-button>
                </el-popover>
              </el-col>
            </el-form-item>
          </template>
        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

    export default {
        name: "market-setting",
        props: {
            course: {},
            courseSet: {},
            courseProduct: {},
            notifies: {},
            liveCapacityUrl: '',
            canModifyCoursePrice: true,
            buyBeforeApproval: false,
            vipInstalled: false,
            vipEnabled: false,
            vipLevels: {},
            serviceTags: {},
        },
        watch: {},
        methods: {
            serviceItemClick(event) {
                let $item = $(event.currentTarget);
                if (!this.course.services) {
                    this.course.services = [];
                }

                let code = $item.data('code')
                if ($item.hasClass('service-primary-item')) {
                    $item.removeClass('service-primary-item');
                    this.course.services.splice(this.course.services.indexOf(code), 1);
                } else {
                    $item.addClass('service-primary-item');

                    if (this.course.services.indexOf(code) < 0) {
                        this.course.services.push(code);
                    }
                }
            },
            validateForm() {
                let result = false;
                let invalids = {};
                this.$refs.marketSettingForm.clearValidate();

                this.$refs.marketSettingForm.validate((valid, invalidFields) => {
                    if (valid) {
                        result = true;
                    } else {
                        invalids = invalidFields;
                    }
                });

                return {result: result, invalidFields: invalids};
            },
            getFormData() {
                return this.marketingForm;
            },

            customUploadImage(info) {
              const formData = new FormData();
              formData.append('file', info.file);
              formData.append('group', 'system');
              this.$axios.post('/api/file', formData).then((res) => {
                this.marketingForm.drainageImage = res.data.uri;
              });
            }
        },
        data() {
            this.course.buyExpiryTime = this.course.buyExpiryTime > 0 ? this.course.buyExpiryTime * 1000 : null;
            let coursePublished = this.course.status ? this.course.status === 'published' : false;
            let courseClosed = this.course.status ? this.course.status === 'closed' : false;
            let courseSetPublished = this.courseSet.status ? this.courseSet.status === 'published' : false;
            let courseSetClosed = this.courseSet.status ? this.courseSet.status === 'closed' : false;

            let max_year = (rule, value, callback) => {
                value <= 7300 ? callback() : callback(new Error(Translator.trans('validate.max_year.message')));
            }

            let form = {
                maxStudentNum: this.course.maxStudentNum,
                originPrice: this.course.originPrice,
                buyable: this.course.buyable,
                enableBuyExpiryTime: this.course.buyExpiryTime > 0 ? '1' : '0',
                buyExpiryTime: this.course.buyExpiryTime,
                approval: this.course.approval,
                expiryMode: this.course.expiryMode,
                deadline: this.course.expiryEndDate == 0 ? '' : this.course.expiryEndDate,
                deadlineType: this.course.deadlineType ? this.course.deadlineType : 'days',
                expiryDays: this.course.expiryDays > 0 ? this.course.expiryDays : null,
                expiryStartDate: this.course.expiryStartDate == 0 ? '' : this.course.expiryStartDate,
                expiryEndDate: this.course.expiryEndDate == 0 ? '' : this.course.expiryEndDate,
                services: this.course.services,
                drainageEnabled: this.course.drainageEnabled,
                drainageText: this.course.drainageText,
                drainageImage: this.course.drainageImage,
                taskDisplay: this.course.taskDisplay
            };

            if (this.vipInstalled && this.vipEnabled) {
                Object.assign(form, {vipLevelId: this.course.vipLevelId})
            }
            let liveCapacity = null;
            this.$axios.get(this.liveCapacityUrl).then((response) => {
              this.liveCapacity = response.data.capacity;
            });
            return {
                liveCapacity: liveCapacity,
                buyableRadios: [
                    {
                        value: '1',
                        label: Translator.trans('course.marketing_setup.setup.can_join'),
                    },
                    {
                        value: '0',
                        label: Translator.trans('course.marketing_setup.setup.can_not_join'),
                    }
                ],
                buyExpiryTimeEnabledRadios: [
                    {
                        value: '0',
                        label: Translator.trans('course.marketing_setup.expiry_date.anytime'),
                    },
                    {
                        value: '1',
                        label: Translator.trans('course.marketing_setup.expiry_date.custom'),
                    }
                ],
                approvalRadios: [
                    {
                        value: '1',
                        label: Translator.trans('site.datagrid.radios.yes'),
                    },
                    {
                        value: '0',
                        label: Translator.trans('site.datagrid.radios.no'),
                    }
                ],
                courseTaskDisplayRadios: [
                  {
                    value: "1",
                    label: Translator.trans('open')
                  },
                  {
                    value: "0",
                    label:  Translator.trans('close')
                  }
                ],
                drainageRadios: [
                  {
                    value: 1,
                    label: Translator.trans('open')
                  },
                  {
                    value: 0,
                    label:  Translator.trans('close')
                  }
                ],
                today: Date.now(),
                dateOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    }
                },
                startDateOptions: {
                    disabledDate: (time) => {
                        if (this.marketingForm.expiryEndDate !== '' && this.marketingForm.expiryEndDate != null) {
                            return time.getTime() > this.marketingForm.expiryEndDate || time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                        } else {
                            return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                        }
                    }
                },
                endDateOptions: {
                    disabledDate: (time) => {
                        if (this.marketingForm.expiryStartDate !== '') {
                            return time.getTime() < this.marketingForm.expiryStartDate;
                        }else{
                            return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                        }
                    }
                },
                marketingForm: form,
                formRule: {
                    maxStudentNum: [
                      {
                        required: true,
                        message: Translator.trans('course.manage.max_student_num_error_hint'),
                        trigger: 'blur'
                      },
                      {
                        validator: validation.digits_0,
                        message: Translator.trans('validate.unsigned_integer.student_message'),
                        trigger: 'blur'
                      },
                    ],
                    deadline: [
                        {
                            required: true,
                            message: Translator.trans('course.manage.deadline_end_date_error_hint'),
                            trigger: 'blur'
                        }
                    ],
                    expiryStartDate: [
                        {
                            required: true,
                            message: Translator.trans('course.manage.expiry_start_date_error_hint'),
                            trigger: 'blur'
                        },
                        {
                            validator(rule, value, callback) {
                                if (!form.expiryEndDate) return;
                                new Date(value) <= new Date(form.expiryEndDate) ? callback() : callback(new Error(Translator.trans('validate.before_date.message')));
                            },
                            trigger: 'blur',
                        }
                    ],
                    expiryEndDate: [
                        {
                            required: true,
                            message: Translator.trans('course.manage.expiry_end_date_error_hint'),
                            trigger: 'blur'
                        },
                        {
                            validator(rule, value, callback) {
                                if (!form.expiryStartDate) return;
                                new Date(value) >= new Date(form.expiryStartDate) ? callback() : callback(new Error(Translator.trans('validate.after_date.message')));
                            },
                            trigger: 'blur',
                        }
                    ],
                    expiryDays: [
                        {
                            required: true,
                            message: Translator.trans('course.manage.expiry_days_error_hint'),
                            trigger: 'blur'
                        },
                        {
                            validator: validation.digits,
                            message: Translator.trans('validate.positive_integer.message'),
                            trigger: 'blur'
                        },
                        {
                            validator: max_year,
                            message: Translator.trans('course.manage.max_year_error_hint'),
                            trigger: 'blur'
                        }
                    ],
                    originPrice: [
                        {
                            required: true,
                            message: Translator.trans('validate.required.message', {'display': Translator.trans('site.price')}),
                            trigger: 'blur'
                        },
                        {validator: validation.positive_price, trigger: 'blur'},
                    ],
                    enableBuyExpiryTime: [
                        {required: true, trigger: 'blur'}
                    ],
                    buyExpiryTime: [
                        {
                            required: true,
                            message: Translator.trans('course.manage.deadline_end_date_error_hint'),
                            trigger: 'blur'
                        }
                    ],
                    drainageImage: [
                      { required: true,  message: Translator.trans('drainage.qr_no_empty') }
                    ]
                },
                deadlineTypeRadio: {
                    'end_date': Translator.trans('course.teaching_plan.expiry_date.end_date_mode'),
                    'days': Translator.trans('course.teaching_plan.expiry_date.days_mode')
                },
                expiryMode: {
                    'days': Translator.trans('course.teaching_plan.expiry_date.anywhere_mode'),
                    'date': Translator.trans('course.teaching_plan.expiry_date.date_mode'),
                    'forever': Translator.trans('course.teaching_plan.expiry_date.forever_mode')
                },
                expiryModeTips: {
                    'anytime': Translator.trans('course.teaching_plan.expiry_date.anytime'),
                    'realtime': Translator.trans('course.teaching_plan.expiry_date.real_time'),
                    'overdue': Translator.trans('course.teaching_plan.expiry_date.overdue_tips'),
                },
                courseClosed: courseClosed,
                courseSetClosed: courseSetClosed,
                courseSetPublished: courseSetPublished,
                coursePublished: coursePublished,
            }
    }
}
</script>

<style scoped>

</style>
