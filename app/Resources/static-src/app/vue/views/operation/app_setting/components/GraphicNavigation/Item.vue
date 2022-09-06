<template>
  <div class="gn-item">
    <div class="gn-item__img">
      <img :src="item.image.uri || '/static-dist/app/img/vue/decorate/gn_empty.png'">
    </div>
    <div class="gn-item__title text-overflow">{{ item.title }}</div>
  </div>
</template>

<script>
export default {
  name: 'GraphicNavigationItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.gn-item {
  text-align: center;

  &__img {
    overflow: hidden;
    margin: 0 auto;
    width: 40px;
    height: 40px;
    border-radius: 16px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__title {
    margin-top: 4px;
    height: 20px;
    font-size: 12px;
    color: #1D2129;
    font-weight: 400;
    line-height: 20px;
  }
}
</style>
