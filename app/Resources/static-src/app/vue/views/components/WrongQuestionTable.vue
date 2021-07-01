<template>
  <a-table
    class="mt24"
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
    dataIndex: 'stem',
    width: '40%'
  },
  {
    title: '任务名称',
    dataIndex: 'taskName',
    width: '15%'
  },
  {
    title: '来源',
    dataIndex: 'source',
    width: '15%'
  },
  {
    title: '答错人次',
    dataIndex: 'email',
    width: '15%'
  },
  {
    title: 'actions',
    width: '10%',
    scopedSlots: { customRender: 'actions' }
  },
];

export default {
  name: 'WrongQuestionTable',

  data() {
    return {
      columns,
      data: [{
        order: 1,
        stem: '这是一个题目',
        taskName: '这是任务名称',
        source: '这是来源',
        email: '这是答错人次',
        actions: '查看详情'
      }],
      pagination: {
        hideOnSinglePage: true
      },
      loading: false,
      visible: false
    }
  },

  methods: {
    handleTableChange(pagination) {
      const pager = { ...this.pagination };
      pager.current = pagination.current;
      this.pagination = pager;
      // this.fetch({
      //   results: pagination.pageSize,
      //   page: pagination.current,
      //   sortField: sorter.field,
      //   sortOrder: sorter.order,
      //   ...filters,
      // });
    }
  }
}
</script>
