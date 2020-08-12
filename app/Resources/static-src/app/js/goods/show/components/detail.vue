<template>
    <div class="product-detail clearfix" v-if="goods.id">
        <div class="product-detail__left detail-left pull-left">
            <div class="detail-left__img">
                <img :src="goods.images ? goods.images.large : null" alt="">
            </div>
            <ul class="detail-left__text clearfix">
                <li class="pull-left"><i class="es-icon es-icon-friends mrs"></i>{{ goods.product.target.studentNum }}人加入学习
                </li>
                <li class="pull-right">
                    <share :customized-class="'detail-left__text-share'"
                           :title="goods.title"
                           :summary="goods.summary|removeHtml"
                           :message="`我正在学习《${goods.title}》，收获巨大哦，一起来学习吧！`"
                           :picture="goods.images.large"
                           :url="currentUrl"
                           :type="'courseSet'">分享
                    </share>
                    <favorite :is-favorite="goods.isFavorite" :target-type="'goods'"
                              :target-id="goods.id"></favorite>
                </li>
            </ul>
        </div>

        <div class="product-detail__right detail-right pull-right">
            <p class="detail-right__title">{{ goods.title }}</p>
            <p class="detail-right__subtitle">{{ goods.subtitle }}</p>
            <a v-if="currentSku.isTeacher" class="btn btn-default btn-xs detail-right__manage_btn" @click="manageUrl(goods)">
                <i class="es-icon es-icon-setting"></i>&nbsp;{{ '管理'|trans }}
            </a>

            <!-- 价格 -->
            <div v-if="goods.discount" class="detail-right__price">
                <!-- 优惠活动 -->
                <div class="detail-right__price__activities">该商品享受“{{ goods.discount.name }}”打折促销活动，24：00：00后结束，请尽快购买！</div>
                <div class="detail-right__price__num">
                    优惠价
                    <span v-if="currentSku.displayPriceObj.currency === 'RMB'" class="detail-right__price__price">{{ currentSku.displayPriceObj.amount }}<span class="detail-right__price__unit">元</span></span>
                    <span v-if="currentSku.displayPriceObj.currency === 'coin'" class="detail-right__price__price">{{ currentSku.displayPriceObj.coinAmount }}<span class="detail-right__price__unit">{{ currentSku.displayPriceObj.coinName }}</span></span>
                    <s v-if="currentSku.priceObj.currency === 'RMB'">价格: {{ currentSku.priceObj.amount }}元</s>
                    <s v-if="currentSku.priceObj.currency === 'coin'">价格: {{ currentSku.priceObj.coinAmount }}<span class="detail-right__price__unit">{{ currentSku.priceObj.coinName }}</span></s>
                </div>
            </div>
            <div v-if="!goods.discount" class="detail-right__price">
                <!-- 优惠活动 -->
                <!--                <div class="detail-right__price__activities">该商品享受“某某某某某某某某某某某某”打折促销活动，24：00：00后结束，请尽快购买！</div>-->
                <div class="detail-right__price__free" v-if="currentSku.displayPrice == 0">
                    价格
                    <span class="free">免费</span>
                </div>
                <div class="detail-right__price__num" v-if="currentSku.displayPrice != 0">
                    价格
                    <span v-if="currentSku.displayPriceObj.currency === 'RMB'" class="detail-right__price__price">{{ currentSku.displayPriceObj.amount }}<span class="detail-right__price__unit">元</span></span>
                    <span v-if="currentSku.displayPriceObj.currency === 'coin'" class="detail-right__price__price">{{ currentSku.displayPriceObj.coinAmount }}<span class="detail-right__price__unit">{{ currentSku.displayPriceObj.coinName }}</span></span>
                </div>
            </div>

            <!-- 教学计划 -->
            <div class="detail-right__plan plan clearfix" v-if="goods.specs.length > 1">
                <div class="plan-title pull-left">教学计划</div>
                <div class="plan-btns pull-right">
                    <span class="plan-btns__item" v-for="plan in goods.specs" :key="plan.id"
                          :class="{ active: plan.active }" @click="handleClick(plan)">{{ plan.title }}</span>
                </div>
            </div>

            <!-- 学习有效期 -->
            <div class="detail-right__validity validity clearfix">
                <span class="validity-title pull-left">学习有效期</span>
                <span class="validity-content pull-left">
<!--                    {{ buyableModes[currentSku.usageMode] }}-->
                    <span v-if="currentSku.usageMode === 'forever'">长期有效</span>
                    <span v-if="currentSku.usageMode === 'date'">开始：{{ currentSku.usageStartTime|formatDate }} 截止：{{ currentSku.usageEndTime|formatDate }}</span>
                    <span v-if="currentSku.usageMode === 'days'">{{ currentSku.usageDays }}天 （随到随学）</span>
                    <span v-if="currentSku.usageMode === 'end_date'">截止日期： {{ currentSku.usageEndTime|formatDate }}</span>
                </span>
            </div>

            <!-- 承诺服务 -->
            <div class="detail-right__promise promise clearfix" v-if="currentSku.services && currentSku.services.length > 0">
                <div class="promise-title pull-left">承诺服务</div>
                <div class="promise-content pull-left">
              <span class="promise-content__item" v-for="(item, index) in currentSku.services" :key="index">
                {{ item.shortName }}
                <span class="promise-content__item-hover">{{ item.fullName }}</span>
              </span>
                </div>
            </div>
        </div>

        <!-- 立即购买 -->
        <buy-sku :sku="currentSku" :btn-class="'product-detail__btn'" :is-user-login="isUserLogin" :goods="goods"></buy-sku>
    </div>
</template>

<script>
    import Favorite from "./favorite";
    import Share from 'app/js/share/src/share.vue';
    import BuySku from './buy-sku';

    export default {
        components: {
            Favorite,
            Share,
            BuySku,
        },
        props: {
            goods: {
                type: Object,
                default: null
            },
            currentSku: {
                type: Object,
                default: () => {
                }
            },
            isUserLogin: {
                type: Number,
                default: 0,
            },
            currentUrl: {
                type: String,
                default: '',
            }
        },
        methods: {
            handleClick(sku) {
                this.$emit('changeSku', sku.targetId);
            },
            manageUrl(goods) {
                if (goods.type === 'course') {
                    window.open(`/course_set/${goods.product.target.id}/manage/base`, '_blank');
                } else if (goods.type === 'classroom') {
                    window.open(`/classroom/${goods.product.target.id}/manage`, '_blank')
                }
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
            removeHtml(input) {
                return input && input.replace(/<(?:.|\n)*?>/gm, '')
                    .replace(/(&rdquo;)/g, '\"')
                    .replace(/&ldquo;/g, '\"')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;/g, '')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/<[\w\s"':=\/]*/, '');
            },
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
            }
        },
    }
</script>
