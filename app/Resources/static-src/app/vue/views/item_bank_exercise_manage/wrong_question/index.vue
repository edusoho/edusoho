<template>
  <div>
    <search :target-id="targetId" @on-search="onSearch"/>

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
      :current-order="currentOrder"
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
  name: 'ItemBankExerciseManageWrongQuestion',

  components: {
    Search,
    ViewDetailModal,
    StudentWrongQuestionTable
  },

  data() {
    return {
      targetType: 'exercise',
      targetId: $('.js-item-bank-id').val(),
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        total: 0
      },
      loading: false,
      wrongQuestionList: [],
      visible: false,
      currentId: '0',
      currentOrder: 0
    }
  },

  created() {
    this.fetchWrongQuestion();
  },

  methods: {
    onSearch(params = {}) {
      this.fetchWrongQuestion(params);
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchWrongQuestion();
    },

    async fetchWrongQuestion(params = {}) {
      this.loading = true;

      const apiParams = {
        query: {
          targetId: this.targetId,
          targetType: this.targetType
        },
        params: Object.assign({
          offset: (this.pagination.current - 1) * 10,
          limit: 10
        }, params),
      };

      const { data, paging } = await WrongBookStudentWrongQuestion.get(apiParams);

      this.loading = false;
      this.wrongQuestionList = data;
      this.pagination.total = paging.total;
    },

    handleClickViewDetails(data) {
      this.currentId = data.id;
      this.currentOrder = data.order;
      this.visible = true;
    },

    eventCommunication(params) {
      const { type, data } = params;

      if (type === 'pagination') {
        this.handleTableChange(data);
        return;
      }

      if (type === 'view-detail') {
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
