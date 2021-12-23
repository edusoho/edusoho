<template>
  <div class="row-item clearfix">
    <div class="row-item__img pull-left">
      <img :src="item.cover.middle" alt="">
    </div>
    <div class="row-item__info pull-left">
      <div class="row-item__title text-overflow">{{ item.title || item.courseSetTitle }}</div>
      <div class="row-item__price">{{ item.price }} {{ 'cny' | trans }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ItemBankListItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.row-item {
  overflow: hidden;
  margin-top: 16px;
  width: 100%;
  height: 90px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0px 2px 16px 0px rgba(0, 0, 0, 0.05);

  &__img {
    overflow: hidden;
    width: 160px;
    height: 100%;
    border-radius: 8px;
    background-color: #eee;

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__info {
    position: relative;
    padding: 8px;
    width: calc(100% - 160px);
    height: 100%;
  }

  &__title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    line-height: 20px;
  }

  &__price {
    position: absolute;
    left: 8px;
    bottom: 8px;
    font-size: 12px;
    font-weight: 500;
    color: #ff5c3b;
    line-height: 16px;
  }
}
</style>
