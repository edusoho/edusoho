<template>
  <div class="decorate-container">
    <the-header />

    <div class="decorate-main clearfix">
      <left-choose-container @add-component="handleAddComponent" />

      <section class="center-preview-container pull-left">
        <div class="main-preview-container">
          <find-head />

          <component
            v-for="(module, index) in modules"
            :key="index"
            :is="module.type"
            :module-data="module.data"
            :module-type="`${module.type}-${index}`"
            :current-module-type="currentModule.type"
            :is-first="index === 0"
            :is-last="index === lastModuleIndex"
            @click.native="changeCurrentModule(module, index)"
            @event-actions="handleClickActions"
          />

          <find-footer />
        </div>
      </section>

      <aside class="right-edit-container pull-left">
        <component
          v-if="currentModule.editComponent"
          :is="currentModule.editComponent"
          :module-info="modules[currentModule.index].data"
          @update:edit="updateEdit"
        />
      </aside>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';

import TheHeader from './components/TheHeader.vue';
import LeftChooseContainer from './components/LeftChooseContainer.vue';

import FindHead from '../components/FindHead.vue';
import FindFooter from '../components/FindFooter.vue';
import slide_show from '../components/Swiper.vue';
import slide_show_edit from './components/SwiperEdit.vue';

export default {
  components: {
    TheHeader,
    LeftChooseContainer,
    FindHead,
    FindFooter,
    slide_show,
    slide_show_edit
  },

  data() {
    return {
      modules: [],
      currentModule: {}
    }
  },

  computed: {
    lastModuleIndex() {
      return _.size(this.modules) - 1;
    }
  },

  methods: {
    handleAddComponent(info) {
      this.modules.push(info);
      this.changeCurrentModule(info, _.size(this.modules) - 1);
    },

    changeCurrentModule(info, index) {
      const { type } = info;

      const currentModule = {
        index, // 编辑时用来确定位置
        type: `${type}-${index}`, // 提交时的 module-type
        editComponent: `${type}_edit` // 对应的编辑组件
      };

      _.assign(this, {
        currentModule
      });
    },

    handleClickActions(type) {
      if (type === 'up') {
        this.upModulel();
        return;
      }

      if (type === 'down') {
        this.downModule();
        return;
      }

      if (type === 'remove') {
        this.removeModule();
      }
    },

    upModulel() {

    },

    downModule() {

    },

    removeModule() {
      const { index } = this.currentModule;
      this.currentModule = {};
      this.modules.splice(index, 1);

      const newIndex = index - 1 >= 0 ? index - 1 : (index + 1 <= this.lastModuleIndex ? index + 1 : undefined);
      let currentModule = {};
      if (newIndex || newIndex === 0) {
        const module = this.modules[newIndex];
        const { type } = module;
        currentModule = {
          index: newIndex,
          type: `${type}-${newIndex}`,
          editComponent: `${type}_edit`
        };
      }

      _.assign(this, {
        currentModule
      });
    },

    updateEdit(params) {
      const { type, data } = params;
      if (type === 'swiper') {
        this.modules[this.currentModule.index].data = data;
      }
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
