<template>
  <edit-layout>
    <template #title>{{ 'decorate.open_class_list_settings' | trans }}</template>

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
          style="width: 240px;"
          :default-value="moduleData.limitDays"
          @change="(value) => handleChange({ key: 'limitDays', value })"
        >
          <a-select-option key="7">{{ 'decorate.last_7_days' | trans }}</a-select-option>
          <a-select-option key="30">{{ 'decorate.last_30_days' | trans }}</a-select-option>
          <a-select-option key="90">{{ 'decorate.last_90_days' | trans }}</a-select-option>
          <a-select-option key="0">{{ 'decorate.all' | trans }}</a-select-option>
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

      <div class="design-editor__tips">
        <div>·{{ 'decorate.open_course_tip1' | trans }}</div>
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
  name: 'OpenCourseListEdit',

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
        type: 'open_course_list',
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
