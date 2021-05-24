<template>
  <div class="class-info">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-input-search class="pull-left" placeholder="请输入课时或老师关键字搜索" style="width: 260px" @search="onSearch" />
      <a-button class="pull-right" type="primary">
        重排课时/新增课时
      </a-button>
    </div>

    <a-table
      :columns="columns"
      :row-key="record => record.login.uuid"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="name" slot-scope="name"> {{ name.first }} {{ name.last }} </template>
    </a-table>
  </div>
</template>

<script>
const columns = [
  {
    title: '课时名称',
    dataIndex: 'title',
    sorter: true,
    width: '20%'
  },
  {
    title: '教学模式',
    dataIndex: 'gender',
    filters: [
      { text: 'Male', value: 'male' },
      { text: 'Female', value: 'female' },
    ],
    width: '10%',
  },
  {
    title: '开课时间',
    dataIndex: 'email',
    sorter: true,
    width: '10%',
  },
  {
    title: '时长',
    dataIndex: 'gender',
    width: '10%'
  },
  {
    title: '授课老师',
    dataIndex: 'email',
    width: '10%'
  },
    {
    title: '助教老师',
    dataIndex: 'assistant',
    width: '10%'
  },
  {
    title: '问题讨论',
    dataIndex: 'email',
    width: '10%'
  },
  {
    title: '学习人数',
    dataIndex: 'gender',
    width: '10%'
  },
  {
    title: '操作',
    dataIndex: 'email',
    width: '10%'
  },
];

export default {
  data() {
    return {
      data,
      loading: false,
      columns,
    }
  },

  methods: {
    onSearch() {

    }
  }
}
</script>

<style lang="less">

</style>
