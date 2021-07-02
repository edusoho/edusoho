<template>
  <a-modal
    title="详情"
    width="900px"
    :visible="visible"
    :footer="null"
    @cancel="handleCancel"
  >
    <question :question="question" />

    <detail-table
      :data="answerDetails"
      :loading="loading"
      :pagination="pagination"
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
    }
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
        params: {
          targetId: this.targetId
        }
      };
      const { data, item, paging } = await WrongBookWrongQuestionDetail.get(apiParams);

      this.loading = false;
      this.answerDetails = data;
      this.question = item;
      this.pagination.total = Number(paging.total);
    },

    handleTableChange() {

    },

    handleCancel() {
      this.$emit('event-communication', { type: 'modal-cancel' });
    }
  }
};
</script>
