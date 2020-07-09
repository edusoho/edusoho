<template>
  <div class="info-learn">
    <div class="info-learn__header clearfix">
      <span class="header-title pull-left"><slot name="title"></slot></span>
      <span class="header-more pull-right">更多<i class="es-icon es-icon-chevronright"></i></span>
    </div>
    <div class="info-learn__body">
      <template v-if="recommendGoods.length">
        <div class="body-item" v-for="item in recommendGoods" :key="item.id" @click="onJump(item.id)">
          <div class="body-item__img">
            <img :src="item.image" alt="">
            <div class="learners-mask"></div>
            <span class="learners-number"><i class="es-icon es-icon-people"></i>8888</span>
          </div>
          <div class="body-item__content">
            <p class="content-title text-overflow">{{ item.title }}</p>
            <p class="content-price text-overflow">12000.0 Edusoho学习币…</p>
          </div>
        </div>
      </template>
      <div v-else>暂时还没有推荐课程哦...</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    recommendGoods: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    // onJump(id) {
    //   if (id ==  this.$route.params.id) return;
    //   this.$router.push({ path: `/goods/${id}/course`});
    // }
  }
}
</script>