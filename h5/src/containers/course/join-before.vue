<template>
  <div :class="isClassCourse ? '' : 'join-before'">
    <detail-head
      :price="details.price"
      :courseSet="details.courseSet"
      @goodsEmpty="sellOut"
      :seckillActivities="marketingActivities.seckill"></detail-head>

    <detail-plan @getLearnExpiry="getLearnExpiry" @switchPlan="switchPlan"></detail-plan>
    <div class="segmentation"></div>

    <!-- 优惠活动 -->
    <template v-if="showOnsale">
      <onsale :unreceivedCoupons="unreceivedCoupons" :miniCoupons="miniCoupons"
        :activities="marketingActivities"/>
      <div class="segmentation"></div>
    </template>

    <van-tabs v-model="active" @click="onTabClick" :class="tabsClass">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

    <!-- 课程介绍 -->
    <e-panel title="课程介绍" ref="about" class="about">
      <more-mask :disabled="loadMoreAbout" @maskLoadMore="loadMoreAbout = true">
        <div v-html="summary"></div>
      </more-mask>
    </e-panel>
    <div class="segmentation"></div>

    <!-- 教师介绍 -->
    <teacher
      class="teacher" title="教师介绍"
      :teacherInfo="details.teachers"></teacher>
    <div class="segmentation"></div>

    <!-- 课程目录 -->
    <directory ref="directory"></directory>
    <div class="segmentation"></div>

    <!-- 学员评价 -->
    <review-list ref="review" :targetId="details.courseSet.id" :reviews="details.reviews" title="学员评价" type="course" defaulValue="暂无评价"></review-list>

    <!-- 加入学习 -->
    <e-footer v-if="!isClassCourse && !marketingActivities.seckill" :disabled="!accessToJoin" @click.native="handleJoin">
      {{details.access.code | filterJoinStatus('course', vipAccessToJoin)}}</e-footer>
    <!-- 秒杀 -->
    <e-footer v-if="showSeckill" :half="true" @click.native="handleJoin">原价购买</e-footer>
    <e-footer v-if="showSeckill && !isEmpty" :half="true" @click.native="activityHandle(marketingActivities.seckill.id)">去秒杀</e-footer>
  </div>
</template>
<script>
  import moreMask from '@/components/more-mask';
  import reviewList from '@/containers/classroom/review-list';
  import Teacher from './detail/teacher';
  import Directory from './detail/directory';
  import DetailHead from './detail/head';
  import DetailPlan from './detail/plan';
  import onsale from './detail/onsale';
  import { mapActions, mapState } from 'vuex';
  import redirectMixin from '@/mixins/saveRedirect';
  import Api from '@/api';
  import getCouponMixin from '@/mixins/coupon/getCouponHandler';
  import getActivityMixin from '@/mixins/activity/index';

  const TAB_HEIGHT = 44;

  export default {
    name: 'joinBefore',
    mixins: [redirectMixin, getCouponMixin, getActivityMixin],
    data() {
      return {
        tabs: ['课程介绍', '课程目录', '学员评价'],
        loadMoreAbout: false,
        active: 0,
        tabsClass: '',
        learnExpiry: '永久有效',
        startDateStr: '',
        endDateStr: '',
        tops: {
          aboutTop: 0,
          courseTop: 0,
          reviewTop: 0,
        },
        isEmpty: false,
        unreceivedCoupons: [],
        miniCoupons: [],
        marketingActivities: {
          seckill: {}
        },
      };
    },
    components: {
      Teacher,
      Directory,
      DetailHead,
      DetailPlan,
      moreMask,
      reviewList,
      onsale,
    },
    computed: {
      ...mapState('course', {
        details: state => state.details
      }),
      ...mapState(['user']),
      summary () {
        return this.details.summary || this.details.courseSet.summary;
      },
      isClassCourse() {
        return Number(this.details.parentId);
      },
      accessToJoin() {
        return this.details.access.code === 'success'
          || this.details.access.code === 'user.not_login';
      },
      vipAccessToJoin() {
        let vipAccess = false;
        if (!this.details.vipLevel || !this.user.vip) {
          return false;
        }
        if (this.details.vipLevel.seq <= this.user.vip.seq) {
          const vipExpired = new Date(this.user.vip.deadline).getTime() < new Date().getTime();
          vipAccess = !vipExpired;
        }
        return vipAccess;
      },
      showOnsale() {
        return !this.isClassCourse && Number(this.details.price) !== 0
          && (this.unreceivedCoupons.length
            || Object.keys(this.marketingActivities).length
            && !this.onlySeckill);
      },
      onlySeckill() {
        return Object.keys(this.marketingActivities).length === 1
          && this.marketingActivities.seckill;
      },
      showSeckill() {
        return !this.isClassCourse && Number(this.details.price) !== 0
          && this.marketingActivities.seckill && this.accessToJoin;
      }
    },
    mounted() {
      if (!this.isClassCourse) {
        // 获取促销优惠券
        Api.searchCoupon({
          params: {
            targetId: this.details.courseSet.id,
            targetType: 'course',
          }
        }).then(res => {
          this.unreceivedCoupons = res.data

          this.miniCoupons = this.unreceivedCoupons.length > 3 ?
            this.unreceivedCoupons.slice(0, 4) : this.unreceivedCoupons
        }).catch(err => {
          console.error(err);
        });
        // 获取营销活动
        Api.coursesActivities({
          query: { id: this.details.id }
        }).then(res => {
          this.marketingActivities = res;
        }).catch(err => {
          console.error(err);
        });
      }

      window.addEventListener('touchmove', this.handleScroll);
      window.addEventListener('scroll', this.handleScroll);
      setTimeout(() => {
        window.scrollTo(0,0);
      }, 100)
    },
    destroyed () {
      window.removeEventListener('touchmove', this.handleScroll);
      window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
       ...mapActions('course', [
        'joinCourse'
       ]),
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];
        window.scrollTo(0, ref.$el.offsetTop - TAB_HEIGHT);
      },
      transIndex2Tab(index) {
        const tabs = ['about', 'directory', 'review']
        return tabs[index];
      },
      handleScroll() {
        if (this.scrollFlag) {
          return;
        }
        this.scrollFlag = true;
        const refs = this.$refs;
        const tabs = ['about', 'directory', 'review'].reverse()

        // 滚动节流
        setTimeout(() => {
          Object.keys(refs).forEach(item => {
            this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top
          })
          this.scrollFlag = false;
          this.tabsClass = this.tops.aboutTop - TAB_HEIGHT <= 0 ? 'van-tabs--fixed' : '';

          for (let index = 0; index < tabs.length; index++) {
            const activeCondition = this.tops[`${tabs[index]}Top`] - TAB_HEIGHT <= 0
            if (!activeCondition) {
              continue;
            }
            this.active = tabs.length - index - 1;
            return;
          }
        }, 400)
      },
      activeCurrentTab(scrollTop) {
        const tops = this.tops;

        scrollTop  = scrollTop + 44;

        return (scrollTop < tops.teacherTop) ? 0
          :(scrollTop >= tops.directoryTop ? 2 : 1);
      },
      handleJoin(){
        // 会员免费学
        const vipAccessToJoin = this.vipAccessToJoin;

        // 禁止加入
        if (!this.accessToJoin && !vipAccessToJoin) {
          return;
        }

        if (!this.$store.state.token) {
          this.$router.push({
            name: 'prelogin',
            query: {
              redirect: this.redirect,
              doLogin: 1
            }
          });
          return;
        }

        const endDate = this.details.learningExpiryDate.expiryEndDate;
        const endDateStamp = new Date(endDate).getTime();
        const todayStamp = new Date().getTime();
        let isPast = todayStamp < endDateStamp;
        endDate == 0 ? (isPast = true) : (isPast = todayStamp < endDateStamp);
        if (Number(this.details.buyable) && isPast || vipAccessToJoin) {
          if (+this.details.price && !vipAccessToJoin) {
            this.getOrder();
          } else {
            this.joinCourse({
              id: this.details.id
            }).then(res => {
              // 返回空对象，表示加入失败，需要去创建订单购买
              if (!(Object.keys(res).length === 0)) return;
              this.getOrder();
            }).catch(err => {
              console.error(err)
            });
          }
        }
      },
      getLearnExpiry(data) {
        this.learnExpiry = data.val;
        this.startDateStr = data.startDateStr;
        this.endDateStr = data.endDateStr;
      },
      // 切换计划
      switchPlan() {
        // 获取营销活动
        this.marketingActivities = {};
        Api.coursesActivities({
          query: { id: this.details.id }
        }).then(res => {
          this.marketingActivities = res;
        }).catch(err => {
          console.error(err);
        });
      },
      // 创建订单
      getOrder() {
        const expiryMode = this.details.learningExpiryDate.expiryMode;
        const expiryScopeStr = `${this.startDateStr} 至 ${this.endDateStr}`;
        const expiryStr = (expiryMode === 'date') ? expiryScopeStr : this.learnExpiry
        this.$router.push({
          name: 'order',
          params: {
            id: this.details.id,
          },
          query: {
            expiryScope: expiryStr,
            targetType: 'course',
          }
        });
      },
      sellOut() {
        this.isEmpty = true;
      }
    },
  }
</script>
