<template>
  <div class="join-after">
    <detail-head :course-set="details.courseSet" />

    <van-tabs
      class="tabs"
      v-model="active"
      :class="tabFixed ? 'isFixed' : ''"
    >
      <van-tab v-for="item in tabs" :title="$t(`courseLearning.${item}`)" :key="item" />
    </van-tabs>

    <div class="join-after__content">
      <keep-alive>
        <!-- 目录 -->
        <div v-if="active == 0">
          <div class="course-info" @click="gotoGoodsPage">
            <div class="course-info__left">
              <div class="title text-overflow">{{ details.courseSet && details.courseSet.title }}</div>
              <div class="learning-progress">
                <div class="learning-progress__bar" :style="{ width: progress }" />
                <div class="learning-progress__text">
                  {{ progress }}
                </div>
              </div>
            </div>
            <van-icon name="arrow" v-if="details.goodsId" />
          </div>

          <!-- 助教 -->
          <div class="assistant" v-if="details.assistant && details.assistant.weChatQrCode">
            <div class="assistant-btn" @click="showAssistant">
              <div class="assistant-btn__text">
                <i class="iconfont icon-weixin1"></i>
                {{ $t('courseLearning.addTeachingAssistantWeChat') }}
              </div>

              <van-icon class="arrow-icon" name="arrow" />
            </div>

            <van-popup
              class="assistant-info"
              v-model="assistantShow"
              position="bottom"
              closeable
              round
            >
              <img class="avatar" :src="details.assistant.avatar.middle" :alt="$t('courseLearning.picture')" />
              <p class="nickname">{{ details.assistant.nickname }}</p>
              <p class="desc">{{ $t('courseLearning.addTheTeachingAssistant') }}</p>
              <img class="wechat" :src="details.assistant.weChatQrCode" :alt="$t('courseLearning.qRCodePicture')" />
              <div class="tips" v-if="isWeixin">{{ $t('courseLearning.longPressThePicture') }}</div>
              <div class="tips" v-else>{{ $t('courseLearning.longPressThePicture2') }}</div>
            </van-popup>
          </div>

          <afterjoin-directory :error-msg="errorMsg" @showDialog="showDialog" />
        </div>

        <!-- 问答、话题、笔记、评价 通过动态组件实现 -->
        <component v-else :is="currentTabComponent"></component>
      </keep-alive>
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

    <van-overlay :show="show" z-index="1000" @click="clickCloseOverlay" />
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';
import afterjoinDirectory from './detail/afterjoin-directory';
import collectUserInfo from '@/mixins/collectUserInfo';
import { mapState, mapMutations } from 'vuex';
import { Dialog, Toast } from 'vant';
import infoCollection from '@/components/info-collection.vue';
import Api from '@/api';
import * as types from '@/store/mutation-types.js';

// tabs 子组件
import Question from './question/index.vue';
import Discussion from './discussion/index.vue';
import Notes from './notes/index.vue';
import Evaluation from './evaluation/index.vue';
// 为什么第一个为空？ 目录是原有功能，为减少风险，暂时保留
const tabComponent = ['', 'Question', 'Discussion', 'Notes', 'Evaluation'];

export default {
  inheritAttrs: true,

  components: {
    // eslint-disable-next-line vue/no-unused-components
    Directory,
    DetailHead,
    afterjoinDirectory,
    infoCollection,
    Question,
    Discussion,
    Notes,
    Evaluation
  },

  props: {
    details: {
      type: Object,
      value: () => {}
    }
  },

  data() {
    return {
      headBottom: 0,
      active: 0,
      scrollFlag: false,
      tabs: ['catalogue', 'question', 'discussion', 'notes', 'evaluation'],
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

    isWeixin() {
      const ua = navigator.userAgent.toLowerCase();
      return ua.match(/MicroMessenger/i) == 'micromessenger';
    },

    currentTabComponent() {
      return tabComponent[this.active];
    }
  },
  watch: {
    selectedPlanId: function(val, oldVal) {
      this.active = 0;
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

  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },

  methods: {
    ...mapMutations('course', {
      setCurrentJoin: types.SET_CURRENT_JOIN_COURSE,
    }),

    gotoGoodsPage() {
      // 班级课程，不存在 goodsId
      if (!this.details.goodsId) return;

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
  },
};
</script>

<style lang="scss" scoped>
.tabs {
  box-shadow: 0px 2px 6px 0px rgba(49, 49, 49, 0.1);

  /deep/ .van-tabs__wrap {
    height: vw(56);
  }
}

.course-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-right: vw(16);
  padding-left: vw(16);
  height: vw(56);
  border-bottom: vw(8) solid #f5f5f5;

  &__left {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    height: 100%;

    .title {
      width: vw(300);
      font-size: vw(14);
      font-weight: 500;
      color: #333;
      line-height: vw(20);
    }

    .learning-progress {
      position: relative;
      width: vw(250);
      height: vw(8);
      background: #f5f5f5;
      border-radius: 4px;

      &__bar {
        position: absolute;
        top: 0;
        left: 0;
        height: vw(8);
        background: $primary-color;
        border-radius: 4px;
      }

      &__text {
        position: absolute;
        right: vw(-8);
        top: 50%;
        transform: translate(100%, -50%);
        font-size: vw(12);
        color: $primary-color;
        line-height: vw(16);
      }
    }
  }
}

.assistant {
  .assistant-btn {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 vw(16);
    height: vw(32);
    border-bottom: 1px solid #f5f5f5;

    &__text {
      display: flex;
      align-items: center;
      width: vw(300);
      font-size: 12px;
      color: #666;
      line-height: 16px;

      i {
        margin-right: vw(4);
        color: #03c777;
      }
    }
  }

  .assistant-info {
    text-align: center;

    .avatar {
      width: 20%;
      height: 20%;
      margin-top: vw(40);
      margin-top: vw(24);
      border-radius: 50%;
    }

    .nickname {
      padding-top: vw(10);
      color: #bbb;
    }

    .desc {
      padding-top: vw(10);
    }

    .wechat {
      margin: vw(20) 0 vw(10);
    }

    .tips {
      margin-bottom: vw(10);
      font-size: vw(14);
    }
  }
}
</style>
