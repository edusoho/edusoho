<template>
  <edit-layout>
    <template #title>{{ 'decorate.question_bank_list_settings' | trans }}</template>

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
        <span class="design-editor__label">{{ 'decorate.question_bank_classification' | trans }}：</span>
        <a-cascader
          style="width: 240px;"
          :options="options"
          change-on-select
          :default-value="moduleData.categoryIds"
          :field-names="{ label: 'name', value: 'id', children: 'children' }"
          @change="(value) => handleChange({ key: 'categoryId', value })"
        />
      </div>

      <div class="design-editor__item">
        <span class="design-editor__label">{{ 'decorate.order' | trans }}：</span>
        <a-select
          style="width: 116px;"
          :default-value="moduleData.sort"
          @change="(value) => handleChange({ key: 'sort', value })"
        >
          <a-select-option key="-studentNum">{{ 'decorate.join_the_most' | trans }}</a-select-option>
          <a-select-option key="-createdTime">{{ 'decorate.recently_created' | trans }}</a-select-option>
          <a-select-option key="-rating">{{ 'decorate.highest_rated' | trans }}</a-select-option>
          <a-select-option key="recommendedSeq">{{ 'decorate.recommended_courses' | trans }}</a-select-option>
        </a-select>
        <a-select
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

      <div class="design-editor__item">
        <span class="design-editor__label">{{ 'decorate.display_number' | trans }}：</span>
        <span style="font-size: 14px; line-height: 22px; color: #999;">{{ 'decorate.itemBankMax' | trans }}</span>
      </div>
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';
import { ItemBankCategory } from 'common/vue/service/index.js';

export default {
  name: 'ItemBankExerciseEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  },

  data() {
    return {
      options: []
    }
  },

  mounted() {
    this.fetchCategories();
  },

  methods: {
    handleChange(params) {
      this.$emit('update-edit', {
        type: 'item_bank_exercise',
        ...params
      });
    },

    async fetchCategories() {
      if (!_.size(state.itemBankCategories)) {
        const data = await ItemBankCategory.get();
        mutations.setItemBankCategories(data);
      };
      this.options = state.itemBankCategories;
    }
  }
}
</script>
