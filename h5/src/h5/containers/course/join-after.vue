<template>
  <div class="join-after">
    <detail-head :course-set="details.courseSet" />

    <van-tabs
      id="tabs"
      ref="tabs"
      v-model="active"
      :class="tabFixed ? 'isFixed' : ''"
    >
      <van-tab v-for="item in tabs" :title="item" :key="item" />
    </van-tabs>

    <!-- 课程目录 -->
    <div class="join-after__content">
      <div v-show="active == 1">
        <div
          id="progress-bar"
          :class="['progress-bar', tabFixed ? 'progress-bar-fix' : '']"
        >
          <div class="progress-bar__content">
            <div :style="{ width: progress }" class="progress-bar__rate" />
          </div>
          <div class="progress-bar__text">{{ progress }}</div>
        </div>
        <!-- 助教 -->
        <div
          v-if="details.assistant && details.assistant !== null"
          class="assistant-show clearfix"
        >
          <div class="assistant-show__icon">
            <i class="iconfont icon-weixin1"></i>
          </div>
          <div class="assistant-show__text" @click="showAssistant">
            为保证更好地学习效果，请点击此处添加助教老师微信
          </div>
        </div>
        <!-- 助教弹出框 -->
        <van-popup
          class="assistant-show__content"
          v-model="assistantShow"
          closeable
          round
        >
          <img
            class="avatar"
            :src="details.assistant.smallAvatar"
            alt="助教图片"
          />
          <p class="name">
            {{ details.assistant.title }}课程助教——{{
              details.assistant.nickname
            }}
          </p>
          <p class="text">请务必添加助教老师微信，否则无法上课哦~</p>
          <img
            class="wechat"
            :src="details.assistant.weChatQrCode"
            alt="二维码图片"
          />
          <van-button type="primary" block @click="downloadCodeImg()"
            >保存图片，前往微信添加</van-button
          >
        </van-popup>

        <afterjoin-directory :error-msg="errorMsg" @showDialog="showDialog" />
      </div>

      <div v-show="active == 0">
        <!-- 课程计划 -->
        <detail-plan @switchPlan="showDialog" />

        <div class="segmentation" />
        <!-- 课程介绍 -->
        <e-panel title="课程介绍">
          <div v-html="summary" />
        </e-panel>
        <div class="segmentation" />

        <!-- 教师介绍 -->
        <teacher :teacher-info="details.teachers" class="teacher" />
        <div class="segmentation" />

        <!-- 评价 -->
        <reviews :details="details" v-if="show_course_review == 1" />
        <div class="segmentation" />
        <div class="segmentation" />
        <div class="segmentation" />
      </div>
    </div>

    <!-- 个人信息表单填写 -->
    <van-action-sheet
      v-model="isShowForm"
      class="minHeight50"
      :title="userInfoCollectForm.formTitle"
      :close-on-click-overlay="false"
      :safe-area-inset-bottom="true"
      @cancel="onCancelForm"
    >
      <info-collection
        :userInfoCollectForm="userInfoCollectForm"
        :formRule="userInfoCollectForm.items"
        @submitForm="onCancelForm"
      ></info-collection>
    </van-action-sheet>
    <e-footer
      @click.native="gotoGoodsPage"
      v-if="active == 0 && this.details.goodsId"
    >
      去商品页
    </e-footer>

    <van-overlay :show="show" z-index="1000" @click="clickCloseOverlay" />
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';
import DetailPlan from './detail/plan';
import Teacher from './detail/teacher';
import afterjoinDirectory from './detail/afterjoin-directory';
import Reviews from '@/components/reviews';
import collectUserInfo from '@/mixins/collectUserInfo';
import { mapState, mapMutations } from 'vuex';
import { Dialog, Toast } from 'vant';
import infoCollection from '@/components/info-collection.vue';
import Api from '@/api';
import * as types from '@/store/mutation-types.js';

export default {
  inheritAttrs: true,
  props: {
    details: {
      type: Object,
      value: () => {},
    },
  },
  data() {
    return {
      headBottom: 0,
      active: 1,
      scrollFlag: false,
      tabs: ['课程介绍', '课程目录'],
      tabFixed: false,
      errorMsg: '',
      offsetTop: '', // tab页距离顶部高度
      offsetHeight: '', // 元素自身的高度
      isFixed: false,
      courseSettings: {},
      isShowForm: false,
      paramsList: {
        action: 'buy_after',
        targetType: 'course',
        targetId: this.details.id,
      },
      show: false,
      show_course_review: this.$store.state.goods.show_course_review,
      assistantShow: false,
    };
  },
  mixins: [collectUserInfo],
  computed: {
    ...mapState('course', {
      selectedPlanId: state => state.selectedPlanId,
      currentJoin: state => state.currentJoin,
    }),
    ...mapState(['user']),
    progress() {
      if (!Number(this.details.publishedTaskNum)) return '0%';

      return parseInt(this.details.progress.percent) + '%';
    },
    summary() {
      return this.details.summary || this.details.courseSet.summary;
    },
    isClassCourse() {
      return Number(this.details.parentId);
    },
    currentTypeText() {
      return this.details.classroom ? '班级' : '课程';
    },
  },
  watch: {
    selectedPlanId: function(val, oldVal) {
      this.active = 1;
    },
    currentJoin: {
      handler(val, oldVal) {
        if (val) {
          Toast.loading({
            duration: 0,
            message: '加载中...',
            forbidClick: true,
          });
          this.getInfoCollectionEvent(this.paramsList).then(res => {
            if (Object.keys(res).length) {
              this.userInfoCollect = res;
              this.getInfoCollectionForm(res.id).then(res => {
                this.isShowForm = true;
                Toast.clear();
              });
              return;
            }
            Toast.clear();
          });
        }
      },
      // 代表在wacth里声明了firstName这个方法之后立即先去执行handler方法，如果设置了false，那么效果和上边例子一样
      immediate: true,
    },
    $route(to, from) {
      this.resetFrom();
    },
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    this.$nextTick(function() {
      const NAVBARHEIGHT = 46;
      const SELFHEIGHT = 44;
      const IMGHEIGHT = document.getElementById('course-detail__head')
        .offsetHeight;
      // 这里要得到top的距离和元素自身的高度
      this.offsetTop = IMGHEIGHT + NAVBARHEIGHT;
      this.offsetHeight = SELFHEIGHT;
    });
  },
  async created() {
    this.showDialog();
    this.courseSettings = await Api.getSettings({
      query: {
        type: 'course',
      },
    }).catch(err => {
      console.error(err);
    });
  },
  components: {
    // eslint-disable-next-line vue/no-unused-components
    Directory,
    DetailHead,
    DetailPlan,
    Teacher,
    afterjoinDirectory,
    infoCollection,
    Reviews,
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },

  methods: {
    ...mapMutations('course', {
      setCurrentJoin: types.SET_CURRENT_JOIN_COURSE,
    }),

    gotoGoodsPage() {
      this.$router.push({
        path: `/goods/${this.details.goodsId}/show`,
        query: {
          targetId: this.details.id,
        },
      });
    },
    showDialog() {
      if (!this.details.member) return;

      let errorCode = '';
      if (this.details.member.access) {
        errorCode = this.details.member.access.code;
      }
      if (!errorCode || errorCode === 'success') {
        return;
      }

      // 学习任务报错信息
      this.errorMsg = this.getErrorMsg(errorCode);

      let errorMessage = '';
      let confirmCallback = function() {};

      if (
        errorCode === 'course.expired' ||
        (errorCode === 'member.expired' && !this.isClassCourse)
      ) {
        // 班级课程不可以退出, 普通课程可以退出
        errorMessage = '课程已到期，无法继续学习，是否退出';
        const params = { id: this.details.id };
        confirmCallback = () => {
          Api.deleteCourse({ query: params }).then(res => {
            if (res.success) {
              window.location.reload();
              return;
            }
            Toast.fail('退出课程失败，请稍后重试');
          });
        };
        this.callConfirm(errorMessage, confirmCallback);
        return;
      }
      const vipName = this.details.vipLevel ? this.details.vipLevel.name : '';
      const vipStatus = {
        // 用户会员服务已过期
        'vip.member_expired': {
          message: `您的会员已到期，会员${this.currentTypeText}已无法学习，请续费会员，或退出后重新购买${this.currentTypeText}。`,
          confirmButtonText: '续费会员',
          cancelButtonText: `退出${this.currentTypeText}`,
        },
        // 当前用户并不是vip
        'vip.not_member': {
          message: `您不是${vipName}，请购买${vipName}后兑换该${this.currentTypeText}学习。或退出后重新购买${this.currentTypeText}。`,
          confirmButtonText: '购买会员',
          cancelButtonText: `退出${this.currentTypeText}`,
        },
        // 用户会员等级过低
        'vip.level_low': {
          message: `您不是${vipName}，请购买${vipName}后兑换该${this.currentTypeText}学习。或退出后重新购买${this.currentTypeText}。`,
          confirmButtonText: '购买会员',
          cancelButtonText: `退出${this.currentTypeText}`,
        },
        // 会员等级无效
        'vip.level_not_exist': {
          message: `您不是${vipName}，请购买${vipName}后兑换该${this.currentTypeText}学习。或退出后重新购买${this.currentTypeText}。`,
          confirmButtonText: '购买会员',
          cancelButtonText: `退出${this.currentTypeText}`,
        },
        // 课程会员被删除
        'vip.vip_right_not_exist': {
          message: `很抱歉，该${this.currentTypeText}已不属于会员权益，请退出后重新购买。`,
          showConfirmButton: false,
          cancelButtonText: `退出${this.currentTypeText}`,
        },
      };

      if (vipStatus[errorCode]) {
        this.vipCallConfirm(vipStatus[errorCode]);
        return;
      }
      Toast.fail(this.errorMsg);
    },
    getErrorMsg(code) {
      switch (code) {
        case 'course.not_found':
          return '当前课程不存在';
        case 'course.unpublished':
          return '当前课程未发布';
        case 'course.expired':
          return '当前课程已过期';
        case 'course.not_arrive':
          return '当前课程还不能学习';
        case 'user.not_login':
          return '用户未登录';
        case 'user.locked':
          return '用户被锁定';
        case 'member.not_found':
          return '用户未加入课程';
        case 'member.expired':
          return '课程已过期';
        case 'vip.vip_closed':
          return '网校已关闭会员功能';
        case 'vip.not_login':
          return '用户未登录';
        case 'vip.not_member':
          return '当前用户并不是vip';
        case 'vip.member_expired':
          return '用户会员服务已过期';
        case 'vip.level_not_exist':
          return '用户会员等级或课程会员不存在';
        case 'vip.level_low':
          return '用户会员等级过低';
        default:
          return '异常错误';
      }
    },
    callConfirm(message, callback) {
      Dialog.confirm({
        message,
        title: '',
      })
        .then(() => {
          callback();
        })
        .catch(() => {});
    },
    vipCallConfirm(config) {
      this.show = true;
      Dialog.confirm({
        ...config,
        title: '',
        confirmButtonColor: '#1895E7',
        cancelButtonColor: '#FD4852',
        messageAlign: 'left',
        overlay: false,
        beforeClose: this.beforeClose,
      });
    },

    clickCloseOverlay() {
      this.show = false;
      Dialog.close();
    },

    beforeClose(action, done) {
      if (action === 'confirm') {
        this.$router.push({
          path: `/vip`,
          query: {
            id: this.details.vipLevel.id,
          },
        });
        done();
        return;
      }

      if (!this.show) return;

      if (this.currentTypeText == '班级') {
        this.deleteClassroom(done);
      } else {
        this.deleteCourse(done);
      }
    },

    deleteClassroom(done) {
      const { id, goodsId } = this.details.classroom;
      const params = { id };
      Api.deleteClassroom({ query: params }).then(res => {
        if (res.success) {
          this.$router.replace({
            path: `/goods/${goodsId}/show`,
            query: {
              backUrl: '/',
            },
          });
          done();
        }
      });
    },

    deleteCourse(done) {
      const { id, goodsId } = this.details;
      const params = { id };
      Api.deleteCourse({ query: params }).then(res => {
        if (res.success) {
          this.$router.replace({
            path: `/goods/${goodsId}/show`,
            query: {
              targetId: id,
            },
          });
          done();
        }
      });
    },

    handleScroll() {
      const SWIPER = document.getElementById('swiper-directory');
      const DOCUMENTHEIGHT = document.documentElement.scrollHeight;
      const CLIENTHEIGHT = document.documentElement.clientHeight;
      // 得到页面滚动的距离
      const scrollTop =
        window.pageYOffset ||
        document.documentElement.scrollTop ||
        document.body.scrollTop;
      // 判断页面滚动的距离是否大于吸顶元素的位置,并将课程的高度固定。由于ios浏览器可以随意拖动，会造成吸顶抖动，所以这里加上必须可视高度是否大于窗口可视高度
      if (
        scrollTop > this.offsetTop &&
        DOCUMENTHEIGHT - this.offsetTop > CLIENTHEIGHT
      ) {
        this.tabFixed = true;
        // eslint-disable-next-line no-unused-expressions
        SWIPER ? SWIPER.classList.add('swiper-directory-fix') : null;
      } else {
        this.tabFixed = false;
        // eslint-disable-next-line no-unused-expressions
        SWIPER ? SWIPER.classList.remove('swiper-directory-fix') : null;
      }
    },
    onCancelForm() {
      this.setCurrentJoin(false);
      this.isShowForm = false;
    },

    showAssistant() {
      this.assistantShow = true;
    },

    downloadCodeImg() {
      const a = document.createElement('a');
      a.download = name || '微信二维码';
      a.href = this.details.assistant.weChatQrCode;
      a.click();
    },
  },
};
</script>
