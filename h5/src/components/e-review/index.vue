<template>
  <div class="e-review">
    <img :src="review.user | avatar" class="e-review-avatar avatar-img" alt="">
    <div class="e-review-body">
      <div>
        <span class="e-review-nickname text-14">{{ review.user | userName }}</span>
        <van-rate
          v-model="emptyRating"
          :size="15"
          :count="emptyRating"
          :readonly="true"
          class="e-review-rating"
          color="#B0BDC9"/>
        <van-rate
          v-model="rating"
          :size="15"
          :count="rating"
          :readonly="true"
          class="e-review-rating"
          color="#FFAA00"/>
      </div>
      <div class="e-review-time text-12">{{ review.createdTime | time(timeFormat) }}</div>

      <div v-if="disableMask" class="e-review-content text-14">{{ review.content }}</div>
      <more-mask
        v-else
        :text="textOption"
        :max-height="100"
        :disabled="loadAllReview"
        @maskLoadMore="loadAllReview = true">
        <div class="e-review-content text-14">{{ review.content }}</div>
      </more-mask>

      <span v-if="courseTitle" class="e-review-origin text-12">来自：{{ courseTitle }}</span>

      <div v-for="post in posts" class="e-review__post">
        <img :src="post.user | avatar" class="e-review-avatar e-review__post-avatar avatar-img" alt="">
        <div class="e-review__post-body">
          <span class="e-review-nickname text-14">{{ post.user | userName }} 回复 {{ review.user | userName }}：</span>
          <div class="e-review-content text-14">{{ post.content }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moreMask from '@/components/more-mask'
import { formatSimpleTime, formatCompleteTime } from '@/utils/date-toolkit.js'

export default {
  name: 'Review',
  components: {
    moreMask
  },
  filters: {
    userName(user) {
      return user && user.nickname || '匿名用户'
    },
    avatar(user) {
      return user && user.avatar && user.avatar.middle
    },
    time(time, timeFormat) {
      const date = new Date(time)
      if (timeFormat === 'simple') {
        return formatSimpleTime(date)
      }
      if (timeFormat === 'complete') {
        return formatCompleteTime(date)
      }
      return time
    }
  },
  props: {
    review: {
      type: Object,
      default: () => {
        return {}
      }
    },
    course: {
      type: Object,
      default: () => {
        return {}
      }
    },
    disableMask: {
      type: Boolean,
      default: true
    },
    isClass: {
      type: Boolean,
      default: true
    },
    timeFormat: {
      type: String,
      default: 'simple'
    }
  },
  data() {
    return {
      loadAllReview: false
    }
  },
  computed: {
    posts() {
      return this.review && this.review.posts || []
    },
    rating: {
      get() {
        return Number(this.review.rating)
      },
      set() {}
    },
    emptyRating: {
      get() {
        return this.rating >= 0 ? Number(5 - this.rating) : 5
      },
      set() {}
    },
    textOption() {
      return { paddingTop: 70, lineHeight: 25, align: 'right', content: '显示全部' }
    },
    courseTitle() {
      return this.isClass ? '' : this.course.displayedTitle
    }
  }
}
</script>

