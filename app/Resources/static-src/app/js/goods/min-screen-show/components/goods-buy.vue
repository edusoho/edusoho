<template>
  <div class="info-buy">
    <div class="info-buy__collection" @click="onFavorite">
      <template v-if="isFavorite">
        <i class="es-icon es-icon-favorite"></i>
        <span>已收藏</span>
      </template>
      <template v-else>
        <i class="es-icon es-icon-favoriteoutline"></i>
        <span>收藏</span>
      </template>
    </div>
    <div class="info-buy__btn">立即购买</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isFavorite: false  // 是否收藏
    }
  },
  methods: {
    // 收藏/移除收藏
    onFavorite() {
      if (this.isFavorite) {
        // 移除收藏
      } else {
        // 收藏
      }
    }
  }
}
</script>