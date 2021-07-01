<template>
  <div>
    <a-form-model
      ref="form"
      :model="form"
      layout="inline"
    >
      <a-form-model-item>
        <a-select
          v-model="form.source"
          style="width: 120px;"
          placeholder="题目来源"
        >
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
          <a-select-option value="kaoshi">
            考试任务
          </a-select-option>
          <a-select-option value="lianxi">
            练习任务
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="form.taskName"
          style="width: 120px;"
          placeholder="任务名称"
        >
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="form.sort"
          style="width: 120px;"
          placeholder="答错人次"
        >
          <a-select-option value="DES">
            由高至低
          </a-select-option>
          <a-select-option value="ASC">
            由低至高
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="onSearch">搜索</a-button>
      </a-form-model-item>
    </a-form-model>

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

    <view-details-modal :visible="visible" @handle-cancel="handleCancel" />
  </div>
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

import ViewDetailsModal from './ViewDetailsModal.vue';

export default {
  name: 'CourseManageWrongQuestion',

  components: {
    ViewDetailsModal
  },

  data() {
    return {
      form: {
        source: undefined,
        taskName: undefined,
        sort: undefined
      },
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
    onSearch() {
      this.$refs.form.validate(valid => {
        if (valid) {
          // do
        }
      })
    },

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
    },

    fetch(params = {}) {
      this.loading = true;
      // queryData({
      //   results: 10,
      //   ...params,
      // }).then(({ data }) => {
      //   const pagination = { ...this.pagination };
      //   // Read total count from server
      //   // pagination.total = data.totalCount;
      //   pagination.total = 200;
      //   this.loading = false;
      //   this.data = data.results;
      //   this.pagination = pagination;
      // });
    },

    handleClickViewDetails() {
      this.visible = true;
    },

    handleCancel() {
      this.visible = false;
    }
  }
}
</script>
