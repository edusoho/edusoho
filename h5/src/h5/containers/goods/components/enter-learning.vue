<template>
  <div class="enter-learning" @click="goto">
    <div class="enter-learning-container">
      <div class="enter-learning-container__icon">
        <i class="iconfont icon-renqi"></i>
      </div>
      <div class="enter-learning-container__text">
        <span class="content">
          {{ qrTitle }}
          <span class="hot">Hot</span>
        </span>
      </div>
      <div class="enter-learning-container__add">{{ $t('goods.join') }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  props: {
    qrInfo: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    goto(e) {
      this.$router.push({
        name: 'goods_qr',
        query: {
          imageUrl: this.qrInfo.imageUrl || '',
          title: this.qrInfo.title || '',
          content: this.qrInfo.content || '',
        },
      });
    },
  },
  computed: {
    qrTitle() {
      return this.qrInfo.title.slice(0, 10).length == 10
        ? this.qrInfo.title.slice(0, 10) + '...'
        : this.qrInfo.title.slice(0, 10);
    },
  },
};
</script>
