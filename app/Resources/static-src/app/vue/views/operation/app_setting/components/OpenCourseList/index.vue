<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    <div class="open-course-list">
      <div class="clearfix">
        <div class="open-course-list__title pull-left text-overflow">{{ moduleData.title }}</div>
        <div class="open-course-list__more pull-right">查看更多<a-icon type="right" /></div>
      </div>

      <div v-if="list.length" :class="['open-course-container mt16', moduleType]">
        <div class="swiper-wrapper">
          <div
            v-for="(item, index) in list"
            :key="index"
            class="swiper-slide"
          >
            <div class="swiper-slide-container">
              <item :item="item" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import Swiper from 'swiper/dist/idangerous.swiper.min.js';
import 'swiper/dist/idangerous.swiper.css';
import { OpenCourse } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';
import Item from './Item.vue';

export default {
  name: 'OpenCourseList',

  mixins: [moduleMixin],

  components: {
    Item
  },

  data() {
    return {
      list: []
    }
  },

  mounted() {
    this.fetchOpenCourse();
    this.initSwiepr();
  },

  watch: {
    moduleData: {
      handler: function() {
        this.fetchOpenCourse();
      },
      deep: true
    }
  },

  methods: {
    async fetchOpenCourse() {
      const { sort, limit, limitDays, categoryId, sourceType, items } = this.moduleData;
      if (sourceType === 'custom') {
        this.list = items;
        this.reInitSwiper();
        return;
      }
      const params = {
        params: {
          sort,
          limit,
          limitDays,
          categoryId
        }
      };
      const { data } = await OpenCourse.search(params);
      this.list = data;
      this.reInitSwiper();
    },

    initSwiepr() {
      new Swiper(`.${this.moduleType}`, {
        slidesPerView: 1.05
      });
    },

    reInitSwiper() {
      this.$nextTick(() => {
        this.initSwiepr();
      });
    }
  }
}
</script>

<style lang="less" scoped>
.open-course-list {
  padding-right: 16px;
  padding-left: 16px;

  .open-course-list__title {
    position: relative;
    padding-left: 10px;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 6px;
      left: 0;
      width: 4px;
      height: 12px;
      background: #03c777;
      border-radius: 1px;
    }
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
}

.open-course-container {
  overflow: hidden;
  width: 100%;
  height: 80px;

  .swiper-slide-container {
    margin-right: 16px;
  }
}
</style>

