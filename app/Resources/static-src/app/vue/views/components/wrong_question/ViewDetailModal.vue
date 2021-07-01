<template>
  <a-modal
    title="详情"
    width="900px"
    :visible="visible"
    :footer="null"
    @cancel="handleCancel"
  >
    <a-table
      :columns="columns"
      :row-key="record => record.order"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
    </a-table>

  </a-modal>
</template>

<script>
const columns = [
  {
    title: '用户名',
    dataIndex: 'usernick',
    width: '20%'
  },
  {
    title: '答题时间',
    dataIndex: 'time',
    width: '40%'
  },
  {
    title: '答题结果',
    dataIndex: 'result',
    width: '40%'
  }
];

export default {
  name: 'ViewDetailsModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    wrongQuestionId: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      columns,
      data: [{
        order: 0,
        usernick: '用户名',
        time: '答题时间',
        result: '答题结果'
      }],
      pagination: {
        hideOnSinglePage: true
      },
      loading: false,
    };
  },

  methods: {
    handleTableChange() {

    },

    handleCancel() {
      this.$emit('event-communication', { type: 'modal-cancel' });
    }
  }
};
</script>
