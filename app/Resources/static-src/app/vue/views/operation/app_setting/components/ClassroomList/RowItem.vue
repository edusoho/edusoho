<template>
  <div class="row-item clearfix">
    <div class="row-item__img pull-left">
      <img :src="item.cover.middle" alt="">
      <img class="img-bottom" src="/static-dist/app/img/vue/decorate/img_bottom.png" alt="">
    </div>
    <div class="row-item__info pull-left">
      <div class="row-item__title text-overflow">{{ item.title }}</div>
      <div class="row-item__subtitle" v-html="subtitle" />
      <div class="row-item__price">{{ item.price }} {{ 'cny' | trans }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClassroomListItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  },

  computed: {
    subtitle() {
      return Translator.trans('classroom_course_num', { courseNum: this.item.courseNum });
    }
  }
}
</script>

<style lang="less" scoped>
.row-item {
  overflow: hidden;
  padding: 16px;
  margin-top: 16px;
  width: 100%;
  height: 122px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0px 2px 16px 0px rgba(0, 0, 0, 0.05);

  &__img {
    position: relative;
    width: 149px;
    height: 84px;

    img {
      overflow: hidden;
      width: 100%;
      height: 100%;
      border-radius: 8px;
    }

    .img-bottom {
      position: absolute;
      bottom: -12px;
      left: 3%;
      width: 94%;
      height: auto;
    }
  }

  &__info {
    position: relative;
    margin-left: 8px;
    width: calc(100% - 190px);
    height: 100%;
  }

  &__title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    line-height: 20px;
  }

  &__subtitle {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;

    /deep/ span {
      margin-right: 2px;
      margin-left: 2px;
      color: #ff5c3b;
    }
  }

  &__price {
    position: absolute;
    left: 0px;
    bottom: 6px;
    font-size: 12px;
    font-weight: 500;
    line-height: 16px;
    color: #ff5c3b;
  }
}
</style>
