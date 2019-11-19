<template>
  <div class="find-page">
    <e-loading v-if="isLoading"/>
    <div v-for="(part, index) in parts" :key="index" class="find-page__part">
      <!-- 尝试下jsx重构代码 -->
      <e-swipe v-if="part.type === 'slide_show'" :slides="part.data"/>
      <e-course-list
        v-if="['classroom_list', 'course_list'].includes(part.type) && part.data.items.length"
        :course-list="part.data"
        :type-list="part.type"
        :feedback="feedback"
        :vip-tag-show="true"
        :index="index"
        class="gray-border-bottom"
        @fetchCourse="fetchCourse"/>
      <e-poster
        v-if="part.type === 'poster'"
        :class="imageMode[part.data.responsive]"
        :poster="part.data"
        :feedback="feedback"/>
      <e-coupon-list
        v-if="part.type === 'coupon' && couponSwitch && part.data.items && part.data.items.length"
        :coupons="part.data.items"
        :show-title="part.data.titleShow"
        :feedback="feedback"
        class="gray-border-bottom"
        @couponHandle="couponHandle($event)"/>
      <e-vip-list
        v-if="part.type === 'vip' && vipSwitch && part.data.items && part.data.items.length"
        :items="part.data.items"
        :show-title="part.data.titleShow"
        :sort="part.data.sort"
        :feedback="feedback"
        class="gray-border-bottom"/>
      <e-market-part
        v-if="['groupon', 'cut', 'seckill'].includes(part.type)"
        :activity="part.data.activity"
        :show-title="part.data.titleShow"
        :type="part.type"
        :tag="part.data.tag"
        :feedback="feedback"
        class="gray-border-bottom"
        @activityHandle="activityHandle"/>
    </div>
    <e-switch-loading v-if="wechatSwitch && showFlag" :close-date="closeDate"/>
  </div>
</template>

<script>
import courseList from '&/components/e-course-list/e-course-list.vue'
import poster from '&/components/e-poster/e-poster.vue'
import marketPart from '&/components/e-marketing/e-activity/index.vue'
import swipe from '&/components/e-swipe/e-swipe.vue'
import couponList from '&/components/e-coupon-list/e-coupon-list.vue'
import swithLoading from '&/components/e-switch-loading/index.vue'
import vipList from '&/components/e-vip-list/e-vip-list.vue'
// eslint-disable-next-line no-unused-vars
import * as types from '@/store/mutation-types'
import getCouponMixin from '@/mixins/coupon/getCouponHandler'
import activityMixin from '@/mixins/activity/index'
import Api from '@/api'
import { mapState } from 'vuex'
import { Toast } from 'vant'

export default {
  components: {
    'e-course-list': courseList,
    'e-swipe': swipe,
    'e-poster': poster,
    'e-coupon-list': couponList,
    'e-vip-list': vipList,
    'e-market-part': marketPart,
    'e-switch-loading': swithLoading
  },
  mixins: [getCouponMixin, activityMixin],
  props: {
    feedback: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      parts: [],
      imageMode: [
        'responsive',
        'size-fit'
      ],
      showFlag: true,
      closeDate: 'closedDate'
    }
  },
  computed: {
    ...mapState(['vipSwitch', 'isLoading', 'wechatSwitch', 'couponSwitch'])
  },
  created() {
    const { preview, token } = this.$route.query

    if (preview == 1) {
      Api.discoveries({
        params: {
          mode: 'draft',
          preview: 1,
          token
        }
      })
        .then((res) => {
          this.parts = Object.values(res)
        })
        .catch((err) => {
          Toast.fail(err.message)
        })
      return
    }

    Api.discoveries()
      .then((res) => {
        this.parts = Object.values(res)
      })
      .catch((err) => {
        Toast.fail(err.message)
      })

    // 判断token有无
    if (!this.$store.state.token) {
      this.showFlag = false
    } else {
      const userId = JSON.parse(localStorage.getItem('user')).id
      this.closeDate = `closedDate-${userId}`
      const now = new Date()
      const today = `${now.getFullYear()}-${now.getMonth() + 1}-${now.getDate()}`
      // 判断用户当天是否手动触发过关闭
      this.showFlag = localStorage.getItem(this.closeDate) !== today
    }
  },
  methods: {
    fetchCourse({ params, index, typeList }) {
      if (typeList === 'classroom_list') {
        Api.getClassList({ params }).then(res => {
          if (this.sourceType === 'custom') return

          this.parts[index].data.items = res.data
        })
        return
      }
      Api.getCourseList({ params }).then(res => {
        if (this.sourceType === 'custom') return

        this.parts[index].data.items = res.data
      })
    }
  }
}
</script>
