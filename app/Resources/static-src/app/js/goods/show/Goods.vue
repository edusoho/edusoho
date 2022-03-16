<template>
    <div class="cd-container">
        <div class="product-breadcrumb"><a href="/">{{ 'homepage'|trans }}</a> / {{goods.title|removeHtml}}</div>
        <detail :vip-enabled="vipEnabled" :drp-recruit-switch="drpRecruitSwitch" :goodsSetting="goodsSetting" :timestamp="timestamp" :goods="goods" :currentSku="currentSku" @changeSku="changeSku" :current-url="currentUrl" :is-user-login="isUserLogin">
        </detail>

        <div class="product-info clearfix" v-if="goods.id">
            <div class="product-info__left info-left pull-left">
                <div v-if="isFixed" class="fixed">
                    <div class="cd-container clearfix">
                        <ul class="info-left__nav pull-left">
                            <li :class="howActive == 1 ? 'active' : ''"><a href="javascript:;" @click="clickType(1)">{{ 'goods.show_page.tab.intro'|trans }}</a>
                            </li>
                            <li :class="howActive == 2 ? 'active' : ''"><a href="javascript:;" @click="clickType(2)">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
                            </li>
                            <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))" :class="howActive == 3 ? 'active' : ''"><a href="javascript:;" @click="clickType(3)">{{ 'goods.show_page.tab.reviews'|trans }}</a>
                            </li>
                        </ul>
                        <div class="buy__btn pull-right">
                            <buy-sku :sku="currentSku" :isShow="false" :is-user-login="isUserLogin" :goods="goods"></buy-sku>
                        </div>
                    </div>
                </div>

                <ul class="info-left__nav" ref="infoLeftNav">
                    <li :class="howActive == 1 ? 'active' : ''">
                        <a href="javascript:;" @click="clickType(1)">{{ 'goods.show_page.tab.intro'|trans }}</a>
                    </li>
                    <li :class="howActive == 2 ? 'active' : ''">
                        <a href="javascript:;" @click="clickType(2)">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
                    </li>
                    <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))" :class="howActive == 3 ? 'active' : ''">
                        <a href="javascript:;" @click="clickType(3)">{{ 'goods.show_page.tab.reviews'|trans }}</a>
                    </li>
                </ul>

                <div class="info-left__content">
                    <div id="info-left-1" class="content-item js-content-item">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.intro'|trans }}</h3>
                        <div v-html="summaryHtml" class="description-content"
                             style="padding-left: 14px; padding-top: 10px;"></div>
                    </div>

                    <div v-if="goods.product.targetType === 'course'" id="info-left-2"
                         class="content-item js-content-item">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
                        <course-tasks v-show="currentSku.taskDisplay == 1" :sku="currentSku" :i18n="i18n" :activity-metas="activityMetas"></course-tasks>
                        <div v-show="currentSku.taskDisplay != 1" class="goods-empty-content">
                          <img src="/static-dist/app/img/vue/goods/empty-content.png" alt="">
                          <p>{{ 'goods.show_page.tab.catalogue.empty' | trans }}</p>
                        </div>
                    </div>
                    <div v-if="goods.product.targetType === 'classroom'" id="info-left-2"
                         class="content-item js-content-item">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
                        <classroom-courses :classroomCourses="componentsData.classroomCourses"></classroom-courses>
                    </div>

                    <div v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))" id="info-left-3" class="info-left-reviews content-item js-content-item reviews">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.reviews'|trans }}</h3>
                        <reviews :can-create="isUserLogin && goods.isMember" :can-operate="goods.canManage"
                                 :report-type="getReportType"
                                 :reply-report-type="getReplyReportType"
                                 :target-type="'goods'"
                                 :current-user-id="currentUserId"
                                 :target-id="goods.id"
                                 v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))"
                        >
                        </reviews>
                        <div v-else class="description-content"
                             style="padding-left: 14px; padding-top: 10px;">{{ 'goods.show_page.tab.reviews_empty_tips'|trans }}</div>
                    </div>
                </div>
            </div>

            <div class="product-info__right pull-right">
                <teacher :teachers="currentSku.teachers"/>
                <qr :mpQrcode="componentsData.mpQrCode"/>
                <recommend :goods="goods" :recommendGoods="componentsData.recommendGoods"/>
                <certificate :goodsId="goodsId" :sku="currentSku" />
            </div>
        </div>
        <back-to-top v-show="isFixed"/>
    </div>
</template>

<script>
    import axios from 'axios';
    import Detail from './components/detail';
    import Teacher from './components/teacher';
    import Qr from './components/qr';
    import Recommend from './components/recommend';
    import BackToTop from './components/back-to-top';
    import Reviews from 'app/common/component/review/index';
    import ClassroomCourses from './components/classroom-courses';
    import CourseTasks from './components/course-tasks';
    import BuySku from './components/buy-sku';
    import Certificate from './components/certificate';

    export default {
        data() {
            return {
                howActive: 1,
                flag: true,
                isFixed: false,
                timerClick: null,
                timerScroll: null,
                goodsId: window.location.pathname.replace(/[^0-9]/ig, ""),
                goods: {},
                currentSku: {},
                componentsData: {},
            }
        },
        props: {
            goods: {
                type: Object,
                default: null,
            },
            componentsData: {
                type: Array,
                default: null,
            },
            currentUserId: {
                type: Number,
                default: null
            },
            targetId: {
                type: Number,
                default: null
            },
            isUserLogin: {
                type: Number,
                default: 0,
            },
            i18n: {
                type: Object,
                default: null,
            },
            goodsSetting: {
                type: Object,
                default: null,
            },
            ugcReviewSetting: {
                type: Object,
                default: null,
            },
            activityMetas: {
                type: Object,
                default: null,
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
        components: {
            Detail,
            Teacher,
            Qr,
            Recommend,
            BackToTop,
            Reviews,
            ClassroomCourses,
            CourseTasks,
            BuySku,
            Certificate,
        },
        computed: {
            summaryHtml() {
                if (!this.goods.summary) return Translator.trans('goods.show_page.tab.summary_empty_tips');
                return this.goods.summary;
            },
            getReportType() {
                if (this.goods.type === 'classroom') {
                    return 'classroom_review';
                }

                if (this.goods.type === 'course' && this.targetId) {
                    return 'course_review';
                }
            },
            getReplyReportType() {
                if (this.goods.type === 'classroom') {
                    return 'classroom_review_reply';
                }

                if (this.goods.type === 'course' && this.targetId) {
                    return 'course_review_reply';
                }
            },
        },
        methods: {
            getGoodsInfo() {
                axios.get(`/api/good/${this.goodsId}`, {
                    headers: {'Accept': 'application/vnd.edusoho.v2+json'}
                }).then((res) => {
                    this.goods = res.data;

                    if (this.goods.type == 'classroom') {
                        return this.changeSku(this.goods.product.target.id);
                    }

                    if (this.goods.type == 'course' && this.targetId) {
                        return this.changeSku(this.targetId);
                    }

                    if (this.goods.product.target.defaultCourseId) {
                        return this.changeSku(this.goods.product.target.defaultCourseId);
                    }

                });
            },
            initGoodsComponents() {
                if (!this.goods.hasExtension) {
                    return;
                }

                axios.get(`/api/goods/${this.goodsId}/components`, {
                    params: {
                        componentTypes: this.goods.extensions
                    },
                    headers: {
                        'Accept': 'application/vnd.edusoho.v2+json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
                    }
                }).then(res => {
                    this.componentsData = res.data;
                });
            },
            changeSku(targetId) {
                for (const key in this.goods.specs) {
                    this.$set(this.goods.specs[key], 'active', false);
                    if (targetId == this.goods.specs[key]['targetId']) {
                        this.$set(this.goods.specs[key], 'active', true);
                        this.currentSku = this.goods.specs[key];
                    }
                }

                // this.goods.hasExtension = true;
                // this.initGoodsComponents();
            },
            handleScroll() {
                let eleTop = this.$refs.infoLeftNav.offsetTop + this.$refs.infoLeftNav.offsetHeight;
                if (!eleTop) return;
                let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                if (eleTop <= scrollTop && !this.isFixed) this.isFixed = true;
                if (eleTop > scrollTop && this.isFixed) this.isFixed = false;
                clearTimeout(this.timerScroll);
                this.timerScroll = null;
                this.timerScroll = setTimeout(() => {
                    if (this.flag) this.calcScrollTop(scrollTop);
                }, 200);
            },
            calcScrollTop(value) {
                let eleArr = $('.js-content-item');
                for (let i = eleArr.length - 1; i >= 0; i--) {
                    const elementTop = eleArr[i].offsetTop - 80;
                    if (value >= elementTop) {
                        if (this.howActive != i + 1) this.howActive = i + 1;
                        return;
                    } else {
                        this.howActive = 1;
                    }
                }
            },
            clickType(value) {
                clearTimeout(this.timer);
                this.timerClick = null
                this.flag = false;
                this.howActive = value;
                let ele = '#info-left-' + value;
                document.documentElement.scrollTop = document.body.scrollTop = $(ele).offset().top - 80;
                this.timerClick = setTimeout(() => {
                    this.flag = true;
                }, 300);
            },
        },
        filters: {
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
            }
        },
        created() {
            window.addEventListener("scroll", this.handleScroll);
            console.log(this.goods);
            if (this.goods.type == 'classroom') {
                return this.changeSku(this.goods.product.target.id);
            }

            if (this.goods.type == 'course' && this.targetId) {
                return this.changeSku(this.targetId);
            }

            if (this.goods.product.target.defaultCourseId) {
                return this.changeSku(this.goods.product.target.defaultCourseId);
            }
        },
        watch: {
            goods(newVal, oldVal) {
                this.goods = newVal;
                if (!oldVal.id && newVal.id) {
                    window.addEventListener("scroll", this.handleScroll);
                }
            },
            componentsData: {
                immediate: true,
                handler(val) {
                    this.componentsData = val;
                },
            },
        },
        destroyed() {
            window.removeEventListener('scroll', this.handleScroll);
        }
    }
</script>
