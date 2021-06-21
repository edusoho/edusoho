<template>
  <div>
    <a-input-search
      allowClear
      placeholder="请输入课程名称"
      style="width: 224px; margin-top: 16px;"
      @search="onSearch"
    />

    <list-item v-for="question in questionList" :key="question.id" :question="question" />

    <a-pagination
      class="text-center"
      style="margin-top: 16px;"
      :hide-on-single-page="true"
      v-model="pagination.current"
      :total="pagination.total"
      @change="onChange"
    />
  </div>
</template>

<script>
import { Me } from 'common/vue/service/index.js';
import ListItem from './ListItem.vue';

export default {
  components: {
    ListItem
  },

  data() {
    return {
      pagination: {},
      keyWord: '',
      questionList: []
    }
  },

  created() {
    this.fetchWrongBooksCertainTypes();
  },

  methods: {
    onSearch(value) {
      console.log(value);
    },

    onChange(current) {
      console.log(current);
    },

    async fetchWrongBooksCertainTypes() {
      const { data, paging } = await Me.getWrongBooksCertainTypes({
        targetType: 'course',
        keyWord: this.keyWord
      });

      this.questionList = data;
      // this.pagination = {
      //   total: paging.total
      // };
    }
  }
}
</script>
