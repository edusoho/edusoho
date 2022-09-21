<template>
  <div class="column-item pull-left">
    <div class="column-item__img">
      <img :src="item.cover.middle" alt="">
      <img class="img-bottom" src="/static-dist/app/img/vue/decorate/img_bottom.png" alt="">
    </div>
    <div class="column-item__info">
      <div class="column-item__title text-overflow">{{ item.title }}</div>
      <div class="column-item__subtitle" v-html="subtitle" />
      <div class="column-item__price">{{ item.price }} {{ 'cny' | trans }}</div>
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
.column-item {
  overflow: hidden;
  margin-top: 16px;
  width: 164px;
  border-radius: 8px;
  box-shadow: 0 2px 16px 0 rgba(0, 0, 0, 0.05);

  &:nth-of-type(odd) {
    margin-right: 15px;
  }

  &__img {
    position: relative;
    padding: 8px;
    width: 100%;
    height: 92px;
    background-color: #fff;

    img {
      width: 100%;
      height: 100%;
    }

    .img-bottom {
      position: absolute;
      bottom: -5px;
      left: 8px;
      width: calc(100% - 16px);
      height: auto;
    }
  }

  &__info {
    position: relative;
    padding: 8px;
    width: 100%;
    height: 80px;
    background-color: #fff;
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
    left: 8px;
    bottom: 8px;
    font-size: 12px;
    font-weight: 500;
    line-height: 16px;
    color: #FF7A34;
  }
}
</style>
