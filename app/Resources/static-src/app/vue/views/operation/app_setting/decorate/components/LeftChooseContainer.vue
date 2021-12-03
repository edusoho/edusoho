<template>
  <aside class="left-choose-container pull-left">
    <div class="component-add-container">
      <component-classify
        v-for="(classify, index) in Classifys"
        :key="index"
        :index="index"
        :classify="classify"
        :current-classify="currentClassify"
        @add-module="addModule"
        @change-classify="changeClassify"
      />
    </div>
  </aside>
</template>

<script>
const Classifys = [
  {
    title: '基础组件',
    icon: 'icon-check-circle',
    components: [
      { title: '轮播图', icon: 'icon-check-circle', type: 'slide_show' },
      { title: '课程列表', icon: 'icon-check-circle', type: 'course_list' },
      { title: '班级列表', icon: 'icon-check-circle', type: 'classroom_list' },
      { title: '图片公告', icon: 'icon-check-circle', type: 'poster' },
      { title: '图文导航', icon: 'icon-check-circle', type: 'graphic_navigation' },
      { title: '公开课列表', icon: 'icon-check-circle', type: 'open_course_list' },
      { title: '题库列表', icon: 'icon-check-circle', type: 'item_bank_exercise' }
    ]
  },
  {
    title: '营销组件',
    icon: 'icon-check-circle',
    components: [
      { title: '优惠卷', icon: 'icon-check-circle', type: 'coupon' },
      { title: '会员专区', icon: 'icon-check-circle', type: 'vip' }
    ]
  }
];

import ComponentClassify from './ComponentClassify.vue';

export default {
  name: 'LeftChooseContainer',

  components: {
    ComponentClassify
  },

  data() {
    return {
      Classifys,
      currentClassify: 0
    }
  },

  methods: {
    addModule(type) {
      this.$emit('add-module', type);
    },

    changeClassify(value) {
      this.currentClassify = value;
    }
  }
}
</script>

<style lang="less" scoped>
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
</style>
