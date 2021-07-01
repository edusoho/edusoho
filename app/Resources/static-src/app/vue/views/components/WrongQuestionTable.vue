<template>
  <a-table
    :columns="columns"
    :row-key="record => record.order"
    :data-source="data"
    :pagination="pagination"
    :loading="loading"
    @change="handleTableChange"
  >
    <template slot="actions" slot-scope="actions, record">
      <a-button type="link" @click="handleClickViewDetails(record)">查看详情</a-button>
    </template>
  </a-table>
</template>

<script>
const columns = [
  {
    title: '',
    dataIndex: 'order',
    width: '5%'
  },
  {
    title: '题目',
    dataIndex: 'itemTitle',
    width: '40%'
  },
  {
    title: '任务名称',
    dataIndex: 'courseName',
    width: '15%'
  },
  {
    title: '来源',
    dataIndex: 'sourceType',
    width: '15%'
  },
  {
    title: '答错人次',
    dataIndex: 'wrong_times',
    width: '15%'
  },
  {
    title: '操作',
    width: '10%',
    scopedSlots: { customRender: 'actions' }
  },
];

export default {
  name: 'WrongQuestionTable',

  props: {
    data: {
      type: Array,
      required: true
    },

    pagination: {
      type: Object,
      required: true
    },

    loading: {
      type: Boolean,
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
      this.$emit('event-communication', {
        type: 'pagination',
        data: pagination
      });
    },

    handleClickViewDetails(params) {
      this.$emit('event-communication', {
        type: 'click',
        data: params
      });
    }
  }
}
</script>
