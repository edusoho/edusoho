<template>
    <div>
        <el-form :model="form" :rules="formRule"
                 ref="marketingForm"
                 label-position="right"
                 label-width="150px">
            <div class="course-manage-subltitle cd-mb40 ml0">{{ 'classroom.marketing_setting'|trans }}</div>
            <el-form-item :label="'classroom.price_label'|trans" prop="price">
                <el-col span="8">
                    <el-input ref="price" v-model="form.price" auto-complete="off"></el-input>
                    <div class="help-block">
                        {{'classroom.price_tips'|trans({'courseNum':courseNum,'price':coursePrice})}}
                    </div>
                    <div class="help-block" id="coinPrice"
                         v-if="coinSetting.coin_enabled && coinSetting.price_type == 'Coin'"
                         :data-rate="coinSetting.cash_rate"
                         :data-name="coinSetting.coin_name">
                        {{ 'classroom.amount'|trans }}
                        {{ form.price * coinSetting.cash_rate }}
                        {{coinSetting.coin_name}}
                    </div>

                </el-col>
            </el-form-item>

            <el-form-item :label="'classroom.show_label'|trans({'name':classroomLabel})">
                <el-radio v-model="form.showable"
                          v-for="(label, value) in statusRadios"
                          :key="value"
                          :label="value"
                          class="cd-radio">{{ label }}
                </el-radio>
                <div class="help-block">
                    关闭后，班级将彻底隐藏，无法在前台查看到。
                </div>
            </el-form-item>

            <el-form-item :label="'classroom.buy_label'|trans({'name':classroomLabel})">
                <el-radio v-model="form.buyable"
                          v-for="(label, value) in statusRadios"
                          :key="value"
                          :label="value"
                          class="cd-radio">{{ label }}
                </el-radio>
                <div class="help-block">
                    关闭后班级将无法在线购买加入。
                </div>
            </el-form-item>
            <el-form-item :label="'classroom.expiry_mode_label'|trans">
                <el-radio v-model="form.expiryMode"
                          v-for="(label, value) in expiryModeRadios"
                          :disabled="classroom.status != 'draft'"
                          class="cd-radio"
                          :label="value"
                          :key="value">
                    {{label}}
                </el-radio>
                <a :href="classroomExpiryRuleUrl" target="_blank">{{'classroom.watch_expiry_mode_rule'|trans}}</a>
                <div class="color-warning">{{ 'classroom.expiry_mode.first_publish_tips'|trans }}</div>
            </el-form-item>
            <el-form-item prop="expiryValue" :inline-message="true">
                <el-date-picker
                    v-if="form.expiryMode == 'date'"
                    v-model="form.expiryValue"
                    type="date"
                    value-format="timestamp"
                    :default-value="today"
                    :picker-options="dateOptions">
                </el-date-picker>
                <el-col class="help-block" v-if="form.expiryMode =='date'">
                    {{ 'classroom.expiry_mode_end_date_tips'|trans }}
                </el-col>
                <div v-if="form.expiryMode == 'days'">
                    <el-col span="8" class="inline-block">
                        <el-input v-model="form.expiryValue"></el-input>
                    </el-col>
                    <el-col span="1" class="plm">{{ 'site.date.day'|trans }}</el-col>
                    <el-col class="help-block" v-if="form.expiryMode =='days'">
                        {{ 'classroom.expiry_mode_days_tips'|trans }}
                    </el-col>
                </div>
            </el-form-item>

            <el-form-item v-if="vipInstalled && vipEnabled" :label="'vip.level.free_learning'|trans">
                <el-select v-model="form.vipLevelId">
                    <el-option value="0" :label="'site.default.none'|trans"></el-option>
                    <el-option
                        v-if="vipLevels"
                        v-for="(label, level) in vipLevels"
                        :key="level"
                        :label="label"
                        :value="level">
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
                              :class="tag.active || form.service.indexOf(tag.code) >= 0 ? 'service-primary-item' : ''"
                              :data-code="tag.code"
                              @click="serviceItemClick"
                        >{{ tag.fullName }}</span>
                    </el-popover>
                </el-col>
                <el-input class="hidden" type="hidden" v-model="form.service"></el-input>
            </el-form-item>


        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

    export default {
        name: "marketing-info",
        props: {
            classroom: {},
            classroomLabel: '',
            classroomExpiryRuleUrl: '',
            serviceTags: [],
            vipInstalled: false,
            vipEnabled: false,
            vipLevels: [],
            courseNum: 0,
            coursePrice: 0,
            coinSetting: {}
        },
        methods: {
            serviceItemClick(event) {
                let $item = $(event.currentTarget);
                if (!this.form.service) {
                    this.form.service = [];
                }

                let code = $item.data('code')
                if ($item.hasClass('service-primary-item')) {
                    $item.removeClass('service-primary-item');
                    this.form.service.splice(this.form.service.indexOf(code), 1);
                } else {
                    $item.addClass('service-primary-item');

                    if (this.form.service.indexOf(code) < 0) {
                        this.form.service.push(code);
                    }
                }
            },
            validateForm() {
                let result = false;
                let invalids = {};
                this.$refs.marketingForm.clearValidate();

                this.$refs.marketingForm.validate((valid, invalidFields) => {
                    if (valid) {
                        result = true;
                    } else {
                        invalids = invalidFields;
                    }
                });

                return {result: result, invalidFields: invalids};
            },
            getFormData() {
                return this.form;
            },
        },
        computed: {
            getExpiryMode: function () {
                return this.form.expiryMode
            }
        },
        watch: {
            getExpiryMode(newVal, oldVal) {
                this.$refs.marketingForm.clearValidate();
                this.form.expiryValue = null;
                if (newVal == 'date') {
                    this.formRule.expiryValue = [
                        {
                            required: true,
                            message: Translator.trans('classroom.manage.expiry_mode_date_error_hint'),
                            trigger: 'blur',
                        }
                    ];
                } else if (newVal == 'days') {
                    this.formRule.expiryValue = [
                        {
                            required: true,
                            message: Translator.trans('classroom.manage.expiry_mode_days_error_hint'),
                            trigger: 'blur',
                        },
                        {
                            validator: validation.currency,
                            trigger: 'blur'
                        }
                    ];
                } else {
                    this.formRule.expiryValue = [];
                }
            }
        },
        data() {

            let form = {
                price: this.classroom.price,
                showable: this.classroom.showable,
                buyable: this.classroom.buyable,
                expiryMode: this.classroom.expiryMode,
                expiryValue: this.classroom.expiryMode == 'date' ? this.classroom.expiryValue * 1000 : this.classroom.expiryValue,
                service: this.classroom.service,
            };

            if (this.vipInstalled && this.vipEnabled) {
                form.vipLevelId = this.classroom.vipLevelId;
            }
            return {
                statusRadios: {
                    '1': Translator.trans('site.open'),
                    '0': Translator.trans('site.close')

                },
                expiryModeRadios: {
                    'date': Translator.trans('classroom.expiry_mode_end_date'),
                    'days': Translator.trans('classroom.expiry_mode_days'),
                    'forever': Translator.trans('classroom.expiry_mode_forever'),
                },
                form: form,
                formRule: {
                    price: [
                        {
                            required: true,
                            message: Translator.trans('validate.required.message', {'display': Translator.trans('classroom.price_label')}),
                            trigger: 'blur'
                        },
                        {
                            validator: validation.currency,
                            trigger: 'blur'
                        },
                    ],
                    expiryValue: []
                },
                today: Date.now(),
                dateOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    }
                }
            };
        }
    }
</script>

<style scoped>

</style>