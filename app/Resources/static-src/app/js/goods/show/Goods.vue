<template>
    <div class="cd-container">
        <div class="product-breadcrumb"><a href="/">{{ 'homepage'|trans }}</a>
            <span v-for="(item, index) in goods.breadcrumbs" :key="index">/ <a :href="'/' + goods.type + '/explore/' + item.code">{{item.name}}</a></span>
             / {{goods.title|removeHtml}}
        </div>
        <a-alert
            v-if="goods.product.target.status == 'closed'"
            class="mt16"
            :message="alertMessage"
            type="warning"
            show-icon
            />
        <detail :vip-enabled="vipEnabled" :drp-recruit-switch="drpRecruitSwitch" :goodsSetting="goodsSetting" :timestamp="timestamp" :goods="goods" :currentSku="currentSku" @changeSku="changeSku" :current-url="currentUrl" :is-user-login="isUserLogin">
        </detail>

        <div class="product-info clearfix" v-if="goods.id">
            <div class="product-info__left info-left pull-left">
                <div v-if="isFixed" class="fixed">
                    <div class="cd-container clearfix">
                        <ul class="info-left__nav pull-left">
                            <li :class="howActive == 1 ? 'active' : ''"><a href="#info-left-1">{{ 'goods.show_page.tab.intro'|trans }}</a>
                            </li>
                            <li :class="howActive == 2 ? 'active' : ''"><a href="#info-left-2">题库</a>
                            </li>
                            <li :class="howActive == 3 ? 'active' : ''"><a href="#info-left-3">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
                            </li>
                            <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))"
                                 :class="howActive == 4 ? 'active' : ''">
                                <a href="#info-left-4">{{ 'goods.show_page.tab.reviews'|trans }}</a>
                            </li>
                        </ul>
                        <div class="buy__btn pull-right">
                            <buy-sku :sku="currentSku" :btn-class="goods.product.target.status == 'closed' ? 'product-detail__btn js-handleLearnOnMessage' : 'product-detail__btn'" :isShow="false" :is-user-login="isUserLogin" :goods="goods"></buy-sku>
                        </div>
                    </div>
                </div>

                <ul class="info-left__nav" ref="infoLeftNav">
                    <li :class="howActive == 1 ? 'active' : ''">
                        <a href="#info-left-1">{{ 'goods.show_page.tab.intro'|trans }}</a>
                    </li>
                    <li :class="howActive == 2 ? 'active' : ''">
                      <a href="#info-left-2">题库</a>
                    </li>
                    <li :class="howActive == 3 ? 'active' : ''">
                        <a href="#info-left-3">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
                    </li>
                    <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))" :class="howActive == 4 ? 'active' : ''">
                        <a href="#info-left-4">{{ 'goods.show_page.tab.reviews'|trans }}</a>
                    </li>
                </ul>

                <div class="info-left__content">
                    <div id="info-left-1" class="content-item js-content-item">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.intro'|trans }}</h3>
                        <div v-html="summaryHtml" class="description-content"
                             style="padding-left: 14px; padding-top: 10px;"></div>
                    </div>

                    <div id="info-left-2" style="scroll-margin-top: 80px; background-color: #FFF; margin-top: 24px; padding: 16px 24px; display: flex; flex-direction: column;">
                      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                        <div style="color: #333; font-size: 18px; font-weight: 500; line-height: 24px; padding-left: 6px">题库</div>
                        <div v-if="bindItemBankExerciseList.length > 1" style="display: flex; align-items: center; font-size: 14px; font-weight: 400; color: #919399; cursor: pointer;" @click="showItemBanKDrawer">
                          <div>{{ `查看全部（${bindItemBankExerciseList.length}）` }}</div>
                          <a-icon type="right" style="line-height: 14px"/>
                        </div>
                      </div>
                      <div v-if="bindItemBankExerciseList.length && bindItemBankExerciseList.length > 0" style="display: flex; justify-content: space-between;">
                        <div style="display: flex">
                          <img :src=bindItemBankExerciseList[0].itemBankExercise.cover.middle alt="" style="height: 96px; margin-right: 16px; border-radius: 6px">
                          <div style="display: flex; flex-direction: column;; justify-content: space-between;">
                            <div>
                              <a-tooltip placement="top">
                                <template slot="title">
                                  <div style="max-width: 216px;">{{ bindItemBankExerciseList[0].itemBankExercise.title }}</div>
                                </template>
                                <div style="font-size: 16px; color: #37393D; font-weight: 500; margin-bottom: 4px; width: fit-content; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ bindItemBankExerciseList[0].itemBankExercise.title }}</div>
                              </a-tooltip>
                              <div style="display: flex">
                                <div style="margin-right: 16px; font-size: 12px; font-weight: 400; color: #919399;">章节练习：<span style="color: #37393D;">{{ bindItemBankExerciseList[0].chapterExerciseNum }}</span></div>
                                <div style="margin-right: 16px; font-size: 12px; font-weight: 400; color: #919399;">试卷练习：<span style="color: #37393D;">{{ bindItemBankExerciseList[0].assessmentNum }}</span></div>
                              </div>
                            </div>
                            <div style="font-size: 20px; font-weight: 600; color: #FF7E56;"><span style="font-size: 12px; margin-right: 2px">¥</span>{{ `${integerPart(bindItemBankExerciseList[0].itemBankExercise.price)}.` }}<span style="font-size: 12px;">{{ decimalPart(bindItemBankExerciseList[0].itemBankExercise.price) }}</span></div>
                          </div>
                        </div>
                        <div style="display: flex; align-items: center;">
                          <a-button type="primary" ghost @click="toItemBankExercisePage(bindItemBankExerciseList[0].itemBankExercise.id)">查看</a-button>
                        </div>
                      </div>
                      <div v-else style="font-size: 14px; font-weight: 400; color: rgba(0, 0, 0, 0.56); ">暂无绑定的题库哦～</div>
                    </div>
                    <a-drawer
                      placement="right"
                      width="50vw"
                      :z-index="1020"
                      :closable="false"
                      :visible="bindItemBankDrawerVisible"
                      @close="closeItemBanKDrawer"
                      :bodyStyle="{padding: '0',}"
                    >
                      <div style="padding: 14px 20px; border-bottom: 1px solid #EFF0F5; display: flex; align-items: center; justify-content: space-between; width: 50vw; position: fixed; z-index: 10;; top: 0; right: 0; background-color: #FFF;">
                        <div style="font-size: 16px; font-weight: 500; color: #37393D;">题库</div>
                        <a-icon type="close" style="font-size: 16px" @click="closeItemBanKDrawer"/>
                      </div>
                      <div style="margin-top: 53px; margin-bottom: 69px; padding: 18px 20px 0 20px; display: flex; flex-direction: column;">
                        <div v-for="item in bindItemBankExerciseList">
                          <div style="display: flex; justify-content: space-between; padding: 16px 24px; border: 1px solid #EFF0F5; border-radius: 6px; margin-bottom: 16px;">
                            <div style="display: flex">
                              <img :src="item.itemBankExercise.cover.middle" alt="" style="height: 96px; margin-right: 16px; border-radius: 6px">
                              <div style="display: flex; flex-direction: column;; justify-content: space-between;">
                                <div>
                                  <a-tooltip placement="top">
                                    <template slot="title">
                                      <div style="max-width: 216px;">{{ item.itemBankExercise.title }}</div>
                                    </template>
                                    <div style="font-size: 16px; color: #37393D; font-weight: 500; margin-bottom: 4px; width: fit-content; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; cursor: pointer;">{{ item.itemBankExercise.title }}</div>
                                  </a-tooltip>
                                  <div style="display: flex">
                                    <div style="margin-right: 16px; font-size: 12px; font-weight: 400; color: #919399;">章节练习：<span style="color: #37393D;">{{ item.chapterExerciseNum }}</span></div>
                                    <div style="margin-right: 16px; font-size: 12px; font-weight: 400; color: #919399;">试卷练习：<span style="color: #37393D;">{{ item.assessmentNum }}</span></div>
                                  </div>
                                </div>
                                <div style="font-size: 20px; font-weight: 600; color: #FF7E56;"><span style="font-size: 12px; margin-right: 2px">¥</span>{{ `${integerPart(item.itemBankExercise.price)}.` }}<span style="font-size: 12px;">{{ decimalPart(item.itemBankExercise.price) }}</span></div>
                              </div>
                            </div>
                            <div style="display: flex; align-items: center;">
                              <a-button type="primary" ghost @click="toItemBankExercisePage(item.itemBankExercise.id)">查看</a-button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div style="padding: 16px 24px; border-top: 1px solid #EFF0F5; display: flex; flex-direction: row-reverse; width: 50vw; position: fixed; bottom: 0; right: 0; background-color: #FFF;">
                        <a-button @click="closeItemBanKDrawer">关闭</a-button>
                      </div>
                    </a-drawer>

                    <div v-if="goods.product.targetType === 'course'" id="info-left-3"
                         class="content-item js-content-item" style="scroll-margin-top: 80px;">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
                        <course-tasks v-show="currentSku.taskDisplay == 1" :sku="currentSku" :i18n="i18n" :activity-metas="activityMetas"></course-tasks>
                        <div v-show="currentSku.taskDisplay != 1" class="goods-empty-content">
                          <img src="/static-dist/app/img/vue/goods/empty-content.png" alt="">
                          <p>{{ 'goods.show_page.tab.catalogue.empty' | trans }}</p>
                        </div>
                    </div>
                    <div v-if="goods.product.targetType === 'classroom'" id="info-left-3"
                         class="content-item js-content-item" style="scroll-margin-top: 80px;">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
                        <div class="searchInput">
                          <a-input-search :placeholder="'course.search.placeholder'|trans" enter-button @search="searchCourse" />
                        </div>
                        <classroom-courses v-if="searchResult.length > 0 || componentsData.classroomCourses.length > 0" :classroomCourses="isSearch ? searchResult : componentsData.classroomCourses"></classroom-courses>
                        <div v-if="searchResult.length === 0 && isSearch" class="emptyCourse">
                          <img class="emptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
                          <p class="emptyCourseContent">{{ 'classroom.search.empty.course' | trans }}</p>
                        </div>
                        <div v-if="componentsData.classroomCourses.length === 0 && !isSearch" class="emptyCourse">
                          <img class="emptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
                          <p class="emptyCourseContent">{{ 'classroom.empty.course' | trans }}</p>
                        </div>
                    </div>

                    <div v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))" id="info-left-4" class="info-left-reviews content-item js-content-item reviews" style="scroll-margin-top: 80px;">
                        <h3 class="content-item__title">{{ 'goods.show_page.tab.reviews'|trans }}</h3>
                        <reviews :can-create="isUserLogin && goods.isMember" :can-operate="goods.canManage"
                                 :report-type="getReportType"
                                 :reply-report-type="getReplyReportType"
                                 :target-type="'goods'"
                                 :current-user-id="currentUserId"
                                 :target-id="goods.id"
                                 :goods="goods"
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
                isSearch: false,
                howActive: 1,
                flag: true,
                isFixed: false,
                timerClick: null,
                timerScroll: null,
                goodsId: window.location.pathname.replace(/[^0-9]/ig, ""),
                currentSku: {},
                searchResult: [],
                bindItemBankExerciseList: [],
                bindItemBankDrawerVisible: false,
            }
        },
        props: {
            goods: {
                type: Object,
                default: null,
            },
            componentsData: {
                type: Object,
                default: null,
            },
            currentUserId: {
                type: String,
                default: null
            },
            targetId: {
                type: [Number, String],
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
                type: [Object, Array],
                default: null,
            },
            ugcReviewSetting: {
                type: [Object, Array],
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
                type: [Number, String],
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
            alertMessage() {
                if (this.goods.type === 'classroom') {
                    return Translator.trans('goods.show_page.tab.classroom.closed_tip');
                }

                if (this.goods.type === 'course' && this.targetId) {
                    return Translator.trans('goods.show_page.tab.course.closed_tip');
                }

                return Translator.trans('validate.learn_content.closed');
            },
            summaryHtml() {
                if (!this.goods.summary) return Translator.trans('goods.show_page.tab.summary_empty_tips');
                return this.goods.summary.replace(/(<table[^>]*>.*?<\/table>)/gs, '<div style="width: 100%; overflow-x: auto;">$1</div>');
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
            searchCourse(value) {
              this.isSearch = Boolean(value)

              axios.get(`/api/classrooms/${this.goods.product.targetId}/courses`, {
                    params: {
                        title: value
                    }
                }).then((res) => {
                    this.searchResult = res.data
                });
            },
            getBindItemBankExercise() {
              axios.get('/api/itemBankExerciseBind', {
                params: {
                  bindType: this.goods.type,
                  bindId: this.currentSku.targetId,
                }
              }).then((res) => {
                this.bindItemBankExerciseList = res.data;
                console.log(this.bindItemBankExerciseList);
              })
            },
            integerPart(num) {
              return num.split('.')[0];
            },
            decimalPart(num) {
              return num.split('.')[1];
            },
            toItemBankExercisePage(exerciseId) {
              window.open(`/item_bank_exercise/${exerciseId}`);
            },
            showItemBanKDrawer() {
              this.bindItemBankDrawerVisible = true;
            },
            closeItemBanKDrawer() {
              this.bindItemBankDrawerVisible = false;
            },
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
                this.getBindItemBankExercise();
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
        beforeMount() {
          this.getBindItemBankExercise();
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
