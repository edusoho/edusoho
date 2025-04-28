<template>
  <div class="cd-container">
    <div class="product-breadcrumb"><a href="/">{{ 'homepage'|trans }}</a>
      <span v-for="(item, index) in goods.breadcrumbs" :key="index">/ <a
        :href="'/' + goods.type + '/explore/' + item.code">{{ item.name }}</a></span>
      / {{ goods.title|removeHtml }}
    </div>
    <a-alert
      v-if="goods.product.target.status == 'closed'"
      class="mt16"
      :message="alertMessage"
      type="warning"
      show-icon
    />
    <detail :vip-enabled="vipEnabled" :drp-recruit-switch="drpRecruitSwitch" :goodsSetting="goodsSetting"
            :timestamp="timestamp" :goods="goods" :currentSku="currentSku" @changeSku="changeSku"
            :current-url="currentUrl" :is-user-login="isUserLogin">
    </detail>

    <div class="product-info clearfix" v-if="goods.id">
      <div class="product-info__left info-left pull-left">
        <div v-if="isFixed" class="fixed">
          <div class="cd-container clearfix">
            <ul class="info-left__nav pull-left">
              <li :class="howActive === 1 ? 'active' : ''"><a href="#info-left-1">{{
                  'goods.show_page.tab.intro'|trans
                }}</a>
              </li>
              <li :class="howActive === 2 ? 'active' : ''"><a href="#info-left-2">题库</a>
              </li>
              <li :class="howActive === 3 ? 'active' : ''"><a
                href="#info-left-3">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
              </li>
              <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))"
                  :class="howActive === 4 ? 'active' : ''">
                <a href="#info-left-4">{{ 'goods.show_page.tab.reviews'|trans }}</a>
              </li>
            </ul>
            <div class="buy__btn pull-right">
              <buy-sku :sku="currentSku"
                       :btn-class="goods.product.target.status == 'closed' ? 'product-detail__btn js-handleLearnOnMessage' : 'product-detail__btn'"
                       :isShow="false" :is-user-login="isUserLogin" :goods="goods"></buy-sku>
            </div>
          </div>
        </div>

        <ul class="info-left__nav" ref="infoLeftNav">
          <li :class="howActive === 1 ? 'active' : ''">
            <a href="#info-left-1">{{ 'goods.show_page.tab.intro'|trans }}</a>
          </li>
          <li :class="howActive === 2 ? 'active' : ''">
            <a href="#info-left-2">题库</a>
          </li>
          <li :class="howActive === 3 ? 'active' : ''">
            <a href="#info-left-3">{{ 'goods.show_page.tab.catalogue'|trans }}</a>
          </li>
          <li v-if="ugcReviewSetting.enable_review == 1
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))"
              :class="howActive === 4 ? 'active' : ''">
            <a href="#info-left-4">{{ 'goods.show_page.tab.reviews'|trans }}</a>
          </li>
        </ul>

        <div class="info-left__content">
          <div id="info-left-1" class="content-item js-content-item" style="scroll-margin-top: 52px;">
            <h3 class="content-item__title">{{ 'goods.show_page.tab.intro'|trans }}</h3>
            <div v-html="summaryHtml" class="description-content"
                 style="padding-left: 14px; padding-top: 10px; color: #000000;"></div>
          </div>

          <div id="info-left-2" class="js-content-item item-bank-exercise-container">
            <div class="header">
              <div class="title">题库</div>
              <div v-if="bindItemBankExerciseList.length > 1"
                   class="show-button"
                   @click="showItemBanKDrawer">
                <div>{{ `查看全部（${bindItemBankExerciseList.length}）` }}</div>
                <a-icon type="right" class="icon"/>
              </div>
            </div>
            <div v-if="bindItemBankExerciseList.length && bindItemBankExerciseList.length > 0" class="item-bank-exercise-list">
              <div class="flex" style="width: 100%">
                <img :src=bindItemBankExerciseList[0].itemBankExercise.cover.middle alt="">
                <div class="content">
                  <div>
                    <a-tooltip placement="top">
                      <template slot="title">
                        <div style="max-width: 216px;">{{ bindItemBankExerciseList[0].itemBankExercise.title }}</div>
                      </template>
                      <div class="title">{{ bindItemBankExerciseList[0].itemBankExercise.title }}</div>
                    </a-tooltip>
                    <div class="exercise-container">
                      <div class="title">章节练习：<span>{{ bindItemBankExerciseList[0].chapterExerciseNum }}</span></div>
                      <div class="flex justify-between">
                        <div class="title">试卷练习：<span>{{ bindItemBankExerciseList[0].assessmentNum }}</span></div>
                        <div class="col-price"><span>¥</span>{{`${integerPart(bindItemBankExerciseList[0].itemBankExercise.price)}.` }}<span>{{decimalPart(bindItemBankExerciseList[0].itemBankExercise.price)}}</span></div>
                      </div>
                    </div>
                  </div>
                  <div class="price"><span>¥</span>{{`${integerPart(bindItemBankExerciseList[0].itemBankExercise.price)}.` }}<span>{{decimalPart(bindItemBankExerciseList[0].itemBankExercise.price)}}</span></div>
                </div>
              </div>
              <div class="show-button">
                <a-button type="primary" ghost @click="toItemBankExercisePage(bindItemBankExerciseList[0].itemBankExercise.id)">查看</a-button>
              </div>
            </div>
            <div v-else class="empty">暂无绑定的题库哦～</div>
          </div>
          <a-drawer
            :placement="drawerPlacement"
            :width="drawerWidth"
            :height="drawerHeight"
            :closable="false"
            :visible="bindItemBankDrawerVisible"
            :headerStyle="{position: 'fixed', width: '100%', backgroundColor: '#FFF', zIndex: '1020'}"
            wrapClassName="item-bank-exercise-drawer"
            @close="closeItemBanKDrawer"
            :bodyStyle="{padding: '0',}"
          >
            <div class="header">
                <div class="title">题库</div>
                <a-icon type="close" @click="closeItemBanKDrawer"/>
            </div>
            <div class="body-container">
              <div v-for="item in bindItemBankExerciseList">
                <div class="item-bank-exercise-list">
                  <div class="flex width-full">
                    <img :src="item.itemBankExercise.cover.middle" alt="">
                    <div class="content">
                      <div class="flex-col justify-between">
                        <a-tooltip placement="top">
                          <template slot="title">
                            <div style="max-width: 216px;">{{ item.itemBankExercise.title }}</div>
                          </template>
                          <div class="title">{{ item.itemBankExercise.title }}</div>
                        </a-tooltip>
                        <div class="exercise-container">
                          <div class="exercise">章节练习：<span>{{ item.chapterExerciseNum }}</span></div>
                          <div class="flex justify-between">
                            <div class="exercise">试卷练习：<span>{{ item.assessmentNum }}</span></div>
                            <div class="col-price"><span>¥</span>{{`${integerPart(item.itemBankExercise.price)}.` }}<span>{{ decimalPart(item.itemBankExercise.price) }}</span></div>
                          </div>
                        </div>
                      </div>
                      <div class="price"><span>¥</span>{{`${integerPart(item.itemBankExercise.price)}.` }}<span>{{ decimalPart(item.itemBankExercise.price) }}</span></div>
                    </div>
                  </div>
                  <div class="show-button">
                    <a-button type="primary" ghost @click="toItemBankExercisePage(item.itemBankExercise.id)">查看</a-button>
                  </div>
                </div>
              </div>
            </div>
            <div class="footer"><a-button @click="closeItemBanKDrawer">关闭</a-button></div>
          </a-drawer>

          <div v-if="goods.product.targetType === 'course'" id="info-left-3"
               class="content-item js-content-item" style="scroll-margin-top: 100px;">
            <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
            <course-tasks v-show="currentSku.taskDisplay == 1" :sku="currentSku" :i18n="i18n"
                          :activity-metas="activityMetas"></course-tasks>
            <div v-show="currentSku.taskDisplay != 1" class="goods-empty-content">
              <img src="/static-dist/app/img/vue/goods/empty-content.png" alt="">
              <p>{{ 'goods.show_page.tab.catalogue.empty' | trans }}</p>
            </div>
          </div>
          <div v-if="goods.product.targetType === 'classroom'" id="info-left-3"
               class="content-item js-content-item" style="scroll-margin-top: 100px;">
            <h3 class="content-item__title">{{ 'goods.show_page.tab.catalogue'|trans }}</h3>
            <div class="searchInput">
              <a-input-search :placeholder="'course.search.placeholder'|trans" enter-button @search="searchCourse"/>
            </div>
            <classroom-courses v-if="searchResult.length > 0 || componentsData.classroomCourses.length > 0"
                               :classroomCourses="isSearch ? searchResult : componentsData.classroomCourses"></classroom-courses>
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
                                 && ((ugcReviewSetting.enable_course_review == 1 && goods.type == 'course') || (ugcReviewSetting.enable_classroom_review == 1 && goods.type == 'classroom'))"
               id="info-left-4" class="info-left-reviews content-item js-content-item reviews"
               style="scroll-margin-top: 100px;">
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
                 style="padding-left: 14px; padding-top: 10px;">{{ 'goods.show_page.tab.reviews_empty_tips'|trans }}
            </div>
          </div>
        </div>
      </div>

      <div class="product-info__right pull-right">
        <teacher :teachers="currentSku.teachers"/>
        <qr :mpQrcode="componentsData.mpQrCode"/>
        <recommend :goods="goods" :recommendGoods="componentsData.recommendGoods"/>
        <certificate :goodsId="goodsId" :sku="currentSku"/>
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
      goodsId: window.location.pathname.replace(/[^0-9]/ig, ''),
      currentSku: {},
      searchResult: [],
      bindItemBankExerciseList: [],
      bindItemBankDrawerVisible: false,
    };
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
    drawerPlacement() {
      return window.innerWidth <= 992 ? 'bottom' : 'right';
    },
    drawerWidth() {
      return window.innerWidth <= 992 ? '100vw' : '70vw';
    },
    drawerHeight() {
      return window.innerWidth <= 992 ? '80vh' : '100vh';
    },
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
      this.isSearch = Boolean(value);

      axios.get(`/api/classrooms/${this.goods.product.targetId}/courses`, {
        params: {
          title: value
        }
      }).then((res) => {
        this.searchResult = res.data;
      });
    },
    getBindItemBankExercise() {
      axios.get('/api/itemBankExerciseBind', {
        params: {
          bindType: this.goods.type,
          bindId: this.goods.type === 'course' ? this.targetId : this.goods.product.target.id,
        }
      }).then((res) => {
        this.bindItemBankExerciseList = res.data;
      });
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
    changeSku(targetId) {
      for (const key in this.goods.specs) {
        this.$set(this.goods.specs[key], 'active', false);
        if (targetId == this.goods.specs[key]['targetId']) {
          this.$set(this.goods.specs[key], 'active', true);
          this.currentSku = this.goods.specs[key];
        }
      }
      this.getBindItemBankExercise();
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
      }, 100);
    },
    calcScrollTop(value) {
      const eleArr = document.querySelectorAll('.js-content-item');
      if (eleArr.length === 0) return;
      const firstElementTop = eleArr[0].offsetTop;
      if (value < firstElementTop - 52) {
        this.howActive = 1;
        return;
      }
      for (let i = 1; i < eleArr.length; i++) {
        const elementTop = eleArr[i].offsetTop;
        if (value <= elementTop - 100) {
          if (this.howActive !== i + 1) {
            this.howActive = i + 1;
          }
          return;
        }
      }
    }
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
    window.addEventListener('scroll', this.handleScroll);

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
        window.addEventListener('scroll', this.handleScroll);
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
};
</script>

<style lang="less">
.item-bank-exercise-drawer {
  color: red;
  .ant-drawer-body {
    .footer {
      padding: 16px 24px;
      border-top: 1px solid #EFF0F5;
      display: flex;
      flex-direction: row-reverse;
      position: fixed;
      bottom: 0;
      right: 0;
      background-color: #FFF;
      width: 100%
    }
  }
}
</style>
