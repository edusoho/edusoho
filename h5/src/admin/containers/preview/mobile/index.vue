<template>
  <div class="find-page">
    <div v-for="(part, index) in parts" :key="index" class="find-page__part">
      <e-swipe
        v-if="part.type == 'slide_show'"
        :slides="part.data"
        :feedback="feedback"/>
      <e-course-list
        v-if="['classroom_list', 'course_list'].includes(part.type)"
        :course-list="part.data"
        :feedback="feedback"
        :type-list="part.type"
        class="gray-border-bottom"/>
      <e-poster
        v-if="part.type == 'poster'"
        :class="imageMode[part.data.responsive]"
        :poster="part.data"
        :feedback="feedback"/>
      <e-market-part
        v-if="['groupon', 'cut', 'seckill'].includes(part.type)"
        :tag="part.data.tag"
        :type="part.type"
        :show-title="part.data.titleShow"
        :activity="part.data.activity"
        class="gray-border-bottom"/>
      <div v-if="part.type == 'coupon'" class="coupon-preview__container gray-border-bottom">
        <e-coupon-list
          :coupons="part.data.items"
          :feedback="true"
          :show-title="part.data.titleShow"/>
      </div>
      <e-vip-list
        v-if="part.type == 'vip'"
        :items="part.data.items"
        :feedback="true"
        :sort="part.data.sort"
        :show-title="part.data.titleShow"
        class="gray-border-bottom"/>
    </div>
    <!-- 垫底的 -->
    <div class="mt50"/>
  </div>
</template>

<script>
import pathName2Portal from 'admin/config/api-portal-config'
import courseList from '&/components/e-course-list/e-course-list.vue'
import poster from '&/components/e-poster/e-poster.vue'
import swipe from '&/components/e-swipe/e-swipe.vue'
import marketPart from '&/components/e-marketing/e-activity'
import coupon from '&/components/e-coupon-list/e-coupon-list'
import vipList from '&/components/e-vip-list/e-vip-list'
import { mapActions } from 'vuex'

export default {
  components: {
    'e-course-list': courseList,
    'e-swipe': swipe,
    'e-poster': poster,
    'e-market-part': marketPart,
    'e-coupon-list': coupon,
    'e-vip-list': vipList
  },
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
      from: this.$route.query.from
    }
  },
  created() {
    this.getDraft({
      portal: pathName2Portal[this.from],
      type: 'discovery',
      mode: 'draft'
    }).then(res => {
      this.parts = Object.values(res)
    }).catch(err => {
      console.log(err, 'error')
    })
  },
  methods: {
    ...mapActions([
      'getDraft'
    ])
  }
}
</script>
