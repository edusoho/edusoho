<template>
  <div v-if="recommendGoods.length" class="info-right-learn info-right-box">
    <div class="title">猜你想学</div>
    <div class="learn-info">
      <div class="learn-info-item learn clearfix" @click="onClickGoods(item)"  v-for="item in recommendGoods" :key="item.id">
        <div class="learn-img pull-left">
          <img :src="item.images.small" alt="">
        </div>
        <div class="learn-text pull-right">
          <p class="learn-text__title">{{ item.title }}</p>
          <span v-if="item.minPrice == item.maxPrice" class="learn-text__price">{{ item.minPrice }}</span>
          <span v-if="item.minPrice != item.maxPrice" class="learn-text__price">{{ item.minPrice }}起</span>
        </div>
      </div>
    </div>
    <div class="learn-more">
        <a v-if="goods.type ==='course'" target="_blank" href="/course/explore">查看更多<i class="es-icon es-icon-chevronright"></i></a>
        <a v-if="goods.type ==='classroom'" target="_blank" href="/classroom/explore">查看更多<i class="es-icon es-icon-chevronright"></i></a>
    </div>
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
      },
      goods: {
          type: Object,
          default: null,
      }
    },
    methods: {
        onClickGoods(item) {
            window.open(`/goods/show/${item.id}`, '_blank');
        },
    }
  }
</script>