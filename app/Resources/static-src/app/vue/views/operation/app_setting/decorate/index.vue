<template>
  <div class="decorate-container">
    <the-header
      :preview="preview"
      @save="handleClickSave"
      @preview="handleClickPreview"
    />

    <div class="decorate-main clearfix">
      <left-choose-container
        :preview="preview"
        @add-module="addModule"
        :coupon-enabled="couponEnabled"
        :vip-enabled="vipEnabled"
      />

      <section ref="previewContainer" class="center-preview-container pull-left" :class="{ 'center-preview-container--py0': preview }">
        <div ref="mainContainer" class="main-preview-container" :class="{ 'main-preview-container--scalc': preview }">
          <img v-show="preview" class="app-find-box" src="/static-dist/app/img/vue/phone.png">

          <find-head :top-header="!preview" />

          <div :class="{ 'preview-container-scroll': preview }">
            <draggable
              v-model="modules"
              v-bind="dragOptions"
              @start="draggableStart"
              @end="draggableEnd"
            >
              <transition-group type="transition" :name="!drag ? 'flip-list' : null">
                <component
                  v-for="(module, index) in modules"
                  :key="module.oldKey"
                  :is="module.type"
                  :module-data="module.data"
                  :module-type="`${module.type}-${index}`"
                  :current-module-type="currentModule.type"
                  :is-first="index === 0"
                  :is-last="index === lastModuleIndex"
                  :preview="preview"
                  :validator-result="module.validatorResult"
                  @click.native="changeCurrentModule(module, index)"
                  @event-actions="handleClickActions"
                />
              </transition-group>
            </draggable>
          </div>
          <find-footer />
        </div>
      </section>

      <aside class="right-edit-container pull-left" :class="{ 'right-edit-container--blank': preview }">
        <component
          v-show="!preview"
          v-if="currentModule.editComponent"
          :key="currentModule.index"
          :is="currentModule.editComponent"
          :module-data="modules[currentModule.index].data"
          @update-edit="updateEdit"
        />
      </aside>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import ModuleCounter from 'app/vue/utils/module-counter.js';
import { Vip, Pages, Setting } from 'common/vue/service';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';

import { DefaultData } from './default-data';

import Draggable from 'vuedraggable';
import TheHeader from './components/TheHeader.vue';
import LeftChooseContainer from './components/LeftChooseContainer/index.vue';

import FindHead from '../components/FindHead.vue';
import FindFooter from '../components/FindFooter.vue';
import slide_show from '../components/Swiper/index.vue';
import slide_show_edit from './components/SwiperEdit/index.vue';
import vip from '../components/Vip/index.vue';
import vip_edit from './components/VipEdit/index.vue';
import coupon from '../components/Coupon/index.vue';
import coupon_edit from './components/CouponEdit/index.vue';
import poster from '../components/Poster/index.vue';
import poster_edit from './components/PosterEdit/index.vue';
import graphic_navigation from '../components/GraphicNavigation/index.vue';
import graphic_navigation_edit from './components/GraphicNavigationEdit/index.vue';
import course_list from '../components/CourseList/index.vue';
import course_list_edit from './components/CourseListEdit/index.vue';
import classroom_list from '../components/ClassroomList/index.vue';
import classroom_list_edit from './components/ClassroomListEdit/index.vue';
import open_course_list from '../components/OpenCourseList/index.vue';
import open_course_list_edit from './components/OpenCourseListEdit/index.vue';
import item_bank_exercise from '../components/ItemBankExercise/index.vue';
import item_bank_exercise_edit from './components/ItemBankExerciseEdit/index.vue';
import announcement from '../components/Announcement/index.vue';
import announcement_edit from './components/AnnouncementEdit/index.vue';
import information from '../components/Information/index.vue';
import information_edit from './components/InformationEdit/index.vue';

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
    graphic_navigation_edit,
    course_list,
    course_list_edit,
    classroom_list,
    classroom_list_edit,
    open_course_list,
    open_course_list_edit,
    item_bank_exercise,
    item_bank_exercise_edit,
    announcement,
    announcement_edit,
    information,
    information_edit,
  },

  data() {
    return {
      modules: [],
      currentModule: {},
      drag: false,
      typeCount: new ModuleCounter(),
      vipLevels: [],
      validatorResult: true,
      alreadyMessage: false,
      couponEnabled: null,
      vipEnabled: null,
      preview: false
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

  mounted() {
    this.fetchDiscovery();
    this.fetchVipSetting();
    this.fetchCouponSetting();
  },

  methods: {
    async fetchDiscovery() {
      const params = {
        params: { mode: 'published' }
      };
      const data = await Pages.appsDiscovery(params);
      const modules = Object.values(data);

      _.forEach(modules, (module, index) => {
        module.oldKey = index;
      });

      this.modules = modules;
      this.moduleCountInit();
    },

    async fetchVipSetting() {
      if (!_.size(state.vipSetting)) {
        const vipSetting = await Setting.get('vip');
        mutations.setVipSetting(vipSetting);
      };
      this.vipEnabled = !!state.vipSetting.enabled;
    },

    async fetchCouponSetting() {
      if (!_.size(state.couponSetting)) {
        const couponSetting = await Setting.get('coupon');
        mutations.setCouponSetting(couponSetting);
      };
      this.couponEnabled = !!state.couponSetting.enabled;
    },

    scrollBottom() {
      const top = this.$refs.mainContainer.clientHeight;
      this.$refs.previewContainer.scrollTo({ top: top, behavior: 'smooth' });
    },

    // 模块类型计数初始化
    moduleCountInit() {
      _.forEach(this.modules, item => {
        this.typeCount.addByType(item.type);
      });
    },

    addModule(type) {
      if (this.typeCount.getCounterByType(type) >= 5) {
        this.$message.warning(Translator.trans('message.add_up_to'));
        return;
      }

      if (type === 'vip' && !this.vipEnabled) {
        this.$confirm({
          title: Translator.trans('message.membership_function_is_not_activated'),
          okText: Translator.trans('message.go_to_open'),
          cancelText: Translator.trans('site.cancel'),
          onOk() {
            window.open('/admin/v2/setting/vip');
          }
        });
        return;
      }

      if (type === 'coupon' && !this.couponEnabled) {
        this.$confirm({
          title: Translator.trans('message.coupon_function_is_not_activated'),
          okText: Translator.trans('message.go_to_open'),
          cancelText: Translator.trans('site.cancel'),
          onOk() {
            window.open('/admin/v2/coupon');
          }
        });
        return;
      }

      const info = _.cloneDeep(DefaultData[type]);

      if (type === 'vip') {
        this.getVipLevels();
        const tempLevels = [...this.vipLevels];
        info.data.items = tempLevels;
      }

      info.oldKey = _.size(this.modules);
      this.modules.push(info);
      this.typeCount.addByType(type);
      this.changeCurrentModule(info, _.size(this.modules) - 1);
      // 滚动到底部
      clearInterval(this.timer);
      this.timer = null;
      this.timer = setTimeout(() => {
        this.scrollBottom();
      }, 500);
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

    draggableStart() {
      this.drag = true;
      this.currentModule = {};
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
      this.typeCount.removeByType(this.modules[index].type);

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
      const { type, key, value, index } = params;
      const currentIndex = this.currentModule.index;

      if (type === 'swiper') {
        if (key === 'add') {
          this.modules[currentIndex].data.push(value);
          return;
        }

        if (key === 'edit') {
          this.modules[currentIndex].data[index].image = value;
          return;
        }

        if (key === 'remove') {
          this.modules[currentIndex].data.splice(index, 1);
          return;
        }

        if (key === 'drag') {
          this.modules[currentIndex].data = value;
          return;
        }

        this.modules[currentIndex].data[index][key] = value;
        return;
      }

      if (type === 'vip' && key === 'sort') {
        this.modules[currentIndex].data.items.reverse();
        return;
      }

      if (type === 'graphic_navigation') {
        if (key === 'add') {
          this.modules[currentIndex].data.push(value);
          return;
        }

        if (key === 'remove') {
          this.modules[currentIndex].data.splice(index, 1);
          return;
        }

        if (key === 'type') {
          this.modules[currentIndex].data[index].link.type = value;
          this.modules[currentIndex].data[index].link.conditions = {};
          this.modules[currentIndex].data[index].link.categoryId = undefined;
          return;
        }

        if (key === 'conditions') {
          this.modules[currentIndex].data[index].link.categoryId = value.categoryId;
          this.modules[currentIndex].data[index].link.conditions = value;
          return;
        }

        this.modules[currentIndex].data[index][key] = value;
        return;
      }

      const types = [
        'course_list',
        'classroom_list',
        'item_bank_exercise',
        'open_course_list',
        'poster',
        'coupon',
        'vip'
      ];

      if (_.includes(types, type)) {
        if (key === 'categoryId') {
          this.modules[currentIndex].data[key] = value[value.length - 1];
          this.modules[currentIndex].data.categoryIds = value;
          return;
        }
        this.modules[currentIndex].data[key] = value;
      }
    },

    moduleValidator(module) {
      const { data, type } = module;
      // 轮播图
      if (type === 'slide_show') {
        const length = _.size(data);

        if (!length) {
          if (!this.alreadyMessage) this.$message.error(Translator.trans('message.carousel_module_information'));
          return false;
        }

        _.forEach(data, (item, index) => {
          const { uri } = item.image;
          if (!uri) {
            if (!this.alreadyMessage) this.$message.error(Translator.trans('message.carousel_module_information'));
            return false;
          }
        });
        return true;
      }

      // 课程、班级
      if (_.includes(['course_list', 'classroom_list', 'open_course_list', 'item_bank_exercise'], type)) {
        const messages = {
          course_list: 'message.course_module_information',
          classroom_list: 'message.classroom_module_information',
          open_course_list: 'message.open_class_module_information',
          item_bank_exercise: 'message.qusetion_bank_module_information'
        };

        const { title, sourceType, items } = data;
        const length = _.size(items);

        if (!title || (sourceType === 'custom' && !length)) {
          if (!this.alreadyMessage) this.$message.error(Translator.trans(messages[type]));
          return false;
        }
        return true;
      }

      // 图片广告
      if (type === 'poster') {
        const { uri } = data.image;
        if (!uri) {
          if (!this.alreadyMessage) this.$message.error(Translator.trans('message.advertising_module_information'));
          return false;
        }
        return true;
      }

      // 图文导航
      if (type === 'graphic_navigation') {
        _.forEach(data, (item, index) => {
          const { title, image: { uri }, link: { type } } = item;
          if (!title || !uri || !type) {
            if (!this.alreadyMessage) this.$message.error(Translator.trans('message.gn_module_information'));
            return false;
          }
        });
        return true;
      }

       // 优惠券
      if (type === 'coupon') {
        const length = _.size(data.items);

        if (!length) {
          if (!this.alreadyMessage) this.$message.error(Translator.trans('message.coupon_module_information'));
          return false;
        }
        return true;
      }

      return true;
    },

    async handleClickSave() {
      this.alreadyMessage = false;
      this.validatorResult = true;
      const data = {};
      _.forEach(this.modules, (module, index) => {
        const result = this.moduleValidator(module);
        if (!result) {
          this.alreadyMessage = true;
          this.validatorResult = false;
        }
        this.$set(module, 'validatorResult', result);
        const moduleType = `${module.type}-${index}`;
        module.moduleType = moduleType;
        data[moduleType] = module;
      });

      // 校验不通过
      if (!this.validatorResult) return;

      const params = {
        params: {
          type: 'discovery',
          mode: 'published'
        },
        data
      };

      try {
        await Pages.appsSettings(params);
        this.$message.success(Translator.trans('message.saved_successfully'));
        window.location.href = '/admin/v2/setting/mobile_discoveries';
      } catch (errpr) {
        this.$message.error(Translator.trans('message.save_failed'));
      }
    },

    handleClickPreview(value) {
      this.preview = value;
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

      &--py0 {
        padding-top: 0;
        padding-bottom: 0;
      }

      &::-webkit-scrollbar {
        display: none;
      }

      .preview-container-scroll {
        overflow-y: auto;
        height: 658px;

        &::-webkit-scrollbar {
          display: none;
        }
      }

      .main-preview-container {
        position: relative;
        padding-bottom: 50px;
        margin: 0 auto;
        width: 375px;
        min-height: 658px;
        box-shadow: 0px 1px 3px 1px rgba(0, 0, 0, 0.05);
        background-color: #f5f5f5;

        .app-find-box {
          position: absolute;
          left: -29px;
          top: -64px;
          width: 432px;
          height: 878px;
          z-index: -1;
        }

        &--scalc {
          transform: scale(0.8);
        }
      }
    }

    .right-edit-container {
      overflow-y: auto;
      width: 384px;
      height: 100%;
      background-color: #fff;

      &--blank {
        background-color: #f5f7fa;
      }
    }
  }
}
</style>
