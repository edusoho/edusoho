<template>
  <edit-layout>
    <template #title>{{ 'decorate.class_list_settings' | trans }}</template>

    <div class="design-editor">
      <div class="design-editor__item">
        <span class="design-editor__required">{{ 'decorate.list_name' | trans }}：</span>
        <a-input
          :placeholder="'decorate.please_enter_the_name_of_the_list' | trans"
          style="width: 200px;"
          size="small"
          :default-value="moduleData.title"
          allow-clear
          @change="(e) => handleChange({ key: 'title', value: e.target.value })"
        />
      </div>

      <div class="design-editor__item">
        <span>{{ 'decorate.arrangement' | trans }}：</span>
        <a-select
          style="width: 200px;"
          :default-value="moduleData.displayStyle"
          size="small"
          @change="(value) => handleChange({ key: 'displayStyle', value })"
        >
          <a-select-option key="row">{{ 'decorate.row_by_column' | trans }}</a-select-option>
          <a-select-option key="distichous">{{ 'decorate.one_row_and_two_columns' | trans }}</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span>{{ 'decorate.class_source' | trans }}：</span>
        <a-radio-group
          :default-value="moduleData.sourceType"
          @change="(e) => handleChange({ key: 'sourceType', value: e.target.value })"
        >
          <a-radio value="condition">
            {{ 'decorate.class_classification' | trans }}
          </a-radio>
          <a-radio value="custom">
            {{ 'decorate.customize' | trans }}
          </a-radio>
        </a-radio-group>
      </div>

      <div v-show="moduleData.sourceType === 'custom'" class="design-editor__item">
        <span class="design-editor__required">{{ 'decorate.class_classification' | trans }}：</span>
        <a-button size="small" @click="handleSelect">{{ 'decorate.select_class' | trans }}</a-button>
      </div>

      <div v-show="moduleData.sourceType === 'custom'" class="design-editor__item">
        <draggable
          class="classroom-list"
          v-model="moduleData.items"
          v-bind="dragOptions"
          @start="drag = true"
          @end="drag = false"
        >
          <transition-group type="transition" :name="!drag ? 'flip-list' : null">
            <div class="classroom-list__item" v-for="item in moduleData.items" :key="item.id">
              {{ item.title || item.courseSetTitle }}
            </div>
          </transition-group>
        </draggable>
      </div>

      <div class="design-editor__item" v-show="moduleData.sourceType === 'condition'">
        <span>{{ 'decorate.class_classification' | trans }}：</span>
        <a-cascader
          style="width: 200px;"
          size="small"
          :options="options"
          change-on-select
          :default-value="[moduleData.categoryId]"
          :field-names="{ label: 'name', value: 'id', children: 'children' }"
          @change="(value) => handleChange({ key: 'categoryId', value: value[value.length - 1] })"
        />
      </div>

      <div class="design-editor__item" v-show="moduleData.sourceType === 'condition'">
        <span>{{ 'decorate.order' | trans }}：</span>
        <a-select
          :style="{ width: showLastDays ? '90px' : '200px' }"
          size="small"
          :default-value="moduleData.sort"
          @change="(value) => handleChange({ key: 'sort', value })"
        >
          <a-select-option key="-studentNum">{{ 'decorate.join_the_most' | trans }}</a-select-option>
          <a-select-option key="-createdTime">{{ 'decorate.recently_created' | trans }}</a-select-option>
          <a-select-option key="-rating">{{ 'decorate.highest_rated' | trans }}</a-select-option>
          <a-select-option key="recommendedSeq">{{ 'decorate.recommended_class' | trans }}</a-select-option>
        </a-select>
        <a-select
          v-show="showLastDays"
          style="width: 106px;"
          size="small"
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
        <span>{{ 'decorate.display_number' | trans }}：</span>
        <a-select
          style="width: 200px;"
          size="small"
          :default-value="moduleData.limit"
          @change="(value) => handleChange({ key: 'limit', value })"
        >
          <a-select-option v-for="item in 8" :key="item">{{ item  }}</a-select-option>
        </a-select>
      </div>
    </div>

    <selete-classroom-modal ref="modal" @update-items="handleUpdateItems" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Draggable from 'vuedraggable';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';
import { Categories } from 'common/vue/service/index.js';
import SeleteClassroomModal from './SeleteClassroomModal.vue';

export default {
  name: 'ClassroomListEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout,
    SeleteClassroomModal,
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
        type: 'classroom_list',
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
      if (!_.size(state.classroomCategories)) {
        const data = await Categories.get({ query: { type: 'classroom' }});
        mutations.setClassroomCategories(data);
      };
      this.options = state.classroomCategories;
    }
  }
}
</script>

<style lang="less" scoped>
.classroom-list {
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
