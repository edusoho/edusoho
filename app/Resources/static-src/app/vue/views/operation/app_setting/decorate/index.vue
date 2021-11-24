<template>
  <div class="decorate-container">
    <the-header />

    <div class="decorate-main clearfix">
      <aside class="left-choose-container pull-left">
        <div class="component-add-container">
          <component-classify
            v-for="(classify, index) in Classifys"
            :key="index"
            :classify="classify"
            :index="index"
            :current-classify-index="currentClassifyIndex"
            @add-component="handleClickAdd"
            @click.native="handleChangeClassify(index)"
          />
        </div>
      </aside>

      <section class="center-preview-container pull-left">
        <div class="main-preview-container">
          <find-head />

          <component
            :is="getComponentName(component.type)"
            v-for="(component, index) in components"
            :key="index"
            :module-type="`${component.type}-${index}`"
            :current-edit="currentEdit"
            @click.native="changeCurrentEdit(component.type, index)"
          />

          <find-footer />
        </div>
      </section>

      <aside class="right-edit-container pull-left">
        <component :is="currentEditComponent" />
      </aside>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';

import { Classifys } from './default-config';

import ModuleCounter from 'app/vue/utils/module-counter';

import TheHeader from './components/TheHeader.vue';
import ComponentClassify from './components/ComponentClassify.vue';
import FindHead from '../components/FindHead.vue';
import FindFooter from '../components/FindFooter.vue';
import Swiper from '../components/Swiper.vue';
import SwiperEdit from '../components/SwiperEdit.vue';

const components = {
  slide_show: 'Swiper'
}

const editComponents = {
  slide_show: 'SwiperEdit'
}

export default {
  components: {
    TheHeader,
    ComponentClassify,
    FindHead,
    FindFooter,
    Swiper,
    SwiperEdit
  },

  data() {
    return {
      Classifys,
      currentClassifyIndex: 0,
      components: [],
      typeCount: {},
      currentEdit: '',
      currentEditComponent: ''
    }
  },

  mounted() {
    this.initTypeCount();
    this.initCurrentEdit();
  },

  methods: {
    // 模块类型计数初始化
    initTypeCount() {
      const typeCount = new ModuleCounter();
      _.forEach(this.components, (component) => {
        typeCount.addByType(component.type);
      });
      this.typeCount = typeCount;
    },

    // 初始化当前编辑组件
    initCurrentEdit() {
      const length = _.size(this.components);
      if (length) {
        this.changeCurrentEdit(this.components[0].type, 0);
      }
    },

    handleChangeClassify(val) {
      this.currentClassifyIndex = val;
    },

    handleClickAdd(info) {
      const { type } = info;
      this.typeCount.addByType(type);
      this.components.push(info);
      this.changeCurrentEdit(type, _.size(this.components) - 1);
    },

    changeCurrentEdit(type, index) {
      this.currentEdit = `${type}-${index}`;
      this.currentEditComponent = editComponents[type];
    },

    getComponentName(type) {
      return components[type];
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
      padding: 40px 0 40px 190px;
      width: calc(100% - 384px - 80px);
      height: 100%;
      background-color: #f5f7fa;
      scrollbar-width: none;
      -ms-overflow-style: none;

      &::-webkit-scrollbar {
        display: none;
      }

      .main-preview-container {
        position: relative;
        padding-bottom: 50px;
        margin: 0 auto;
        width: 375px;
        min-height: 90%;
        box-shadow: 0px 1px 3px 1px rgba(0, 0, 0, 0.05);
        background-color: #fff;
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
