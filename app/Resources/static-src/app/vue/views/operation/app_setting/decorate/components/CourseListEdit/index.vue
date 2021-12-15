<template>
  <edit-layout>
    <template #title>课程列表设置</template>

    <div class="design-editor">
      <div class="design-editor__item">
        <span class="design-editor__required">列表名称：</span>
        <a-input
          placeholder="请输入列表名称"
          style="width: 200px;"
          size="small"
          allow-clear
        />
      </div>

      <div class="design-editor__item">
        <span>排列方式：</span>
        <a-select style="width: 200px;" size="small">
          <a-select-option key="1">一行一列</a-select-option>
          <a-select-option key="2">一行两列</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span>课程来源：</span>
        <a-radio-group>
          <a-radio value="show">
            课程分类
          </a-radio>
          <a-radio value="unshow">
            自定义
          </a-radio>
        </a-radio-group>
      </div>

      <div class="design-editor__item">
        <span>排列顺序：</span>
        <a-select style="width: 200px;" size="small">
          <a-select-option key="1">加入最多</a-select-option>
          <a-select-option key="2">最近创建</a-select-option>
          <a-select-option key="3">评分最高</a-select-option>
          <a-select-option key="4">推荐课程</a-select-option>
        </a-select>
      </div>

      <div class="design-editor__item">
        <span>显示个数：</span>
        <a-select style="width: 200px;" size="small">
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
  }
}
</script>
