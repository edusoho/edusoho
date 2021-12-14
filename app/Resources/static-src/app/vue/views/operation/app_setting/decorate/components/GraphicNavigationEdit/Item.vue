<template>
  <div class="gn-item clearfix">
    <div class="gn-item__img pull-left">
      <div class="modity-mask">更换图片</div>
      <img :src="item.image.url || '/static-dist/app/img/vue/decorate/gn_empty.png'">
    </div>
  </div>
</template>

<script>
export default {
  name: 'GraphicNavigationEditItem',

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
  position: relative;
  padding: 15px 10px;
  border: 1px solid #e1e1e1;
  background-color: #fff;

  &__img {
    position: relative;
    overflow: hidden;
    margin-right: 10px;
    width: 80px;
    height: 80px;
    line-height: 80px;
    text-align: center;
    background-color: #f5f5f5;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
      border-radius: 16px;
    }

    .modity-mask {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      color: transparent;
      transition: all .2s ease-in-out;
    }

    &:hover {
      .modity-mask {
        background: rgba(0, 0, 0, .5);
        color: #fff;
        transition: all .2s ease-in-out;
      }
    }
  }
}
</style>
