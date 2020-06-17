<template>
  <div class="reviews">
    <div class="reviews-item clearfix" v-for="item in reviews" :key="item.id">
      <img class="review-item__img pull-left" src="http://qa.edusoho.cn/files/default/2020/06-09/1643462753a8389520.jpg" alt="">
      <div class="reviews-item__text reviews-text pull-left">
        <p class="reviews-text__nickname">{{ item.user.nickname }} {{ item.targetName }} {{ item.createdTime }}</p>
        <p class="reviews-text__rating">星星 {{ item.rating }}</p>
        <p class="reviews-text__content">{{ item.content }}</p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      reviews: {
        type: Array,
        default: function () {
          return []
        }
      }
    }
  }
</script>