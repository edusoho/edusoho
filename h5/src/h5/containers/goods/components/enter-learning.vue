<template>
  <div class="enter-learning" @click="goto">
    <div class="enter-learning-container">
      <div class="enter-learning-container__icon">
        <i class="iconfont icon-renqi"></i>
      </div>
      <div class="enter-learning-container__text">
        <span class="content">
          进群免费学习课程
          <span class="hot">Hot</span>
        </span>
      </div>
      <div class="enter-learning-container__add">加入学习</div>
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
        },
      });
    },
  },
};
</script>
