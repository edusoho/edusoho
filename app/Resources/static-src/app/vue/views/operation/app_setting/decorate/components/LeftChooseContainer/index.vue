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
    title: Translator.trans('decorate.basic'),
    icon: 'icon-base',
    components: [
      { title: Translator.trans('decorate.carousel'), icon: 'icon-lunbotu', type: 'slide_show' },
      { title: Translator.trans('decorate.graphic_navigation'), icon: 'icon-tuwendaohang', type: 'graphic_navigation' },
      { title: Translator.trans('decorate.image_ad'), icon: 'icon-tuwenguanggao', type: 'poster' },
      { title: Translator.trans('decorate.curriculum_schedule'), icon: 'icon-kechengliebiao', type: 'course_list' },
      { title: Translator.trans('decorate.class_list'), icon: 'icon-banjiliebiao', type: 'classroom_list' },
      { title: Translator.trans('decorate.question_bank_list'), icon: 'icon-item-bank', type: 'item_bank_exercise' },
      { title: Translator.trans('decorate.open_class_list'), icon: 'icon-open-course', type: 'open_course_list' },
      { title: Translator.trans('decorate.announcement'), icon: 'icon-announcement', type: 'announcement' },
      { title: Translator.trans('decorate.information'), icon: 'icon-information', type: 'information' }
    ]
  },
  {
    title: Translator.trans('decorate.marketing'),
    icon: 'icon-marketing',
    components: [
      { title: Translator.trans('decorate.coupon'), icon: 'icon-youhuiquan', type: 'coupon' },
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
  background: #333;

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
