<template>
  <edit-layout>
    <template #title>课程列表设置</template>

    <div class="design-editor">
      <div class="design-editor__item">
        <span class="design-editor__required">列表名称：</span>
        <a-input
          placeholder="请输入列表名称"
          style="width: 250px;"
          size="small"
          :default-value="moduleData.title"
          allow-clear
          @change="(e) => handleChange({ key: 'title', value: e.target.value })"
        />
      </div>

      <div class="design-editor__item">
        <span>排列方式：</span>
        <a-select
          style="width: 250px;"
          :default-value="moduleData.displayStyle"
          size="small"
          @change="(value) => handleChange({ key: 'displayStyle', value })"
        >
          <a-select-option key="row">一行一列</a-select-option>
          <a-select-option key="distichous">一行两列</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span>课程来源：</span>
        <a-radio-group
          :default-value="moduleData.sourceType"
          @change="(e) => handleChange({ key: 'sourceType', value: e.target.value })"
        >
          <a-radio value="condition">
            课程分类
          </a-radio>
          <a-radio value="custom">
            自定义
          </a-radio>
        </a-radio-group>
      </div>

      <div class="design-editor__item">
        <span>排列顺序：</span>
        <a-select
          :style="{ width: showLastDays ? '124px' : '250px' }"
          size="small"
          :default-value="moduleData.sort"
          @change="(value) => handleChange({ key: 'sort', value })"
        >
          <a-select-option key="-studentNum">加入最多</a-select-option>
          <a-select-option key="-createdTime">最近创建</a-select-option>
          <a-select-option key="-rating">评分最高</a-select-option>
          <a-select-option key="recommendedSeq">推荐课程</a-select-option>
        </a-select>
        <a-select
          v-if="showLastDays"
          style="width: 124px;"
          size="small"
          :default-value="moduleData.lastDays"
          @change="(value) => handleChange({ key: 'lastDays', value })"
        >
          <a-select-option key="7">最近 7 天</a-select-option>
          <a-select-option key="30">最近 30 天</a-select-option>
          <a-select-option key="90">最近 90 天</a-select-option>
          <a-select-option key="0">历史所有</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span>显示个数：</span>
        <a-select
          style="width: 250px;"
          size="small"
          :default-value="moduleData.limit"
          @change="(value) => handleChange({ key: 'limit', value })"
        >
          <a-select-option v-for="item in 8" :key="item">{{ item  }}</a-select-option>
        </a-select>
      </div>
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'CourseListEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  },

  computed: {
    showLastDays() {
      const { sort } = this.moduleData;
      return _.includes(['-studentNum', '-rating'], sort);
    }
  },

  methods: {
    handleChange(params) {
      this.$emit('update-edit', {
        type: 'course_list',
        ...params
      });
    }
  }
}
</script>
