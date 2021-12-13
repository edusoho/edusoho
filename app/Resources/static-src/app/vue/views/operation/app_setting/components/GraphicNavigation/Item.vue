<template>
  <div class="gn-item">
    <div class="gn-item__img">
      <img :src="item.image.url || '/static-dist/app/img/vue/decorate/gn_empty.png'">
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
  padding-top: 16px;
  padding-bottom: 16px;
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
    margin-top: 8px;
    font-size: 12px;
    color: #464646;
    line-height: 16px;
  }
}
</style>
