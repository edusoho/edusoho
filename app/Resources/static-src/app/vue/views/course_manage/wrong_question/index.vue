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

    <view-detail-modal
      v-if="visible"
      :visible="visible"
      :current-id="currentId"
      :target-type="targetType"
      :target-id="targetId"
      @event-communication="eventCommunication"
    />
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
      targetType: 'course',
      targetId: '72',
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        total: 0
      },
      loading: false,
      wrongQuestionList: [],
      visible: false,
      currentId: '0'
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
          targetId: this.targetId,
          targetType: this.targetType
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
      this.currentId = data.itemId;
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
