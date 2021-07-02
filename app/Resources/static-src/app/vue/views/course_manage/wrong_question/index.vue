<template>
  <div>
    <search />

    <student-wrong-question-table
      class="mt24"
      :data="wrongQuestionList"
      :pagination="pagination"
      :loading="loading"
      @event-communication="eventCommunication"
    />

    <view-detail-modal :visible="visible" />
  </div>
</template>

<script>
import { WrongBookStudentWrongQuestion } from 'common/vue/service/index.js';
import Search from './Search.vue';
import ViewDetailModal from './ViewDetailModal.vue';
import StudentWrongQuestionTable from 'app/vue/views/components/WrongQuestion/StudentWrongQuestionTable.vue';

export default {
  name: 'CourseManageWrongQuestion',

  components: {
    Search,
    ViewDetailModal,
    StudentWrongQuestionTable
  },

  data() {
    return {
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        total: 0
      },
      loading: false,
      wrongQuestionList: [],
      visible: false,
      wrongQuestionId: '0'
    }
  },

  created() {
    this.fetchWrongQuestion();
  },

  methods: {
    onSearch() {
      this.$refs.form.validate(valid => {
        if (valid) {
          // do
        }
      })
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchWrongQuestion();
    },

    async fetchWrongQuestion() {
      this.loading = true;

      const apiParams = {
        query: {
          targetId: 72,
          targetType: 'course'
        },
        params: {
          offset: (this.pagination.current - 1) * 10,
          limit: 10
        }
      };

      const { data, paging } = await WrongBookStudentWrongQuestion.get(apiParams);

      this.loading = false;
      this.wrongQuestionList = data;
      this.pagination.total = paging.total;
    },

    handleClickViewDetails(data) {
      this.wrongQuestionId = data.itemId;
      this.visible = true;
    },

    eventCommunication(params) {
      const { type, data } = params;

      if (type === 'table-pagination') {
        this.handleTableChange(data);
        return;
      }

      if (type === 'click-view-detail') {
        this.handleClickViewDetails(data);
        return;
      }

      if (type === 'modal-cancel') {
        this.visible = false;
      }
    }
  }
}
</script>
