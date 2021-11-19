<template>
  <div class="decorate-container">
    <the-header />

    <div class="decorate-main clearfix">
      <aside class="left-choose-container pull-left">
        <div class="component-add-container">
          <component-classify
            v-for="(classify, index) in Classifys"
            :key="classify.key"
            :classify="classify"
            :index="index"
            :current-classify="currentClassify"
            @click.native="handleChangeClassify(classify.key)"
          />
        </div>
      </aside>

      <section class="center-preview-container pull-left">
        <div class="main-preview-container">
          <find-head />
        </div>
      </section>

      <aside class="right-edit-container pull-left"></aside>
    </div>
  </div>
</template>

<script>
const Classifys = [
  {
    title: '基础组件',
    key: 'basic',
    icon: 'icon-check-circle',
    lists: [
      { title: '轮播图', icon: 'icon-check-circle', name: 'Swiper' },
      { title: '课程列表', icon: 'icon-check-circle', name: 'CouseList' },
      { title: '班级列表', icon: 'icon-check-circle', name: 'ClassroomList' },
      { title: '图片公告', icon: 'icon-check-circle', name: 'Ad' },
      { title: '图文导航', icon: 'icon-check-circle', name: 'Nav' },
      { title: '公开课列表', icon: 'icon-check-circle', name: 'OpenCourseList' },
      { title: '题库列表', icon: 'icon-check-circle', name: 'QuestionBankList' }
    ]
  },
  {
    title: '营销组件',
    key: 'marketing',
    icon: 'icon-check-circle',
    lists: [
      { title: '优惠卷', icon: 'icon-check-circle', name: 'Coupon' },
      { title: '会员专区', icon: 'icon-check-circle', name: 'Vip' }
    ]
  }
];

import TheHeader from './components/TheHeader.vue';
import ComponentClassify from './components/ComponentClassify.vue';
import FindHead from '../components/FindHead.vue';

export default {
  components: {
    TheHeader,
    ComponentClassify,
    FindHead
  },

  data() {
    return {
      Classifys,
      currentClassify: 'basic'
    }
  },

  methods: {
    handleChangeClassify(val) {
      this.currentClassify = val;
    }
  }
}
</script>

<style lang="less" scoped>
.decorate-container {
  width: 100%;
  height: 100%;

  .decorate-main {
    overflow-y: hidden;
    position: relative;
    width: 100%;
    height: calc(100% - 52px);

    .left-choose-container {
      width: 80px;
      height: 100%;
      background: #243042;

      .component-add-container {
        position: relative;
        padding-top: 20px;
        height: 100%;
        user-select: none;
      }
    }

    .center-preview-container {
      overflow-y: auto;
      padding: 40px 0 20px 190px;
      width: calc(100% - 384px - 80px);
      pointer-events: none;
      background-color: #f5f7fa;

      .main-preview-container {
        margin: 0 auto;
        width: 375px;
        height: 700px;
        background-color: red;
      }
    }

    .right-edit-container {
      overflow-y: auto;
      width: 384px;
      height: 100%;
      background-color: #fff;
    }
  }
}

</style>
