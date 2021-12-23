<template>
  <edit-layout>
    <template #title>{{ 'decorate.course_list_setting' | trans }}</template>

    <div class="design-editor">
      <div class="design-editor__item">
        <span class="design-editor__label design-editor__required">{{ 'decorate.list_name' | trans }}：</span>
        <a-input
          :placeholder="'decorate.please_enter_the_name_of_the_list' | trans"
          style="width: 240px;"
          :default-value="moduleData.title"
          allow-clear
          @change="(e) => handleChange({ key: 'title', value: e.target.value })"
        />
      </div>

      <div class="design-editor__item">
        <span class="design-editor__label">{{ 'decorate.arrangement' | trans }}：</span>
        <a-select
          style="width: 240px;"
          :default-value="moduleData.displayStyle"
          @change="(value) => handleChange({ key: 'displayStyle', value })"
        >
          <a-select-option key="row">{{ 'decorate.row_by_column' | trans }}</a-select-option>
          <a-select-option key="distichous">{{ 'decorate.one_row_and_two_columns' | trans }}</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span class="design-editor__label">{{ 'decorate.course_source' | trans }}：</span>
        <a-radio-group
          :default-value="moduleData.sourceType"
          @change="(e) => handleChange({ key: 'sourceType', value: e.target.value })"
        >
          <a-radio value="condition">
            {{ 'decorate.course_sorts' | trans }}
          </a-radio>
          <a-radio value="custom">
            {{ 'decorate.customize' | trans }}
          </a-radio>
        </a-radio-group>
      </div>

      <div v-show="moduleData.sourceType === 'custom'" class="design-editor__item">
        <span class="design-editor__label design-editor__required">{{ 'decorate.course_sorts' | trans }}：</span>
        <a-button @click="handleSelect">{{ 'decorate.choose_a_course' | trans }}</a-button>
      </div>

      <div v-show="moduleData.sourceType === 'custom'" class="design-editor__item">
        <draggable
          class="course-list"
          v-model="moduleData.items"
          v-bind="dragOptions"
          @start="drag = true"
          @end="drag = false"
        >
          <transition-group type="transition" :name="!drag ? 'flip-list' : null">
            <div class="course-list__item" v-for="item in moduleData.items" :key="item.id">
              <a-icon type="drag" style="color: #999;" /> {{ item.title || item.courseSetTitle }}
            </div>
          </transition-group>
        </draggable>
      </div>

      <div class="design-editor__item" v-show="moduleData.sourceType === 'condition'">
        <span class="design-editor__label">{{ 'decorate.course_sorts' | trans }}：</span>
        <a-cascader
          style="width: 240px;"
          :options="options"
          change-on-select
          :default-value="[moduleData.categoryId]"
          :field-names="{ label: 'name', value: 'id', children: 'children' }"
          @change="(value) => handleChange({ key: 'categoryId', value: value[value.length - 1] })"
        />
      </div>

      <div class="design-editor__item" v-show="moduleData.sourceType === 'condition'">
        <span class="design-editor__label">{{ 'decorate.order' | trans }}：</span>
        <a-select
          :style="{ width: showLastDays ? '116px' : '240px' }"
          :default-value="moduleData.sort"
          @change="(value) => handleChange({ key: 'sort', value })"
        >
          <a-select-option key="-studentNum">{{ 'decorate.join_the_most' | trans }}</a-select-option>
          <a-select-option key="-createdTime">{{ 'decorate.recently_created' | trans }}</a-select-option>
          <a-select-option key="-rating">{{ 'decorate.highest_rated' | trans }}</a-select-option>
          <a-select-option key="recommendedSeq">{{ 'decorate.recommended_courses' | trans }}</a-select-option>
        </a-select>
        <a-select
          v-show="showLastDays"
          style="width: 120px;"
          :default-value="moduleData.lastDays"
          @change="(value) => handleChange({ key: 'lastDays', value })"
        >
          <a-select-option key="7">{{ 'decorate.last_7_days' | trans }}</a-select-option>
          <a-select-option key="30">{{ 'decorate.last_30_days' | trans }}</a-select-option>
          <a-select-option key="90">{{ 'decorate.last_90_days' | trans }}</a-select-option>
          <a-select-option key="0">{{ 'decorate.history' | trans }}</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item" v-show="moduleData.sourceType === 'condition'">
        <span class="design-editor__label">{{ 'decorate.display_number' | trans }}：</span>
        <a-select
          style="width: 240px;"
          :default-value="moduleData.limit"
          @change="(value) => handleChange({ key: 'limit', value })"
        >
          <a-select-option v-for="item in 8" :key="item">{{ item  }}</a-select-option>
        </a-select>
      </div>
    </div>

    <selete-course-modal ref="modal" @update-items="handleUpdateItems" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Draggable from 'vuedraggable';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';
import { Categories } from 'common/vue/service/index.js';
import SeleteCourseModal from './SeleteCourseModal.vue';

export default {
  name: 'CourseListEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout,
    SeleteCourseModal,
    Draggable
  },

  data() {
    return {
      options: [],
      drag: false
    }
  },

  computed: {
    showLastDays() {
      const { sort } = this.moduleData;
      return _.includes(['-studentNum', '-rating'], sort);
    },

    isCustom() {
      const { sourceType } = this.moduleData;
      return sourceType === 'custom';
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
    this.fetchCategories();
  },

  methods: {
    handleChange(params) {
      this.$emit('update-edit', {
        type: 'course_list',
        ...params
      });
    },

    handleUpdateItems(value) {
      this.handleChange({
        key: 'items',
        value
      });
    },

    handleSelect() {
      this.$refs.modal.showModal();
    },

    async fetchCategories() {
      if (!_.size(state.courseCategories)) {
        const data = await Categories.get({ query: { type: 'course' }});
        mutations.setCourseCategories(data);
      };
      this.options = state.courseCategories;
    }
  }
}
</script>

<style lang="less" scoped>
.course-list {
  padding-right: 8px;
  padding-left: 8px;
  background: rgba(237, 237, 237, 0.53);

  &__item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    cursor: move;

    &:last-child {
      border-bottom: none;
    }
  }
}
</style>
