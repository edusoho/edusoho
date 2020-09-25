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
      </div>

      <!-- 学员评价 -->
      <div v-show="active == 2">
        <review-list
          ref="review"
          :target-id="details.courseSet.id"
          :reviews="courseSettings.show_review == 1 ? details.reviews : []"
          title="学员评价"
          defaul-value="暂无评价"
          type="course"
        />
      </div>
    </div>

    <!-- 个人信息表单填写 -->
    <van-action-sheet
      v-model="isShowForm"
      :title="userInfoCollectForm.formTitle"
      :close-on-click-overlay="false"
      @cancel="onCancelForm"
    >
      <info-collection
        :userInfoCollectForm="userInfoCollectForm"
        :formRule="userInfoCollectForm.items"
        @submitForm="onCancelForm"
      ></info-collection>
    </van-action-sheet>
  </div>
</template>
<script>
import reviewList from '@/containers/classroom/review-list';
import Directory from './detail/directory';
import DetailHead from './detail/head';
import DetailPlan from './detail/plan';
import Teacher from './detail/teacher';
import afterjoinDirectory from './detail/afterjoin-directory';
import collectUserInfo from '@/mixins/collectUserInfo';
import { mapState, mapMutations } from 'vuex';
import { Dialog, Toast } from 'vant';
import infoCollection from '../info-collection/index';
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
      tabs: ['课程介绍', '课程目录', '学员评价'],
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
  },
  watch: {
    selectedPlanId: function(val, oldVal) {
      this.active = 1;
    },
    currentJoin: {
      handler(val, oldVal) {
        if (val) {
          Toast.loading({
            message: '加载中...',
            forbidClick: true,
          });
          this.getInfoCollectionEvent(this.paramsList).then(res => {
            if (Object.keys(res).length) {
              this.userInfoCollect = res;
              this.getInfoCollectionForm().then(res => {
                this.isShowForm = true;
                Toast.clear();
              });
            }
          });
        }
      },
      // 代表在wacth里声明了firstName这个方法之后立即先去执行handler方法，如果设置了false，那么效果和上边例子一样
      immediate: true,
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
    reviewList,
    afterjoinDirectory,
    infoCollection,
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },

  methods: {
    ...mapMutations('course', {
      setCurrentJoin: types.SET_CURRENT_JOIN_COURSE,
    }),
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
      } else if (errorCode === 'vip.member_expired') {
        errorMessage = '会员已到期，请及时续费会员';
        confirmCallback = () => {
          this.$router.push({
            path: `/vip`,
          });
        };
        this.callConfirm(errorMessage, confirmCallback);
      } else {
        Toast.fail(this.errorMsg);
      }
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
  },
};
</script>
