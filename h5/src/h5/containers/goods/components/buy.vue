<template>
  <div class="info-buy">
    <div class="info-buy__collection" @click="onFavorite">
      <template v-if="isFavorite">
        <i class="iconfont icon-wode" style="color: #FF7E56;"></i>
        <span style="color: #FF7E56;">已收藏</span>
      </template>
      <template v-else>
        <i class="iconfont icon-wode"></i>
        <span>收藏</span>
      </template>
    </div>
    <div class="info-buy__btn">立即购买</div>
  </div>
</template>

<script>
import Api from '@/api';
export default {
  data() {
    return {
      isFavorite: false
    }
  },
  methods: {
    // 添加收藏
    addFavorite() {
      Api.addFavorite({
        data: {
          'targetType': 'goods',
          'targetId': this.$route.params.id
        }
      }).then(res => {
        console.log(res);
      });
    },
    // 移除收藏
    removeFavorite() {
      Api.removeFavorite({
        params: {
          'targetType': 'course',
          'targetId': this.$route.params.id
        }
      }).then(res => {
        console.log(res);
      });
    },
    onFavorite() {
      if (this.isFavorite) {
        this.isFavorite = false;
        this.removeFavorite();
      } else {
        this.isFavorite = true;
        this.addFavorite();
      }
    }
  }
}
</script>