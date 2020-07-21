<template>
  <div v-if="recommendGoods.length" class="info-right-learn info-right-box">
    <div class="title">猜你想学</div>
    <div class="learn-info">
      <div class="learn-info-item learn clearfix"  v-for="item in recommendGoods" :key="item.id">
        <div class="learn-img pull-left">
          <img :src="item.images.small" alt="">
        </div>
        <div class="learn-text pull-right">
          <p class="learn-text__title">{{ item.title }}</p>
          <span class="learn-text__price">129.00</span>
        </div>
      </div>
    </div>
    <div class="learn-more"><a href="javascript:;">查看更多<i class="es-icon es-icon-chevronright"></i></a></div>
  </div>
</template>

<script>
  export default {
    props: {
      recommendGoods: {
        type: Array,
        default: function () {
          return []
        }
      }
    }
  }
</script>