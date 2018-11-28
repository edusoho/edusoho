<template>
  <div class="e-review">
    <img class="e-review-avatar avatar-img" :src="review.user | avatar" alt="">
    <div class="e-review-body">
      <div>
        <span class="e-review-nickname text-14">{{ review.user | userName }}</span>
        <van-rate class="e-review-rating" v-model="emptyRating" :size="15" :count="emptyRating"
          color="#B0BDC9" :readonly="true"></van-rate>
        <van-rate class="e-review-rating" v-model="rating" :size="15" :count="rating"
          color="#FFAA00" :readonly="true"></van-rate>
      </div>
      <div class="e-review-time text-12">{{ review.createdTime | time(timeFormat) }}</div>

      <div v-if="disableMask" class="e-review-content text-14">{{ review.content }}</div>
      <more-mask v-else :text="textOption" :maxHeight="100" :disabled="loadAllReview"
        @maskLoadMore="loadAllReview = true">
        <div class="e-review-content text-14">{{ review.content }}</div>
      </more-mask>

      <div class="e-review__post" v-for="post in posts">
        <img class="e-review-avatar e-review__post-avatar avatar-img" :src="post.user | avatar" alt="">
        <div class="e-review__post-body">
          <span class="e-review-nickname text-14">{{ post.user | userName }} 回复 {{ review.user | userName }}</span>
          <div class="e-review-content text-14">{{ post.content }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moreMask from '@/components/more-mask';
import { formatSimpleTime, formatCompleteTime } from '@/utils/date-toolkit.js';

export default {
  name: 'review',
  props: {
    review: {
      type: Object,
      default: {}
    },
    disableMask: {
      type: Boolean,
      default: true,
    },
    isClass: {
      type: Boolean,
      default: true,
    },
    timeFormat: {
      type: String,
      default: 'simple'
    }
  },
  data() {
    return {
      loadAllReview: false,
    };
  },
  components: {
    moreMask,
  },
  filters: {
    userName(user) {
      return user && user.nickname || '匿名用户';
    },
    avatar(user) {
      return user && user.avatar && user.avatar.middle;
    },
    time(time, timeFormat) {
      const date = new Date(time);
      if (timeFormat === 'simple') {
        return formatSimpleTime(date);
      }
      if (timeFormat === 'complete') {
        return formatCompleteTime(date)
      }
      return time;
    }
  },
  computed: {
    posts() {
      return this.review && this.review.posts || [];
    },
    rating: {
      get() {
        return Number(this.review.rating);
      },
      set() {},
    },
    emptyRating: {
      get() {
        return this.rating >= 0 ? Number(5 - this.rating) : 5
      },
      set() {},
    },
    textOption() {
      return { paddingTop:70, lineHeight:25, align: 'right', content: '显示全部' };
    }
  }
}
</script>

