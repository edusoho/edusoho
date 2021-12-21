<template>
  <aside class="left-choose-container pull-left" :class="{ 'left-choose-container--blank': preview }">
    <div v-show="!preview" class="component-add-container">
      <component-classify
        v-for="(classify, index) in Classifys"
        :key="index"
        :index="index"
        :classify="classify"
        :current-classify="currentClassify"
        :coupon-enabled="couponEnabled"
        :vip-enabled="vipEnabled"
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
    icon: 'icon-base',
    components: [
      { title: '轮播图', icon: 'icon-lunbotu', type: 'slide_show' },
      { title: '课程列表', icon: 'icon-kechengliebiao', type: 'course_list' },
      { title: '班级列表', icon: 'icon-banjiliebiao', type: 'classroom_list' },
      { title: '图片广告', icon: 'icon-tuwenguanggao', type: 'poster' },
      { title: '图文导航', icon: 'icon-tuwendaohang', type: 'graphic_navigation' },
      { title: '公开课列表', icon: 'icon-open-course', type: 'open_course_list' },
      { title: '题库列表', icon: 'icon-item-bank', type: 'item_bank_exercise' }
    ]
  },
  {
    title: '营销组件',
    icon: 'icon-marketing',
    components: [
      { title: '优惠卷', icon: 'icon-youhuiquan', type: 'coupon' },
      { title: '会员专区', icon: 'icon-huiyuanzhuanqu', type: 'vip' }
    ]
  }
];

import ComponentClassify from './ComponentClassify.vue';

export default {
  name: 'LeftChooseContainer',

  props: {
    vipEnabled: {
      type: Boolean,
      default: false
    },

    couponEnabled: {
      type: Boolean,
      default: false
    },

    preview: {
      type: Boolean,
      required: true
    }
  },

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

  &--blank {
    background-color: #f5f7fa;
  }

  .component-add-container {
    position: relative;
    padding-top: 20px;
    height: 100%;
    user-select: none;
  }
}
</style>
