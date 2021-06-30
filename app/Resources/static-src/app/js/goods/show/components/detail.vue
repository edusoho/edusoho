<template>
    <div class="product-detail clearfix" v-if="goods.id">
        <div class="product-detail__left detail-left pull-left">
            <div class="detail-left__img">
                <drp-info v-if="isUserLogin && drpInfo && drpInfo.tagVisible" :drp-info="drpInfo" :drp-recruit-switch="drpRecruitSwitch"></drp-info>
                <img :src="goods.images ? goods.images.large : null" alt="">
            </div>
            <ul class="detail-left__text clearfix">
                <li v-if="goodsSetting.show_number_data !== 'none'" class="pull-left"><i class="es-icon es-icon-friends mrs"></i>
                    <span v-if="goodsSetting.show_number_data === 'join'">
                    {{ goods.product.target.studentNum }}人
                    </span>
                    <span v-else-if="goodsSetting.show_number_data === 'visitor'">
                    {{ goods.hitNum }}人
                    </span>
                </li>
                <li class="pull-right">
                    <share :customized-class="'detail-left__text-share'"
                           :title="goods.title|removeHtml"
                           :summary="goods.summary|removeHtml"
                           :message="`我正在学习《${goods.title|removeHtml}》，收获巨大哦，一起来学习吧！`"
                           :picture="goods.images.large"
                           :url="currentUrl"
                           :type="'courseSet'">{{ 'site.share'|trans }}
                    </share>
                    <favorite :is-favorite="goods.isFavorite" :target-type="'goods'"
                              :target-id="goods.id"></favorite>
                </li>
            </ul>
        </div>

        <div class="product-detail__right detail-right pull-right">
            <div class="detail-right__box">
                <p class="detail-right__title">{{ goods.title|removeHtml }}</p>
                <p class="detail-right__subtitle">{{ goods.subtitle|removeHtml }}</p>
                <a v-if="goods.canManage" class="detail-right__manage_btn" @click="manageUrl(goods)">
                    <i class="es-icon es-icon-setting"></i>&nbsp;{{ 'site.manage'|trans }}
                </a>

                <!-- 价格 -->
                <div v-if="goods.discount && currentSku.price != 0" class="detail-right__price">
                    <!-- 优惠活动 -->
                    <div class="detail-right__price__activities">该商品享受“{{ goods.discount.name }}”打折促销活动，<span id="discount-endtime-countdown">{{ discountCountDown }}</span>后结束，请尽快购买！</div>
                    <div class="detail-right__price__num">
                        优惠价
                        <span v-if="currentSku.displayPriceObj.currency === 'RMB'" class="detail-right__price__price">{{ currentSku.displayPriceObj.amount|formatPrice }}<span class="detail-right__price__unit">元</span></span>
                        <span v-if="currentSku.displayPriceObj.currency === 'coin'" class="detail-right__price__price">{{ currentSku.displayPriceObj.coinAmount|formatPrice }}<span class="detail-right__price__unit">{{ currentSku.displayPriceObj.coinName }}</span></span>
                        <s v-if="currentSku.priceObj.currency === 'RMB'">价格: {{ currentSku.priceObj.amount }}元</s>
                        <s v-if="currentSku.priceObj.currency === 'coin'">价格: {{ currentSku.priceObj.coinAmount }}<span class="detail-right__price__unit">{{ currentSku.priceObj.coinName }}</span></s>
                    </div>
                </div>
                <div v-if="!goods.discount || currentSku.price == 0" class="detail-right__price">
                    <!-- 优惠活动 -->
                    <!--                <div class="detail-right__price__activities">该商品享受“某某某某某某某某某某某某”打折促销活动，24：00：00后结束，请尽快购买！</div>-->
                    <div class="detail-right__price__free" v-if="currentSku.displayPrice == 0">
                        {{ 'classroom.price_label'|trans }}
                        <span class="free">{{ 'course.marketing_setup.preview.set_task.free'|trans }}</span>
                    </div>
                    <div class="detail-right__price__num" v-if="currentSku.displayPrice != 0">
                        {{ 'classroom.price_label'|trans }}
                        <span v-if="currentSku.displayPriceObj.currency === 'RMB'" class="detail-right__price__price">{{ currentSku.displayPriceObj.amount|formatPrice }}<span class="detail-right__price__unit">元</span></span>
                        <span v-if="currentSku.displayPriceObj.currency === 'coin'" class="detail-right__price__price">{{ currentSku.displayPriceObj.coinAmount|formatPrice }}<span class="detail-right__price__unit">{{ currentSku.displayPriceObj.coinName }}</span></span>
                    </div>
                </div>

                <!-- 教学计划 -->
                <div class="detail-right__plan plan clearfix" v-if="goods.specs.length > 1">
                    <div class="plan-title pull-left">{{ 'site.course_plan'|trans }}</div>
                    <div class="plan-btns pull-right">
                        <span class="plan-btns__item" v-for="plan in goods.specs" :key="plan.id"
                            :class="{ active: plan.active }" @click="handleClick(plan)">{{ plan.title|transSpecsTitle(goods.type)|removeHtml }}</span>
                    </div>
                </div>

                <!-- 学习有效期 -->
                <div class="detail-right__validity validity clearfix">
                    <span class="validity-title pull-left">{{ 'goods.show_page.components.expiry_mode'|trans }}</span>
                    <span class="validity-content pull-left">
    <!--                    {{ buyableModes[currentSku.usageMode] }}-->
                        <span v-if="currentSku.usageMode === 'forever'">{{ 'goods.show_page.components.expiry_mode.forever'|trans }}</span>
                        <span v-if="currentSku.usageMode === 'date'">{{ 'goods.show_page.components.expiry_mode.date_start'|trans }}：{{ currentSku.usageStartTime|formatDate }} {{ 'goods.show_page.components.expiry_mode.date_end'|trans }}：{{ currentSku.usageEndTime|formatDate }}</span>
                        <span v-if="currentSku.usageMode === 'days'">{{ currentSku.usageDays }}{{ 'site.date.day'|trans }} （{{ 'goods.show_page.components.expiry_mode.forever_tips'|trans }}）</span>
                        <span v-if="currentSku.usageMode === 'end_date'">{{ 'goods.show_page.components.expiry_mode.date_end_label'|trans }}： {{ currentSku.usageEndTime|formatDate }}</span>
                    </span>
                </div>

                <!-- 承诺服务 -->
                <div class="detail-right__promise promise clearfix" v-if="currentSku.services && currentSku.services.length > 0">
                    <div class="promise-title pull-left">{{ 'goods.show_page.components.services'|trans }}</div>
                    <div class="promise-content pull-left">
                <span class="promise-content__item" v-for="(item, index) in currentSku.services" :key="index">
                    {{ item.shortName }}
                    <span class="promise-content__item-hover">{{ item.fullName }}</span>
                </span>
                    </div>
                </div>
            </div>
            <!-- 立即购买 -->
            <buy-sku :sku="currentSku" :btn-class="'product-detail__btn'" :is-user-login="isUserLogin" :goods="goods" :vip-enabled="vipEnabled"></buy-sku>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Favorite from "./favorite";
    import Share from 'app/js/share/src/share.vue';
    import BuySku from './buy-sku';
    import DrpInfo from './drp-info';

    export default {
        components: {
            Favorite,
            Share,
            BuySku,
            DrpInfo,
        },
        props: {
            goods: {
                type: Object,
                default: null
            },
            currentSku: {
                type: Object,
                default: () => {},
            },
            goodsSetting: {
                type: Object,
                default: () => {},
            },
            isUserLogin: {
                type: Number,
                default: 0,
            },
            currentUrl: {
                type: String,
                default: '',
            },
            timestamp: {
                type: String,
                default: '',
            },
            drpRecruitSwitch: {
                type: Number,
                default: 0
            },
            vipEnabled: {
                type: Number,
                default: 1
            }
        },
        methods: {
            handleClick(sku) {
                this.$emit('changeSku', sku.targetId);
            },
            manageUrl(goods) {
                window.open(goods.manageUrl, '_blank');
            },
            remainTime() {
                if (this.goods.discountId == 0) {
                    return ;
                }
                if (this.timer) {
                    clearInterval(this.timer)
                }

                let remainTime = this.goods.discount.endTime - parseInt(new Date().getTime()/1000);

                if (remainTime >= 0) {
                    this.timer = setInterval(()=>{
                        remainTime--
                        this.discountCountDown = this.secondToDate(remainTime);
                        if(this.times===0){
                            clearInterval(this.timer)
                            window.location.reload();

                        }
                    },1000)
                }
            },
            secondToDate(msd) {
                let time =msd
                if (null != time && "" != time) {
                    if (time > 60 && time < 60 * 60) {
                        time = parseInt(time / 60.0) + "分钟" + parseInt((parseFloat(time / 60.0) -
                            parseInt(time / 60.0)) * 60) + "秒";
                    }
                    else if (time >= 60 * 60 && time < 60 * 60 * 24) {
                        time = parseInt(time / 3600.0) + "小时" + parseInt((parseFloat(time / 3600.0) -
                            parseInt(time / 3600.0)) * 60) + "分钟" +
                            parseInt((parseFloat((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60) -
                                parseInt((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60)) * 60) + "秒";
                    } else if (time >= 60 * 60 * 24) {
                        time = parseInt(time / 3600.0/24) + "天" +parseInt((parseFloat(time / 3600.0/24)-
                            parseInt(time / 3600.0/24))*24) + "小时" + parseInt((parseFloat(time / 3600.0) -
                            parseInt(time / 3600.0)) * 60) + "分钟" +
                            parseInt((parseFloat((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60) -
                                parseInt((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60)) * 60) + "秒";
                    }
                    else {
                        time = parseInt(time) + "秒";
                    }
                }
                return time;
            },
            getDrpInfo() {
                axios.get(`/drp_info/${this.currentSku.targetId}/${this.goods.type}`).then(res => {
                        this.drpInfo = res.data;
                    });
            }
        },
        filters: {
            formatDate(time, fmt = 'yyyy-MM-dd') {
                time = time * 1000
                let date = new Date(time)
                if (/(y+)/.test(fmt)) {
                    fmt = fmt.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length))
                }
                let o = {
                    'M+': date.getMonth() + 1,
                    'd+': date.getDate(),
                    'h+': date.getHours(),
                    'm+': date.getMinutes(),
                    's+': date.getSeconds()
                }
                for (let k in o) {
                    if (new RegExp(`(${k})`).test(fmt)) {
                        let str = o[k] + ''
                        fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? str : ('00' + str).substr(str.length))
                    }
                }
                return fmt
            },
            formatPrice(input) {
                return (Math.round(input * 100) / 100).toFixed(2);
            },
            removeHtml(input) {
                return input && input.replace(/<(?:.|\n)*?>/gm, '')
                    .replace(/(&rdquo;)/g, '\"')
                    .replace(/&ldquo;/g, '\"')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;/g, '')
                    .replace(/&amp;/g, '&')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/<[\w\s"':=\/]*/, '');
            },
            transSpecsTitle(specsTitle, goodsType) {
                if ('course' === goodsType && '' == specsTitle) {
                    return Translator.trans('course.unname_title');
                }
                return specsTitle;
            }
        },
        data() {
            return {
                goods: this.goods,
                product: this.goods ? this.goods.product : null,
                buyableModes: {
                    'date': Translator.trans('classroom.expiry_mode_end_date'),
                    'days': Translator.trans('classroom.expiry_mode_days'),
                    'forever': Translator.trans('classroom.expiry_mode_forever'),
                },
                discountCountDown: '',
                drpInfo: [],
            }
        },
        mounted() {
            this.remainTime();
            this.getDrpInfo();
        },
        watch: {
            currentSku(newVal, oldVal) {
                this.remainTime();
                this.getDrpInfo();
            }
        }
    }
</script>
