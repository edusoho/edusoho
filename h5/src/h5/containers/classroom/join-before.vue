<template>
  <div class="course-detail classroom-detail">
    <div class="join-before">
      <detail-head
        :cover="details.cover"
        :price="planDetails.price"
        :classroom-id="details.classId"
        :seckill-activities="marketingActivities.seckill"
        @goodsEmpty="sellOut"/>

      <detail-plan
        :details="planDetails"
        :join-status="details.joinStatus"
        @getLearnExpiry="getLearnExpiry"/>
      <div class="segmentation"/>

      <!-- 优惠活动 -->
      <template v-if="showOnsale" >
        <onsale
          :unreceived-coupons="unreceivedCoupons"
          :mini-coupons="miniCoupons"
          :activities="marketingActivities"/>
        <div class="segmentation"/>
      </template>

      <van-tabs v-model="active" :class="tabsClass" @click="onTabClick">
        <van-tab v-for="item in tabs" :title="item" :key="item"/>
      </van-tabs>

      <!-- 班级介绍 -->
      <e-panel ref="about" title="班级介绍" class="about">
        <more-mask :disabled="loadMoreAbout" @maskLoadMore="loadMoreAbout = true">
          <div v-html="details.summary"/>
        </more-mask>
      </e-panel>
      <div class="segmentation"/>

      <!-- 教师介绍 -->
      <teacher
        :teacher-info="details.teachers"
        class="teacher"
        title="教师介绍"/>
      <div class="segmentation"/>

      <teacher
        :teacher-info="details.headTeacher ? [details.headTeacher] : []"
        class="teacher"
        title="班主任"
        defaul-value="尚未设置班主任"/>
      <div class="segmentation"/>

      <!-- 班级课程 -->
      <course-set-list ref="course" :course-sets="details.courses" :disable-mask.sync="disableMask" title="班级课程" defaul-value="暂无课程"/>
      <div class="segmentation"/>

      <!-- 学员评价 -->
      <review-list ref="review" :target-id="details.classId" :reviews="details.reviews" title="学员评价" type="classroom" defaul-value="暂无评价"/>

      <!-- 加入学习 -->
      <e-footer v-if="!marketingActivities.seckill || (marketingActivities.seckill && (isEmpty || seckillStatus === '已到期')) || planDetails.price == 0" :disabled="!accessToJoin" @click.native="handleJoin">
        {{ details.access.code | filterJoinStatus('classroom', vipAccessToJoin) }}</e-footer>
      <!-- 秒杀 -->
      <div v-if="!!showSeckill && seckillStatus !== '已到期'" >
        <e-footer :disabled="!accessToJoin" half="true" @click.native="handleJoin">{{ details.access.code | filterJoinStatus('classroom', vipAccessToJoin) }}</e-footer>
        <e-footer half="true" @click.native="activityHandle(marketingActivities.seckill.id)">去秒杀</e-footer>
      </div>
    </div>

  </div>
</template>

<script>
import teacher from './teacher'
import detailHead from './head'
import reviewList from './review-list'
import courseSetList from './course-set-list'
import detailPlan from './plan'
import directory from '../course/detail/directory'
import onsale from '../course/detail/onsale'
import moreMask from '@/components/more-mask'
import redirectMixin from '@/mixins/saveRedirect'
import { mapState } from 'vuex'
import Api from '@/api'
import getCouponMixin from '@/mixins/coupon/getCouponHandler'
import getActivityMixin from '@/mixins/activity/index'
import { dateTimeDown } from '@/utils/date-toolkit'

const TAB_HEIGHT = 44

export default {
  components: {
    directory,
    detailHead,
    detailPlan,
    teacher,
    courseSetList,
    reviewList,
    moreMask,
    onsale
  },
  mixins: [redirectMixin, getCouponMixin, getActivityMixin],
  props: ['details', 'planDetails'],
  data() {
    return {
      tops: {
        aboutTop: 0,
        courseTop: 0,
        reviewTop: 0
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
      isEmpty: true
    }
  },
  computed: {
    ...mapState(['couponSwitch', 'user']),
    accessToJoin() {
      return this.details.access.code === 'success' ||
          this.details.access.code === 'user.not_login'
    },
    vipAccessToJoin() {
      let vipAccess = false
      if (!this.details.vipLevel || !this.user.vip) {
        return false
      }
      if (this.details.vipLevel.seq <= this.user.vip.seq) {
        const vipExpired = new Date(this.user.vip.deadline).getTime() < new Date().getTime()
        vipAccess = !vipExpired
      }
      return vipAccess
    },
    showOnsale() {
      return Number(this.planDetails.price) !== 0 &&
          (!!(this.unreceivedCoupons.length ||
            Object.keys(this.marketingActivities).length &&
            !this.onlySeckill))
    },
    onlySeckill() {
      return Object.keys(this.marketingActivities).length === 1 &&
          this.marketingActivities.seckill
    },
    seckillStatus() {
      const seckillData = this.marketingActivities.seckill
      const endTime = dateTimeDown(new Date(seckillData.endTime).getTime())
      return endTime
    },
    showSeckill() {
      const seckillData = this.marketingActivities.seckill
      return Number(this.planDetails.price) !== 0 &&
          seckillData && seckillData.status === 'ongoing' && !this.isEmpty
    }
  },
  mounted() {
    // 获取促销优惠券
    if (this.couponSwitch) {
      Api.searchCoupon({
        params: {
          targetId: this.details.classId,
          targetType: 'classroom'
        }
      }).then(res => {
        this.unreceivedCoupons = res.data

        this.miniCoupons = this.unreceivedCoupons.length > 3
          ? this.unreceivedCoupons.slice(0, 4) : this.unreceivedCoupons
      }).catch(err => {
        console.error(err)
      })
    }
    // 获取营销活动
    Api.classroomsActivities({
      query: { id: this.details.classId }
    }).then(res => {
      this.marketingActivities = res
      this.isEmpty = res.seckill ? !+res.seckill.productRemaind : true
    }).catch(err => {
      console.error(err)
    })

    window.addEventListener('touchmove', this.handleScroll)
    window.addEventListener('scroll', this.handleScroll)
    setTimeout(() => {
      window.scrollTo(0, 0)
    }, 100)
  },
  destroyed() {
    window.removeEventListener('touchmove', this.handleScroll)
    window.removeEventListener('scroll', this.handleScroll)
  },
  methods: {
    onTabClick(index, title) {
      const ref = this.$refs[this.transIndex2Tab(index)]
      window.scrollTo(0, ref.$el.offsetTop - TAB_HEIGHT)
    },
    transIndex2Tab(index) {
      const tabs = ['about', 'course', 'review']
      return tabs[index]
    },
    handleScroll() {
      if (this.scrollFlag) {
        return
      }
      this.scrollFlag = true
      const refs = this.$refs
      const tabs = ['about', 'course', 'review'].reverse()

      // 滚动节流
      setTimeout(() => {
        Object.keys(refs).forEach(item => {
          this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top
        })
        this.scrollFlag = false
        this.tabsClass = this.tops.aboutTop - TAB_HEIGHT <= 0 ? 'van-tabs--fixed' : ''

        for (let index = 0; index < tabs.length; index++) {
          const activeCondition = this.tops[`${tabs[index]}Top`] - TAB_HEIGHT <= 0
          if (!activeCondition) {
            continue
          }
          this.active = tabs.length - index - 1
          return
        }
      }, 400)
    },
    handleJoin() {
      // 会员免费学
      const vipAccessToJoin = this.vipAccessToJoin

      // 禁止加入
      if (!this.accessToJoin && !vipAccessToJoin) {
        return
      }

      const details = this.details
      const planDetails = this.planDetails
      const canJoinIn = Number(details.buyable) === 1 ||
          (+planDetails.price) === 0 || vipAccessToJoin

      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect
          }
        })
        return
      }

      if (!canJoinIn) return

      if (+planDetails.price && !vipAccessToJoin) {
        this.$router.push({
          name: 'order',
          params: {
            id: details.classId
          },
          query: {
            expiryScope: this.learnExpiry,
            targetType: 'classroom'
          }
        })
        return
      }

      Api.joinClass({
        query: {
          classroomId: details.classId
        }
      }).then(res => {
        this.details.joinStatus = res
      }).catch(err => {
        console.error(err.message)
      })
    },
    getLearnExpiry({ val }) {
      this.learnExpiry = val
    },
    sellOut() {
      this.isEmpty = true
    }
  }
}
</script>
