<template>
  <div class="reviews">
    <div class="reviews-item clearfix" v-for="item in reviews" :key="item.id">
      <img class="reviews-item__img pull-left" src="http://qa.edusoho.cn/files/default/2020/06-09/1643462753a8389520.jpg" alt="">
      <div class="reviews-item__text reviews-text pull-left">
        <div class="reviews-text__nickname">
          <a class="link-dark" href="javascript:;" target="_blank">{{ item.user.nickname }}</a>
          <span>{{ item.targetName }}</span>
          {{ item.createdTime }}
        </div>
        <div class="reviews-text__rating" v-html="$options.filters.rating(item.rating)"></div>
        <div class="reviews-text__content">{{ item.content }}</div>
        <div class="reviews-text__reply"><a href="javascript:;">回复</a></div>
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
    },
    filters: {
      rating(score) {
        let floorScore = Math.floor(score);
        let emptyNum = 5 - floorScore;
        let ele = '';
        if (floorScore > 0) {
          for (let i = 0; i < floorScore; i++) {
            ele += `<i class="es-icon es-icon-star color-warning"></i>`;
          }
        }
        if ((score - floorScore) >= 0.5) {
          ele += `<i class="es-icon es-icon-starhalf color-warning"></i>`;
        }
        if (emptyNum > 0) {
          for (let i = 0; i < emptyNum; i++) {
            ele += `<i class="es-icon es-icon-staroutline"></i>`;
          }
        }
        return ele;
      }
    }
  }
</script>