<template>
  <div>
    <a-input-search
      allowClear
      placeholder="请输入题库练习名称"
      style="width: 224px; margin-top: 16px;"
      @search="onSearch"
    />

    <div class="text-center mt20" v-if="loading">
      <a-spin />
    </div>

    <template v-else>
      <list-item
        v-for="question in questionList"
        :key="question.id"
        :question="question"
      />
    </template>

    <empty v-if="!loading && !questionList.length" />

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
  import Empty from 'app/vue/views/components/Empty.vue';

  export default {
    components: {
      ListItem,
      Empty
    },

    data() {
      return {
        loading: false,
        pagination: {
          current: 1
        },
        keyWord: '',
        questionList: []
      }
    },

    created() {
      this.fetchWrongBooksCertainTypes();
    },

    methods: {
      onSearch(value) {
        this.keyWord = value;
        this.pagination.current = 1;
        this.fetchWrongBooksCertainTypes();
      },

      onChange(current) {
        this.fetchWrongBooksCertainTypes();
      },

      async fetchWrongBooksCertainTypes() {
        this.loading = true;

        const { data, paging } = await Me.getWrongBooksCertainTypes({
          targetType: 'exercise',
          keyWord: this.keyWord,
          offset: (this.pagination.current - 1) * 2
        });

        this.pagination.total = Number(paging.total);
        this.loading = false;
        this.questionList = data;
      }
    }
  }
</script>
