<template>
  <div class="app-find clearfix" :style="{ height: getMaxHeight + 'px' }">
    <div class="app-find__left pull-left ml24" :style="{ transform: 'scale(' + getScale + ')' }">
      <img class="app-find-box" src="/static-dist/app/img/vue/phone.png">
      <div class="app-find-container">
        <find-head :top-header="false" />
        <div class="app-find-container-scroll">
          <component
            v-for="(module, index) in modules"
            :key="index"
            :is="module.type"
            :module-data="module.data"
            :module-type="`${module.type}-${index}`"
          />
        </div>
        <find-footer />
      </div>
    </div>
    <div class="app-find__right pull-left">
      <h4>{{ 'admin.mobile_setting.discovery_tips' | trans }}</h4>
      <a-button class="mt16" type="primary" @click="handleClick">{{ 'site.btn.edit' | trans }}</a-button>
    </div>
  </div>
</template>

<script>
import { Pages } from 'common/vue/service';

import FindHead from './components/FindHead.vue';
import FindFooter from './components/FindFooter.vue';
import slide_show from './components/Swiper/index.vue';
import vip from './components/Vip/index.vue';
import coupon from './components/Coupon/index.vue';
import poster from './components/Poster/index.vue';
import graphic_navigation from './components/GraphicNavigation/index.vue';
import course_list from './components/CourseList/index.vue';
import classroom_list from './components/ClassroomList/index.vue';
import open_course_list from './components/OpenCourseList/index.vue';
import item_bank_exercise from './components/ItemBankExercise/index.vue';
import announcement from './components/Announcement/index.vue';
import information from './components/Information/index.vue';

export default {
  components: {
    FindHead,
    FindFooter,
    slide_show,
    vip,
    coupon,
    poster,
    graphic_navigation,
    course_list,
    classroom_list,
    open_course_list,
    item_bank_exercise,
    announcement,
    information
  },

  data() {
    return {
      modules: []
    }
  },

  computed: {
    getMaxHeight() {
      const height = $(window).height() - 230;
      const maxHeight = 800;
      return height > maxHeight ? maxHeight : height;
    },

    getScale() {
      return this.getMaxHeight / 867;
    }
  },

  mounted() {
    this.fetchDiscovery();
  },

  methods: {
    async fetchDiscovery() {
      const params = {
        params: { mode: 'published' }
      };
      const data = await Pages.appsDiscovery(params);
      this.modules = Object.values(data);
    },

    handleClick() {
      window.open('/admin/v2/setting/app_decorate');
    }
  }
}
</script>

<style lang="less" scoped>
.app-find {
  overflow: hidden;

  &__left {
    position: relative;
    width: 434px;
    transform-origin: left top;

    .app-find-box {
      width: 100%;
      height: 100%;
    }

    .app-find-container {
      position: absolute;
      width: 375px;
      top: 60px;
      bottom: 60px;
      left: 30px;

      .app-find-container-scroll {
        overflow-y: auto;
        height: calc(100% - 50px - 40px);
        background-color: #f5f7fa;

        &::-webkit-scrollbar {
          display: none;
        }
      }
    }
  }

  &__right {
    margin-top: 100px;
    width: calc(100% - 480px);
  }
}
</style>
