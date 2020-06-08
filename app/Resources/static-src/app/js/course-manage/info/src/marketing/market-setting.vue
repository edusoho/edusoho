<template>
    <div>
        <div class="course-manage-subltitle cd-mb40">{{ 'course.marketing_setup'|trans }}</div>
        <div role="course-marketing-info">
            <div v-if="course.platform == 'supplier'">
                <div class="form-group mb0">
                    <div class="col-sm-2 control-label">
                        <label for="cooperationPrice">{{ 's2b2c.product.cooperation_price'|trans }}</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-control course-mangae-info__input price-from-control-unset-bg"
                             id="cooperationPrice">
                            {{ courseProduct.cooperationPrice }}
                        </div>
                        <span class="ml5">{{ 'site.currency.CNY'|trans }}</span>
                        <i v-if="notifies.modifyPrice"
                           class="es-icon es-icon-tip admin-update__icon v2-color-warning color-danger"
                           data-container="body" data-toggle="popover" data-trigger="hover"
                           :data-content="('s2b2c.resource.sync.prev_price.notify'|trans) + (notifies.modifyPrice.data.old.cooperationPrice)"></i>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2 control-label">
                        <label for="course_price">{{ 's2b2c.product.suggestion_price'|trans }}</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-control course-mangae-info__input price-from-control-unset-bg"
                             id="suggestionPrice">
                            {{ courseProduct.suggestionPrice }}
                        </div>
                        <span class="ml5">{{ 'site.currency.CNY'|trans }}</span>
                        <i v-if="notifies.modifyPrice"
                           class="es-icon es-icon-tip admin-update__icon v2-color-warning color-danger"
                           data-container="body" data-toggle="popover" data-trigger="hover"
                           :data-content="('s2b2c.resource.sync.prev_price.notify'|trans) + (notifies.modifyPrice.data.old.suggestionPrice)"></i>
                    </div>
                </div>
            </div>

            <div class="hidden" id="js-course-info"
                 :data-hint-message="course.platform == 'self' ? 'validate_old.positive_currency.message' : 'course_manage.positive_currency.message'"
                 :data-min-price="course.platform == 'self' ? 0 : 0.01">
            </div>

            <div class="form-group">
                <div class="col-sm-2 control-label">
                    <label class="control-label-required" for="course_price">{{ 'site.price'|trans }}</label>
                </div>
                <div class="col-sm-8">
                    <input class="form-control course-mangae-info__input mrs" id="course_price" type="text"
                           name="originPrice"
                           :disabled="course.platform == 'supplier' && !canModifyCoursePrice ? true : false"
                           v-model="course.originPrice" aria-required="true" aria-describedby="course_price-error"
                           aria-invalid="true">
                    <span class="ml5">{{ 'site.currency.CNY'|trans }}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label mb5">
                    {{ 'course.marketing_setup.setup.can_join'|trans }}
                    <a class="es-icon es-icon-help text-normal course-mangae-info__help" data-container="body"
                       data-toggle="popover"
                       data-trigger="hover" data-placement="top"
                       :data-content="'course.marketing_setup.setup.can_join.tips'|trans"></a>
                </label>

                <div class="col-sm-8 cd-radio-group mb0">
                    <label class="cd-radio" v-for="(key, value) in buyableRadio"
                           :class="course.buyable == value  ? 'checked' : ''">
                        v: {{value}}
                        c: {{course.buyable}}
                        if:{{course.buyable == value}}
                        <input type="radio"
                               data-toggle="cd-radio"
                               name="buyable"
                               :value="value"
                               v-model="course.buyable"/>
                        {{ key }}
                    </label>
                </div>
            </div>

            <div class="js-course-add-open-show" :class="course.buyable = 0 ? 'hidden' : ''">
                <div class="form-group">
                    <div class="col-sm-2 control-label">
                        <label class="control-label-required">{{ 'course.marketing_setup.expiry_date'|trans
                            }}</label>
                    </div>
                    <div class="col-sm-8 cd-radio-group course-mangae-info__group mb0">
                        <div class="col-sm-8 cd-radio-group mb0">
                            <label class="cd-radio" :class="course.buyExpiryTime == value ? 'checked' : ''"
                                   v-for="(key, value) in buyExpiryTimeRadio">
                                <input type="radio"
                                       name="enableBuyExpiryTime"
                                       :value="value"
                                       :disabled="course.buyExpiryTime > 0 ? true : false"
                                       v-model="course.buyExpiryTime"
                                       data-toggle="cd-radio"/>
                                {{ key }}
                            </label>
                        </div>

                        <input type="text"
                               class="form-control course-mangae-info__input mlm"
                               :class="course.buyExpiryTime == 0 ? 'hidden' : ''"
                               id="buyExpiryTime" name="buyExpiryTime"
                               v-model="course.buyExpiryTime">
                    </div>
                </div>
                <!--                {% if setting('magic.buy_before_approval') %}-->
                <!--                <div class="form-group">-->
                <!--                    <label class="col-sm-2 control-label">-->
                <!--                        {{ 'course.marketing_setup.approval'|trans }}-->
                <!--                        <a class="es-icon es-icon-help text-normal course-mangae-info__help"-->
                <!--                           data-container="body" data-toggle="popover" data-trigger="hover"-->
                <!--                           data-placement="top" data-content="{{ 'course.marketing_setup.approval_tips'|trans }}"></a></label>-->
                <!--                    <div class="col-sm-8 cd-radio-group">-->
                <!--                        {{ cd_radios('approval', { '1': 'site.datagrid.radios.yes'|trans, '0': 'site.datagrid.radios.no'|trans }, course.approval|default(0)) }}-->
                <!--                    </div>-->
                <!--                </div>-->
                <!--                {% endif %}-->
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        name: "market-setting",
        props: {
            course: {},
            courseProduct: {},
            notifies: {},
            canModifyCoursePrice: true,
        },
        data() {
            return {
                course: {},
                courseProduct: {},
                notifies: {},
                canModifyCoursePrice: true,
                buyableRadio: {
                    0: Translator.trans('course.marketing_setup.setup.can_not_join'),
                    1: Translator.trans('course.marketing_setup.setup.can_join'),
                },
                buyExpiryTimeRadio: {
                    0: Translator.trans('course.marketing_setup.expiry_date.anytime'),
                    1: Translator.trans('course.marketing_setup.expiry_date.custom')
                }
            }
        }
    }
</script>

<style scoped>

</style>