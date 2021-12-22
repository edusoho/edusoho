<template>
  <div class="open-course-item clearfix">
    <div class="open-course-img pull-left">
      <img :src="item.middlePicture" alt="">
    </div>

    <div class="open-course-info pull-right">
      <div class="open-course-info__title text-overflow">{{ item.title }}</div>
      <div class="open-course-info__bottom clearfix">
        <div class="open-course-time pull-left">{{ time }}</div>
        <div class="open-course-btn pull-right">{{ 'site.btn.enter_live' | trans }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OpenCourseListItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  },

  computed: {
    time() {
      return moment(this.item.createdTime).format('MM.DD HH.mm');
    }
  }
}
</script>

<style lang="less" scoped>
.open-course-item {
  overflow: hidden;
  width: 100%;
  height: 80px;
  background: #fff;
  box-shadow: 0px 2px 16px 0px rgba(0, 0, 0, 0.05);
  border-radius: 8px;

  .open-course-img {
    width: 142px;
    height: 100%;
    background-color: #eee;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .open-course-info {
    padding: 8px;
    width: calc(100% - 142px);
    height: 100%;

    &__title {
      font-size: 14px;
      font-weight: 500;
      color: #333;
      line-height: 20px;
    }

    &__bottom {
      margin-top: 20px;
      width: 100%;

      .open-course-time {
        position: relative;
        top: 2px;
        font-size: 12px;
        color: #999;
        line-height: 16px;
      }

      .open-course-btn {
        width: 64px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        background: #03c777;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 500;
        color: #fff;
      }
    }
  }
}
</style>
