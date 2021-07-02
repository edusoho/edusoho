<template>
  <a-modal
    title="详情"
    width="900px"
    :visible="visible"
    :footer="null"
    :destroyOnClose="true"
    @cancel="handleCancel"
  >
    <detail-table
      :data="data"
      :loading="loading"
      :pagination="pagination"
    />
  </a-modal>
</template>

<script>
import { WrongBookWrongQuestionDetail } from 'common/vue/service';
import DetailTable from 'app/vue/views/components/WrongQuestion/DetailTable.vue';

export default {
  name: 'ViewDetailsModal',

  components: {
    DetailTable
  },

  props: {
    visible: {
      type: Boolean,
      required: true
    }
  },

  data() {
    return {
      data: [{
        order: 0,
        usernick: '用户名',
        time: '答题时间',
        result: '答题结果'
      }],
      pagination: {
        hideOnSinglePage: true
      },
      loading: false
    };
  },

  created() {
    this.fetchWrongQuestionDetail();
  },

  methods: {
    async fetchWrongQuestionDetail() {
      const apiParams = {
        query: {
          itemId: this.wrongQuestionId,
          targetType: 'course'
        },
        params: {
          targetId: 72
        }
      };
      const result = await WrongBookWrongQuestionDetail.get(apiParams);
      console.log(result);
    },

    handleTableChange() {

    },

    handleCancel() {
      this.$emit('event-communication', { type: 'modal-cancel' });
    }
  }
};
</script>
