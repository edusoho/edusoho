<template>
  <div class="decorate-container">
    <the-header @save="handleClickSave" />

    <div class="decorate-main clearfix">
      <left-choose-container @add-module="addModule" />

      <section class="center-preview-container pull-left">
        <div class="main-preview-container">
          <find-head />

          <draggable
            v-model="modules"
            v-bind="dragOptions"
            @start="drag = true"
            @end="draggableEnd"
          >
            <transition-group type="transition" :name="!drag ? 'flip-list' : null">
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
            </transition-group>
          </draggable>

          <find-footer />
        </div>
      </section>

      <aside class="right-edit-container pull-left">
        <component
          v-if="currentModule.editComponent"
          :key="currentModule.index"
          :is="currentModule.editComponent"
          :module-info="modules[currentModule.index].data"
          :module-data="modules[currentModule.index].data"
          @update-edit="updateEdit"
        />
      </aside>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';

import { Vip } from 'common/vue/service';

import { DefaultData } from './default-data';

import Draggable from 'vuedraggable';
import TheHeader from './components/TheHeader.vue';
import LeftChooseContainer from './components/LeftChooseContainer.vue';

import FindHead from '../components/FindHead.vue';
import FindFooter from '../components/FindFooter.vue';
import slide_show from '../components/Swiper.vue';
import slide_show_edit from './components/SwiperEdit.vue';
import vip from '../components/Vip.vue';
import vip_edit from './components/VipEdit.vue';
import coupon from '../components/Coupon/index.vue';
import coupon_edit from './components/CouponEdit/index.vue';
import poster from '../components/Poster/index.vue';
import poster_edit from './components/PosterEdit/index.vue';
import graphic_navigation from '../components/GraphicNavigation/index.vue';
import graphic_navigation_edit from './components/GraphicNavigationEdit/index.vue';

export default {
  components: {
    Draggable,
    TheHeader,
    LeftChooseContainer,
    FindHead,
    FindFooter,
    slide_show,
    slide_show_edit,
    vip,
    vip_edit,
    coupon,
    coupon_edit,
    poster,
    poster_edit,
    graphic_navigation,
    graphic_navigation_edit
  },

  data() {
    return {
      modules: [],
      currentModule: {},
      drag: false,
      vipLevels: []
    }
  },

  computed: {
    lastModuleIndex() {
      return _.size(this.modules) - 1;
    },

    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      }
    }
  },

  methods: {
    addModule(type) {
      const info = _.cloneDeep(DefaultData[type]);
      if (type === 'vip') {
        this.getVipLevels();
        const tempLevels = [...this.vipLevels];
        info.data.items = tempLevels;
      }

      this.modules.push(info);
      this.changeCurrentModule(info, _.size(this.modules) - 1);
    },

    async getVipLevels() {
      if (_.size(this.vipLevels)) return;

      this.vipLevels = await Vip.getLevels();
      _.forEach(this.modules, module => {
        const { type } = module;
        if (type === 'vip') {
          const tempLevels = [...this.vipLevels];
          module.data.items = tempLevels;
        }
      });
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

    draggableEnd({ newIndex }) {
      this.drag = false;
      this.changeCurrentModule(this.modules[newIndex], newIndex);
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
      const { index } = this.currentModule;
      const tempModule = this.modules[index - 1];
      const module = this.modules[index];
      this.$set(this.modules, index - 1, this.modules[index]);
      this.$set(this.modules, index, tempModule);
      this.changeCurrentModule(module, index - 1);
    },

    downModule() {
      const { index } = this.currentModule;
      const tempModule = this.modules[index + 1];
      const module = this.modules[index];
      this.$set(this.modules, index + 1, this.modules[index]);
      this.$set(this.modules, index, tempModule);
      this.changeCurrentModule(module, index + 1);
    },

    removeModule() {
      const { index } = this.currentModule;
      this.currentModule = {};
      this.modules.splice(index, 1);

      let newIndex;

      if (index === 0) {
        newIndex = this.lastModuleIndex >= 0 ? 0 : undefined;
      } else {
        newIndex = index - 1 >= 0 ? index - 1 : (index + 1 <= this.lastModuleIndex ? index + 1 : undefined);
      }

      let currentModule = {};
      if (newIndex || newIndex === 0) {
        const module = this.modules[newIndex];
        const { type } = module;
        currentModule = {
          index: newIndex,
          type: `${type}-${newIndex}`,
          editComponent: `${type}_edit`
        };

        _.assign(this, {
          currentModule
        });
      }
    },

    updateEdit(params) {
      const { type, data, key, value } = params;
      const { index } = this.currentModule;
      if (type === 'swiper') {
        this.modules[index].data = data;
        return;
      }

      if (type === 'vip') {
        if (key === 'sort') {
          this.modules[index].data.items.reverse();
        }
        this.modules[index].data[key] = value;
        return;
      }

      if (type === 'coupon') {
        this.modules[index].data[key] = value;
        return;
      }

      if (type === 'poster') {
        this.modules[index].data[key] = value;
      }
    },

    handleClickSave() {
      _.forEach(this.modules, (module, index) => {
        module.moduleType = `${module.type}-${index}`;
      });
      console.log(this.modules);
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
        background-color: #f5f5f5;
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
