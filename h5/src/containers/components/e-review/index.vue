<template>
  <div class="e-review">
    <img class="e-review-avatar avatar-img" :src="avatar" alt="">
    <div class="e-review-body">
      <div>
        <span class="e-review-nickname text-14">{{ userName }}</span>
        <van-rate class="e-review-rating" v-model="emptyRating" :size="15" :count="emptyRating"
          color="#B0BDC9" :readonly="true"></van-rate>
        <van-rate class="e-review-rating" v-model="rating" :size="15" :count="rating"
          color="#FFAA00" :readonly="true"></van-rate>
      </div>
      <div class="e-review-time text-12">{{ review.createdTime }}</div>
      <div class="e-review-content text-14">{{ review.content }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'review',
  props: {
    review: {
      type: Object,
      default: {}
    }
  },
  computed: {
    avatar() {
      try {
        return this.review.user.avatar.middle;
      } catch (e) {
        console.error(e)
        return '';
      }
    },
    userName() {
      try {
        return this.review.user.nickname;
      } catch (e) {
        console.error(e)
        return '匿名用户';
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
    }
  }
}
</script>

