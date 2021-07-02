<template>
  <a-table
    :columns="columns"
    :row-key="record => record.order"
    :data-source="data"
    :pagination="pagination"
    :loading="loading"
    @change="handleTableChange"
  >
  </a-table>
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
  name: 'ViewWrongQuestionDetailTable',

  props: {
    data: {
      type: Array,
      required: true
    },

    loading: {
      type: Boolean,
      required: true
    },

    pagination: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      columns
    }
  },

  methods: {
    handleTableChange(pagination) {
      console.log(pagination);
    }
  }
};
</script>
