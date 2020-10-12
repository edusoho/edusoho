<template>
  <div class="course-detail classroom-detail">
    <div class="join-before">
      <detail-head
        :cover="details.cover"
        :price="planDetails.price"
        :classroom-id="details.classId"
        :seckill-activities="marketingActivities.seckill"
        @goodsEmpty="sellOut"
      />

      <detail-plan
        :details="planDetails"
        :join-status="details.joinStatus"
        @getLearnExpiry="getLearnExpiry"
      />
      <div class="segmentation" />

      <!-- 优惠活动 -->
      <template v-if="showOnsale">
        <onsale
          :unreceived-coupons="unreceivedCoupons"
          :mini-coupons="miniCoupons"
          :activities="marketingActivities"
        />
        <div class="segmentation" />
      </template>

      <van-tabs v-model="active" :class="tabsClass" @click="onTabClick">
        <van-tab v-for="item in tabs" :title="item" :key="item" />
      </van-tabs>

      <!-- 班级介绍 -->
      <e-panel ref="about" title="班级介绍" class="about">
        <more-mask
          :disabled="loadMoreAbout"
          @maskLoadMore="loadMoreAbout = true"
        >
          <div v-html="details.summary" />
        </more-mask>
      </e-panel>
      <div class="segmentation" />

      <!-- 教师介绍 -->
      <teacher
        :teacher-info="details.teachers"
        class="teacher"
        title="教师介绍"
      />
      <div class="segmentation" />

      <teacher
        :teacher-info="details.headTeacher ? [details.headTeacher] : []"
        class="teacher"
        title="班主任"
        defaul-value="尚未设置班主任"
      />
      <div class="segmentation" />

      <!-- 班级课程 -->
      <course-set-list
        ref="course"
        :course-sets="details.courses"
        :disable-mask.sync="disableMask"
        title="班级课程"
        defaul-value="暂无课程"
      />
      <div class="segmentation" />

      <!-- 学员评价 -->
      <review-list
        ref="review"
        :target-id="details.classId"
        :reviews="classroomSettings.show_review == 1 ? details.reviews : []"
        title="学员评价"
        type="classroom"
        defaul-value="暂无评价"
      />

      <!-- 个人信息表单填写 -->
      <van-action-sheet
        v-model="isShowForm"
        class="minHeight50"
        :title="userInfoCollectForm.formTitle"
        :close-on-click-overlay="false"
        :safe-area-inset-bottom="true"
      >
        <info-collection
          :userInfoCollectForm="this.userInfoCollectForm"
          :formRule="this.userInfoCollectForm.items"
          @submitForm="joinFreeClass"
        ></info-collection>
      </van-action-sheet>
      <!-- 加入学习 -->
      <e-footer
        v-if="
          !marketingActivities.seckill ||
            (marketingActivities.seckill &&
              (isEmpty || seckillStatus === '已到期')) ||
            planDetails.price == 0
        "
        :disabled="!accessToJoin"
        @click.native="handleJoin"
      >
        {{
          details.access.code | filterJoinStatus('classroom', vipAccessToJoin)
        }}</e-footer
      >
      <!-- 秒杀 -->
      <div v-if="!!showSeckill && seckillStatus !== '已到期'">
        <e-footer
          :disabled="!accessToJoin"
          half="true"
          @click.native="handleJoin"
          >{{
            details.access.code | filterJoinStatus('classroom', vipAccessToJoin)
          }}</e-footer
        >
        <e-footer
          half="true"
          @click.native="activityHandle(marketingActivities.seckill.id)"
          >去秒杀</e-footer
        >
      </div>
    </div>
  </div>
</template>

<script>
import teacher from './teacher';
import detailHead from './head';
import reviewList from './review-list';
import courseSetList from './course-set-list';
import detailPlan from './plan';
import directory from '../course/detail/directory';
import onsale from '../course/detail/onsale';
import moreMask from '@/components/more-mask';
import redirectMixin from '@/mixins/saveRedirect';
import { mapState, mapMutations } from 'vuex';
import Api from '@/api';
import collectUserInfo from '@/mixins/collectUserInfo';
import getCouponMixin from '@/mixins/coupon/getCouponHandler';
import getActivityMixin from '@/mixins/activity/index';
import { dateTimeDown } from '@/utils/date-toolkit';
import { Toast } from 'vant';
import * as types from '@/store/mutation-types';
import infoCollection from '@/components/info-collection.vue';

const TAB_HEIGHT = 44;

export default {
  components: {
    // eslint-disable-next-line vue/no-unused-components
    directory,
    detailHead,
    detailPlan,
    teacher,
    courseSetList,
    reviewList,
    moreMask,
    onsale,
    infoCollection,
  },
  mixins: [redirectMixin, getCouponMixin, getActivityMixin, collectUserInfo],
  props: ['details', 'planDetails'],
  data() {
    return {
      tops: {
        aboutTop: 0,
        courseTop: 0,
        reviewTop: 0,
      },
      active: 0,
      scrollFlag: false,
      tabs: ['班级介绍', '班级课程', '学员评价'],
      tabsClass: '',
      loadMoreAbout: false,
      disableMask: false,
      learnExpiry: '长期有效',
      unreceivedCoupons: [],
      miniCoupons: [],
      marketingActivities: {
        seckill: {},
      },
      isEmpty: true,
      scrollTime: null,
      isManualSwitch: false,
      classroomSettings: {},
      isShowForm: false,
    };
  },
  watch: {
    $route(to, from) {
      this.resetFrom();
    },
  },
  computed: {
    ...mapState(['couponSwitch', 'user']),
    accessToJoin() {
      return (
        this.details.access.code === 'success' ||
        this.details.access.code === 'user.not_login'
      );
    },
    vipAccessToJoin() {
      let vipAccess = false;
      if (!this.details.vipLevel || !this.user.vip) {
        return false;
      }
      if (this.details.vipLevel.seq <= this.user.vip.seq) {
        const vipExpired =
          new Date(this.user.vip.deadline).getTime() < new Date().getTime();
        vipAccess = !vipExpired;
      }
      return vipAccess;
    },
    showOnsale() {
      return (
        Number(this.planDetails.price) !== 0 &&
        !!(
          this.unreceivedCoupons.length ||
          (Object.keys(this.marketingActivities).length && !this.onlySeckill)
        )
      );
    },
    onlySeckill() {
      return (
        Object.keys(this.marketingActivities).length === 1 &&
        this.marketingActivities.seckill
      );
    },
    seckillStatus() {
      const seckillData = this.marketingActivities.seckill;
      const endTime = dateTimeDown(new Date(seckillData.endTime).getTime());
      return endTime;
    },
    showSeckill() {
      const seckillData = this.marketingActivities.seckill;
      return (
        Number(this.planDetails.price) !== 0 &&
        seckillData &&
        seckillData.status === 'ongoing' &&
        !this.isEmpty
      );
    },
  },
  async created() {
    this.classroomSettings = await Api.getSettings({
      query: {
        type: 'classroom',
      },
    }).catch(err => {
      console.error(err);
    });
  },
  mounted() {
    if (this.couponSwitch) {
      Api.searchCoupon({
        params: {
          targetId: this.details.classId,
          targetType: 'classroom',
        },
      })
        .then(res => {
          this.unreceivedCoupons = res.data;

          this.miniCoupons =
            this.unreceivedCoupons.length > 3
              ? this.unreceivedCoupons.slice(0, 4)
              : this.unreceivedCoupons;
        })
        .catch(err => {
          console.error(err);
        });
    }
    // 获取营销活动
    Api.classroomsActivities({
      query: { id: this.details.classId },
    })
      .then(res => {
        this.marketingActivities = res;
        this.isEmpty = res.seckill ? !+res.seckill.productRemaind : true;
      })
      .catch(err => {
        console.error(err);
      });

    window.addEventListener('touchmove', this.handleScroll);
    window.addEventListener('scroll', this.handleScroll);
    setTimeout(() => {
      window.scrollTo(0, 0);
    }, 100);
  },
  destroyed() {
    window.removeEventListener('touchmove', this.handleScroll);
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    ...mapMutations('classroom', {
      setCurrentJoinClass: types.SET_CURRENT_JOIN_CLASS,
    }),
    onTabClick(index, title) {
      this.isManualSwitch = true;

      const ref = this.$refs[this.transIndex2Tab(index)];
      window.scrollTo(0, ref.$el.offsetTop - TAB_HEIGHT);

      setTimeout(() => (this.isManualSwitch = false), 500);
    },
    transIndex2Tab(index) {
      const tabs = ['about', 'course', 'review'];
      return tabs[index];
    },
    handleScroll() {
      if (this.scrollFlag) {
        return;
      }
      this.scrollFlag = true;
      const refs = this.$refs;
      const tabs = ['about', 'course', 'review'].reverse();

      // 滚动节流
      if (this.scrollTime) clearTimeout(this.scrollTime);

      this.scrollTime = setTimeout(() => {
        Object.keys(refs).forEach(item => {
          this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top;
        });
        this.scrollFlag = false;
        this.tabsClass =
          this.tops.aboutTop - TAB_HEIGHT <= 0 ? 'van-tabs--fixed' : '';
        this.setCurrentActive(tabs);
      }, 400);
    },

    setCurrentActive(tabs) {
      if (this.isManualSwitch) return;

      for (let index = 0; index < tabs.length; index++) {
        if (this.tops[`${tabs[index]}Top`] - TAB_HEIGHT > 0) {
          continue;
        }

        this.active = tabs.length - index - 1;
        return;
      }

      this.active = 0;
    },

    handleJoin() {
      // 会员免费学
      const vipAccessToJoin = this.vipAccessToJoin;

      // 禁止加入
      if (!this.accessToJoin && !vipAccessToJoin) {
        return;
      }

      const details = this.details;
      const planDetails = this.planDetails;
      const canJoinIn =
        Number(details.buyable) === 1 ||
        +planDetails.price === 0 ||
        vipAccessToJoin;

      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          },
        });
        return;
      }

      if (!canJoinIn) return;

      if (+planDetails.price && !vipAccessToJoin) {
        this.$router.push({
          name: 'order',
          params: {
            id: details.classId,
          },
          query: {
            expiryScope: this.learnExpiry,
            targetType: 'classroom',
          },
        });
        return;
      }
      this.collectUseInfoEvent();
    },
    joinFreeClass() {
      Api.joinClass({
        query: {
          classroomId: this.details.classId,
        },
      })
        .then(res => {
          this.setCurrentJoinClass(true);
          Toast.clear();
          this.details.joinStatus = res;
        })
        .catch(err => {
          console.error(err.message);
        });
    },
    getLearnExpiry({ val }) {
      this.learnExpiry = val;
    },
    sellOut() {
      this.isEmpty = true;
    },
    getParamsList() {
      this.paramsList = {
        action: 'buy_before',
        targetType: 'classroom',
        targetId: this.details.classId,
      };
    },
    collectUseInfoEvent() {
      if (this.hasUserInfoCollectForm) {
        this.isShowForm = true;
        return;
      }
      Toast.loading({
        duration: 0,
        message: '加载中...',
        forbidClick: true,
      });
      this.getParamsList();
      this.getInfoCollectionEvent(this.paramsList).then(res => {
        if (Object.keys(res).length) {
          this.userInfoCollect = res;
          this.getInfoCollectionForm(res.id).then(res => {
            this.isShowForm = true;
            Toast.clear();
          });
          return;
        }
        this.joinFreeClass();
      });
    },
  },
};
</script>
