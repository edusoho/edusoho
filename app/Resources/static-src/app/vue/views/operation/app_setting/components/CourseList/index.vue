<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="course-list">
      <div class="clearfix">
        <div class="course-list__title pull-left text-overflow">{{ moduleData.title }}</div>
        <div class="course-list__more pull-right">{{ 'site.btn.see_more' | trans }}<a-icon type="right" /></div>
      </div>

      <div :class="{ clearfix: moduleData.displayStyle === 'distichous' }">
        <component
          :is="currentComponent"
          v-for="item in list"
          :key="item.id"
          :item="item"
        />
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { Course } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';
import ColumnItem from './ColumnItem.vue';
import RowItem from './RowItem.vue';

export default {
  name: 'CourseList',

  mixins: [moduleMixin],

  computed: {
    currentComponent() {
      const { displayStyle } = this.moduleData;
      return displayStyle === 'distichous' ? ColumnItem : RowItem;
    }
  },

  data() {
    return {
      list: []
    }
  },

  mounted() {
    this.fetchCourse();
  },

  watch: {
    moduleData: {
      handler: function() {
        this.fetchCourse();
      },
      deep: true
    }
  },

  methods: {
    async fetchCourse() {
      const { sort, limit, lastDays, categoryId, sourceType, items } = this.moduleData;
      if (sourceType === 'custom') {
        this.list = items;
        return;
      }
      const params = {
        sort,
        limit,
        lastDays,
        categoryId
      };
      const { data } = await Course.searchCourses(params);
      this.list = data;
    }
  }
}
</script>

<style lang="less" scoped>
.course-list {
  padding-right: 16px;
  padding-left: 16px;

  .course-list__title {
    position: relative;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
}
</style>

