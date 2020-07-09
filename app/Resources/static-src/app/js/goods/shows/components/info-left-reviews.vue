<template>
  <div class="reviews">
    <create-review :currentPlan="currentPlan" />
    <review v-for="review in reviews" :key="review.id" :review="review" />
    <back-to-top></back-to-top>
  </div>
</template>

<script>
  import BackToTop from './back-to-top'; // 回到顶部
  import CreateReview from './create-review'; // 创建评价
  import Review from './review';
  export default {
    props: {
      reviews: {
        type: Array,
        default: function () {
          return []
        }
      },
      currentPlan: {
        type: Object,
        default: () => {}
      }
    },
    components: {
      BackToTop,
      CreateReview,
      Review
    }
  }
</script>