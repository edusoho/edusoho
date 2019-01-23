<template>
  <div class="course-detail classroom-detail">
    <div class="join-before">
      <detail-head
        :cover="details.cover"
        @goodsEmpty="sellOut"
        :seckillData="seckillData"
        :seckillActivities="marketingActivities.seckill"></detail-head>

      <detail-plan :details="planDetails" :joinStatus="details.joinStatus"
        @getLearnExpiry="getLearnExpiry"></detail-plan>
      <div class="segmentation"></div>

      <!-- 优惠活动 -->
      <template v-if="showOnsale" >
        <onsale :unreceivedCoupons="unreceivedCoupons" :miniCoupons="miniCoupons"
          :activities="marketingActivities"/>
        <div class="segmentation"></div>
      </template>

      <van-tabs v-model="active" @click="onTabClick" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <e-panel title="班级介绍" ref="about" class="about">
        <more-mask :disabled="loadMoreAbout" @maskLoadMore="loadMoreAbout = true">
          <div v-html="details.summary"></div>
        </more-mask>
      </e-panel>
      <div class="segmentation"></div>

      <!-- 教师介绍 -->
      <teacher
        class="teacher" title="教师介绍"
        :teacherInfo="details.teachers"></teacher>
      <div class="segmentation"></div>

      <teacher
        class="teacher" title="班主任" :teacherInfo="details.headTeacher ? [details.headTeacher] : []"
        defaulValue="尚未设置班主任"></teacher>
      <div class="segmentation"></div>

      <!-- 班级课程 -->
      <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程" :disableMask.sync="disableMask"></course-set-list>
      <div class="segmentation"></div>

      <!-- 学员评价 -->
      <review-list ref="review" :targetId="details.classId" :reviews="details.reviews" title="学员评价" type="classroom" defaulValue="暂无评价"></review-list>

      <!-- 加入学习 -->
      <e-footer v-if="!marketingActivities.seckill" :disabled="!accessToJoin" @click.native="handleJoin">
      {{details.access.code | filterJoinStatus('classroom', vipAccessToJoin)}}</e-footer>
      <!-- 秒杀 -->
      <e-footer v-if="showSeckill" :half="seckillData" @click.native="handleJoin">原价购买</e-footer>
      <e-footer v-if="showSeckill && !isEmpty && seckillData" :half="true" @click.native="activityHandle(marketingActivities.seckill.id)">去秒杀</e-footer>
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
  import onsale from '../course/detail/onsale'
  import moreMask from '@/components/more-mask';
  import redirectMixin from '@/mixins/saveRedirect';
  import { mapState } from 'vuex';
  import Api from '@/api';
  import getCouponMixin from '@/mixins/coupon/getCouponHandler';
  import getActivityMixin from '@/mixins/activity/index';

  const TAB_HEIGHT = 44;

  export default {
    mixins: [redirectMixin, getCouponMixin, getActivityMixin],
    components: {
      directory,
      detailHead,
      detailPlan,
      teacher,
      courseSetList,
      reviewList,
      moreMask,
      onsale,
    },
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
        learnExpiry: '永久有效',
        unreceivedCoupons: [],
        miniCoupons: [],
        marketingActivities: {
          seckill: {}
        },
        isEmpty: false
      }
    },
    computed: {
      ...mapState(['user']),
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
        return Number(this.planDetails.price) !== 0
          && (this.unreceivedCoupons.length
            || Object.keys(this.marketingActivities).length
            && !this.onlySeckill);
      },
      onlySeckill() {
        return Object.keys(this.marketingActivities).length === 1
          && this.marketingActivities.seckill;
      },
      showSeckill() {
        return Number(this.planDetails.price) !== 0
          && this.marketingActivities.seckill && this.accessToJoin;
      },
      seckillData() {
        if (!this.marketingActivities.seckill) return false;
        return !!(Object.values(this.marketingActivities.seckill).length);
      },
    },
    mounted() {
      // 获取促销优惠券
      Api.searchCoupon({
        params: {
          targetId: this.details.classId,
          targetType: 'classroom',
        }
      }).then(res => {
        this.unreceivedCoupons = res.data;

        this.miniCoupons = this.unreceivedCoupons.length > 3 ?
          this.unreceivedCoupons.slice(0, 4) : this.unreceivedCoupons
      }).catch(err => {
        console.error(err);
      });
      // 获取营销活动
      Api.classroomsActivities({
        query: { id: this.details.classId }
      }).then(res => {
        this.marketingActivities = res;
      }).catch(err => {
        console.error(err);
      });

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
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];
        window.scrollTo(0, ref.$el.offsetTop - TAB_HEIGHT);
      },
      transIndex2Tab(index) {
        const tabs = ['about', 'course', 'review']
        return tabs[index];
      },
      handleScroll() {
        if (this.scrollFlag) {
          return;
        }
        this.scrollFlag = true;
        const refs = this.$refs;
        const tabs = ['about', 'course', 'review'].reverse()

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
      handleJoin() {
        // 会员免费学
        const vipAccessToJoin = this.vipAccessToJoin;

        // 禁止加入
        if (!this.accessToJoin && !vipAccessToJoin) {
          return;
        }

        const details = this.details;
        const planDetails = this.planDetails;
        const canJoinIn = Number(details.buyable ) === 1
          || (+planDetails.price) === 0 || vipAccessToJoin;

        if (!this.$store.state.token) {
          this.$router.push({
            name: 'login',
            query: {
              redirect: this.redirect
            }
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
            }
          });
          return;
        }

        Api.joinClass({
          query: {
            classroomId: details.classId
          }
        }).then(res => {
          this.details.joinStatus = res;
        }).catch(err => {
          console.error(err.message);
        });
      },
      getLearnExpiry({val}) {
        this.learnExpiry = val;
      },
      sellOut() {
        this.isEmpty = true;
      }
    },
  }
</script>
