<template>
  <div class="info-learn">
    <div class="info-learn__header clearfix">
      <slot class="header-title pull-left" name="title"></slot>
      <span class="header-more pull-right"
        >更多<i class="iconfont icon-About"></i
      ></span>
    </div>
    <div class="info-learn__body">
      <template v-if="recommendGoods.length">
        <div
          class="body-item"
          v-for="goods in recommendGoods"
          :key="goods.id"
          @click="onJump(goods.id)"
        >
          <div class="body-item__img">
            <img :src="goods.images.large" alt="" />
            <div class="learners-mask"></div>
            <span class="learners-number"
              ><i class="iconfont icon-renqi"></i>8888</span
            >
          </div>
          <div class="body-item__content">
            <p class="content-title text-overflow">{{ goods.title }}</p>
            <p
              class="content-price text-overflow"
              :class="{ 'is-free': Number(goods.maxPrice.amount) == 0 }"
              v-if="goods.maxPrice.amount == goods.minPrice.amount"
            >
              {{
                Number(goods.maxPrice.amount) == 0
                  ? '免费'
                  : `¥${goods.maxPrice.amount}`
              }}
            </p>
            <p class="content-price text-overflow" v-else>
              ¥{{ goods.minPrice.amount }} 起
            </p>
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
      default: () => [],
    },
  },
  methods: {
    onJump(id) {
      if (id == this.$route.params.id) return;
      this.$router.push({ path: `/goods/${id}/show` });
    },
  },
};
</script>
