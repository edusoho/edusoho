<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.marketing_setup'|trans }}</div>
        <el-form ref="marketSettingForm" :model="marketingForm"
                 :rules="formRule" label-position="right"
                 label-width="150px">
            <div v-if="course.platform == 'supplier'">
                <el-form-item :label="'s2b2c.product.cooperation_price'|trans">
                    <el-col span="18">
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
                    <el-col span="18">
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
                 :data-hint-message="course.platform == 'self' ? 'validate_old.positive_currency.message' : 'course_manage.positive_currency.message'"
                 :data-min-price="course.platform == 'self' ? 0 : 0.01">
            </div>

            <el-form-item :label="'site.price'|trans" prop="originPrice">
                <el-col span="4">
                    <el-input v-model="marketingForm.originPrice" ref="originPrics"
                              :disabled="course.platform == 'supplier' && !canModifyCoursePrice"></el-input>
                </el-col>
                <el-col span="8" class="mlm">{{ 'site.currency.CNY'|trans }}</el-col>
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
                <el-col span="18">
                    <el-radio v-for="(label, value) in buyableRadio"
                              v-model="marketingForm.buyable"
                              :key="value"
                              :value="value"
                              :label="value"
                              class="cd-radio">
                        {{label}}
                    </el-radio>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.marketing_setup.expiry_date'|trans"
                          :prop="marketingForm.enableBuyExpiryTime == 1 ? 'buyExpiryTime': 'enableBuyExpiryTime'">
                <el-col span="8">
                    <el-radio v-for="(label, value) in buyExpiryTimeEnabledRadio"
                              v-model="marketingForm.enableBuyExpiryTime"
                              :key="value"
                              :label="value"
                              class="cd-radio">
                        {{label}}
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
            <!--                <el-col span="18">-->
            <!--                    <el-radio v-for="(label, value) in approvalRadio"-->
            <!--                              v-model="marketingForm.approval"-->
            <!--                              :key="value"-->
            <!--                              :label="value"-->
            <!--                              class="cd-radio">-->
            <!--                        {{label}}-->
            <!--                    </el-radio>-->
            <!--                </el-col>-->

            <!--            </el-form-item>-->
        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

    export default {
        name: "market-setting",
        props: {
            course: {},
            courseProduct: {},
            notifies: {},
            canModifyCoursePrice: true,
            buyBeforeApproval: false,
        },
        watch: {},
        methods: {
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
                if (this.marketingForm.buyExpiryTime.toString().length > 10) {
                    this.marketingForm.buyExpiryTime /= 1000;
                }
                return this.marketingForm;
            }
        },
        data() {
            this.course.buyExpiryTime = this.course.buyExpiryTime > 0 ? this.course.buyExpiryTime * 1000 : null;
            return {
                course: {},
                courseProduct: {},
                notifies: {},
                canModifyCoursePrice: true,
                buyBeforeApproval: false,
                buyableRadio: {
                    0: Translator.trans('course.marketing_setup.setup.can_not_join'),
                    1: Translator.trans('course.marketing_setup.setup.can_join'),
                },
                buyExpiryTimeEnabledRadio: {
                    0: Translator.trans('course.marketing_setup.expiry_date.anytime'),
                    1: Translator.trans('course.marketing_setup.expiry_date.custom')
                },
                approvalRadio: {
                    0: Translator.trans('site.datagrid.radios.no'),
                    1: Translator.trans('site.datagrid.radios.yes'),
                },
                today: Date.now(),
                dateOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now() - 24 * 60 * 60 * 1000;
                    }
                },
                marketingForm: {
                    originPrice: this.course.originPrice,
                    buyable: this.course.buyable,
                    enableBuyExpiryTime: this.course.buyExpiryTime > 0 ? '1' : '0',
                    buyExpiryTime: this.course.buyExpiryTime,
                    approval: this.course.approval
                },
                formRule: {
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
                    ]
                }
            }
        }
    }
</script>

<style scoped>

</style>