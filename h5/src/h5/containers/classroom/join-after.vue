<template>
  <div class="course-detail classroom-detail">
    <div class="join-after">
      <detail-head
        ref="head"
        :cover="details.cover"
        :price="planDetails.price"
        :classroom-id="details.classId"
      />

      <van-tabs v-model="active" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="$t(item)" :key="item" />
      </van-tabs>

      <!-- 班级介绍 -->
      <div v-show="active == 0">
        <detail-plan
          :details="planDetails"
          :join-status="details.joinStatus"
          :showNumberData="showNumberData"
        />
        <div class="segmentation" />
        <e-panel ref="about" :title="$t('classLearning.intor')" class="about">
          <div v-html="details.summary" />
        </e-panel>
        <div class="segmentation" />

        <!-- 教师介绍 -->
        <teacher
          :teacher-info="details.teachers"
          class="teacher"
          :title="$t('classLearning.teacher')"
        />
        <div class="segmentation" />

        <teacher
          :teacher-info="details.headTeacher ? [details.headTeacher] : []"
          class="teacher"
          :title="$t('classLearning.classTeacher')"
          :defaul-value="$t('classLearning.headTeacherHasNotBeenSet')"
        />
        <div class="segmentation" />

        <!-- 评价 -->
        <reviews :details="details" v-if="show_classroom_review" />
        <div class="segmentation" />
        <div class="segmentation" />
        <div class="segmentation" />
      </div>

      <!-- 班级课程 -->
      <div v-show="active == 1">
        <course-set-list
          ref="course"
          :feedback="!errorMsg"
          :course-sets="details.courses"
          :disable-mask="true"
          :title="$t('classLearning.course')"
          :defaul-value="$t('classLearning.empty')"
          @click.native="showDialog('click')"
        />
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
      {{ $t('classLearning.viewDetails') }}
    </e-footer>

    <van-overlay :show="show" z-index="1000" @click="clickCloseOverlay" />
  </div>
</template>

<script>
import teacher from './teacher';
import detailHead from './head';
import courseSetList from './course-set-list';
import detailPlan from './plan';
import directory from '../course/detail/directory';
import collectUserInfo from '@/mixins/collectUserInfo';
import infoCollection from '@/components/info-collection.vue';
import Reviews from '@/components/reviews';
import { mapState, mapMutations } from 'vuex';
import { Dialog, Toast } from 'vant';
import Api from '@/api';
import * as types from '@/store/mutation-types.js';
// eslint-disable-next-line no-unused-vars
const TAB_HEIGHT = 44;

export default {
  components: {
    // eslint-disable-next-line vue/no-unused-components
    directory,
    detailHead,
    detailPlan,
    teacher,
    courseSetList,
    infoCollection,
    Reviews,
  },
  props: ['details', 'planDetails'],
  data() {
    return {
      headBottom: 0,
      active: 1,
      scrollFlag: false,
      tabs: ['classLearning.intor', 'classLearning.course'],
      tabsClass: '',
      errorMsg: '',
      classroomSettings: {},
      isShowForm: false,
      paramsList: {
        action: 'buy_after',
        targetType: 'classroom',
        targetId: this.details.classId,
      },
      showNumberData: '',
      show: false,
      show_classroom_review: this.$store.state.goods.show_classroom_review,
    };
  },
  mixins: [collectUserInfo],
  computed: {
    ...mapState('classroom', {
      currentJoin: state => state.currentJoin,
    }),
  },
  async created() {
    this.classroomSettings = await Api.getSettings({
      query: {
        type: 'classroom',
      },
    }).catch(err => {
      console.error(err);
    });
    this.getGoodSettings();
  },
  watch: {
    currentJoin: {
      handler(val, oldVal) {
        console.log(val);
        if (val) {
          console.log(val);
          Toast.loading({
            duration: 0,
            message: '加载中...',
            forbidClick: true,
          });
          const paramsList = {
            action: 'buy_after',
            targetType: 'classroom',
            targetId: this.details.classId,
          };
          this.getInfoCollectionEvent(paramsList).then(res => {
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
    window.addEventListener('touchmove', this.handleScroll);

    this.showDialog();
  },
  destroyed() {
    window.removeEventListener('touchmove', this.handleScroll);
  },
  methods: {
    ...mapMutations('classroom', {
      setCurrentJoin: types.SET_CURRENT_JOIN_CLASS,
    }),
    replaceGoodsPage() {
      this.$router.replace({
        path: `/goods/${this.details.goodsId}/show`,
      });
    },
    gotoGoodsPage() {
      this.$router.push({
        path: `/goods/${this.details.goodsId}/show`,
      });
    },
    showDialog(value) {
      let code = '';
      let errorMessage = '';

      // 点击班级课程
      if (value) {
        this.errorMsg = '';
        return;
      }

      let confirmCallback = function() {};

      if (!this.details.member) return;

      if (this.details.member.access) {
        code = this.details.member.access.code;
      }
      if (!code || code === 'success') {
        return;
      }

      // 学习任务报错信息
      this.errorMsg = this.getErrorMsg(code);

      // 错误处理
      if (code === 'classroom.expired' || code === 'member.expired') {
        errorMessage = '班级已到期，无法继续学习，是否退出';
        const params = { id: this.details.classId };
        confirmCallback = () => {
          Api.deleteClassroom({ query: params }).then(res => {
            if (res.success) {
              window.location.reload();
              return;
            }
            Toast.fail('退出班级失败，请稍后重试');
          });
        };
        this.callConfirm(errorMessage, confirmCallback);
        return;
      }
      const vipName = this.details.vipLevel ? this.details.vipLevel.name : '';
      const vipStatus = {
        // 用户会员服务已过期
        'vip.member_expired': {
          message: `您的会员已到期，会员班级已无法学习，请续费会员，或退出后重新购买班级。`,
          confirmButtonText: '续费会员',
        },
        // 当前用户并不是vip
        'vip.not_member': {
          message: `您不是${vipName}，请购买${vipName}后兑换该班级学习。或退出后重新购买班级。`,
          confirmButtonText: '购买会员',
        },
        // 用户会员等级过低
        'vip.level_low': {
          message: `您不是${vipName}，请购买${vipName}后兑换该班级学习。或退出后重新购买班级。`,
          confirmButtonText: '购买会员',
        },
        // 会员等级无效
        'vip.level_not_exist': {
          message: `您不是${vipName}，请购买${vipName}后兑换该班级学习。或退出后重新购买班级。`,
          confirmButtonText: '购买会员',
        },
        // 课程会员被删除
        'vip.vip_right_not_exist': {
          message: `很抱歉，该班级已不属于会员权益，请退出后重新购买。`,
          showConfirmButton: false,
        },
      };
      if (vipStatus[code]) {
        this.vipCallConfirm(vipStatus[code]);
        return;
      }
      Toast.fail(this.errorMsg);

      // else if (code === 'vip.member_expired') {
      //   errorMessage = '会员已到期，请及时续费会员';
      //   confirmCallback = () => {
      //     this.$router.push({
      //       path: `/vip`,
      //     });
      //   };
      //   this.callConfirm(errorMessage, confirmCallback);
      // } else {
      //   Toast.fail(this.getErrorMsg(code));
      // }
    },
    handleScroll() {
      if (this.scrollFlag) {
        return;
      }
      this.scrollFlag = true;
      const refs = this.$refs;

      // 滚动节流
      setTimeout(() => {
        this.headBottom = refs.head.$el.getBoundingClientRect().bottom;
        this.scrollFlag = false;
        this.tabsClass = this.headBottom <= 0 ? 'van-tabs--fixed' : '';
      }, 400);
    },
    getErrorMsg(code) {
      switch (code) {
        case 'classroom.not_found':
          return '当前班级不存在';
        case 'classroom.unpublished':
          return '当前班级未发布';
        case 'classroom.expired':
          return '当前班级已过期';
        case 'user.not_login':
          return '用户未登录';
        case 'user.locked':
          return '用户被锁定';
        case 'member.not_found':
          return '用户未加入班级';
        case 'member.auditor':
          return '用户是旁听生，请前往商品页购买';
        case 'member.expired':
          return '班级已过期';
        case 'vip.vip_closed':
          return '网校已关闭会员功能';
        case 'vip.not_login':
          return '用户未登录';
        case 'vip.not_member':
          return '当前用户并不是vip';
        case 'vip.member_expired':
          return '用户会员服务已过期';
        case 'vip.level_not_exist':
          return '用户会员等级或班级会员不存在';
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
        cancelButtonText: '退出班级',
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
      } else {
        if (!this.show) return;
        const params = { id: this.details.classId };
        Api.deleteClassroom({ query: params }).then(res => {
          if (res.success) {
            this.replaceGoodsPage();
            done();
          }
        });
      }
    },

    onCancelForm() {
      this.setCurrentJoin(false);
      this.isShowForm = false;
    },
    getGoodSettings() {
      Api.getSettings({
        query: {
          type: 'goods',
        },
      }).then(res => {
        this.showNumberData = res.show_number_data;
      });
    },
  },
};
</script>
