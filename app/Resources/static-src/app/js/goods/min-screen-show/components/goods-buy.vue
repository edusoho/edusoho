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
import axios from 'axios';
export default {
  data() {
    return {
      isFavorite: false  // 是否收藏
    }
  },
  methods: {
    // 添加收藏
    addFavorite(id) {
      axios({
        url: "/api/favorite",
        method: "POST",
        data: {
          'targetType': 'goods',
          'targetId': id
        },
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(res => {
        this.isFavorite = true;
      });
    },
    // 移除收藏
    removeFavorite(id) {
      axios({
        url: "/api/favorite",
        method: "DELETE",
        params: {
          'targetType': 'goods',
          'targetId': id
        },
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(res => {
        this.isFavorite = false;
      });
    },
    // 收藏/移除收藏
    onFavorite() {
      let goodsId = window.location.pathname.replace(/[^0-9]/ig, ""); // goods id
      if (this.isFavorite) {
        this.removeFavorite(goodsId);
      } else {
        this.addFavorite(goodsId);
      }
    }
  }
}
</script>