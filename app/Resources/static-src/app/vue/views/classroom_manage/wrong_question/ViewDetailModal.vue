<template>
  <a-modal
    title="详情"
    width="900px"
    :visible="visible"
    :footer="null"
    @cancel="handleCancel"
  >
    <question :question="question" :order="currentOrder" />

    <detail-table
      :data="answerDetails"
      :loading="loading"
      :pagination="pagination"
      @event-communication="eventCommunication"
    />
  </a-modal>
</template>

<script>
import { WrongBookWrongQuestionDetail } from 'common/vue/service';
import DetailTable from 'app/vue/views/components/WrongQuestion/DetailTable.vue';
import Question from 'app/vue/views/components/WrongQuestion/Question/index.vue';

export default {
  name: 'ViewDetailsModal',

  components: {
    DetailTable,
    Question
  },

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    targetType: {
      type: String,
      required: true
    },

    targetId: {
      type: String,
      required: true
    },

    currentId: {
      type: String,
      required: true
    },

    currentOrder: {
      type: Number,
      required: true
    },

    searchParams: {}
  },

  data() {
    return {
      answerDetails: [],
      question: {},
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        total: 0
      },
      loading: false
    };
  },

  created() {
    this.fetchWrongQuestionDetail();
  },

  methods: {
    async fetchWrongQuestionDetail() {
      this.loading = true;

      const apiParams = {
        query: {
          itemId: this.currentId,
          targetType: this.targetType
        },
        params: Object.assign({
          targetId: this.targetId,
          offset: (this.pagination.current - 1) * 10,
          limit: 10
        }, this.searchParams)
      };
      const { data, item, paging } = await WrongBookWrongQuestionDetail.get(apiParams);

      this.loading = false;
      this.answerDetails = data;
      this.question = item;
      this.pagination.total = Number(paging.total);
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchWrongQuestionDetail();
    },

    handleCancel() {
      this.$emit('event-communication', { type: 'modal-cancel' });
    },

    eventCommunication(params) {
      const { type, data } = params;

      if (type === 'pagination') {
        this.handleTableChange(data);
      }
    }
  }
};
</script>
