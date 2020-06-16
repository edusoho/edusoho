<template>
  <div id="info-left-3" class="info-left-reviews content-item js-content-item">
    <h3  class="content-item__title">学员评价</h3>
    <div class="reviews clearfix" v-for="item in reviews" :key="item.id">
      <img class="reviews-img pull-left" src="http://qa.edusoho.cn/files/default/2020/06-09/1643462753a8389520.jpg" alt="">
      <div class="reviews-text pull-left">
        <p>{{ item.user.nickname }} {{ item.targetName }} {{ item.createdTime }}</p>
        <p>星星 {{ item.rating }}</p>
        <p>{{ item.content }}</p>
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