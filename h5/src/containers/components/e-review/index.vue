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
      <div class="e-review-time text-12">{{ review.createdTime | time }}</div>

      <div class="e-review-content text-14">{{ review.content }}</div>

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
import { formatSimpleTime } from '@/utils/date-toolkit.js';

export default {
  name: 'review',
  props: {
    review: {
      type: Object,
      default: {}
    }
  },
  filters: {
    userName(user) {
      try {
        return user.nickname;
      } catch (e) {
        console.error('userName', e)
        return '匿名用户';
      }
    },
    avatar(user) {
      try {
        return user.avatar.middle;
      } catch (e) {
        console.error('avatar', e)
        return '';
      }
    },
    time(time) {
      const date = new Date(time);
      return formatSimpleTime(date);
    }
  },
  computed: {
    posts() {
      try {
        return this.review.posts;
      } catch (e) {
        console.error(e)
        return [];
      }
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

  }
}
</script>

